<?php
// ============================================================
// src/controllers/SpotController.php
// Gère les spots de pêche : liste, détail, création, notes (étoiles), avis
//
// NOTE IMPORTANTE :
// La note rapide (étoiles) et l'avis texte partagent maintenant la même
// table "spot_reviews" (1 ligne par utilisateur et par spot). On peut noter
// sans écrire de texte (vote rapide), ou noter + écrire un avis complet.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class SpotController
{
    private const SPOT_COOLDOWN_SECONDS = 3600; // 1 heure
    // --------------------------------------------------------
    // GET /api/spots → liste tous les spots (pour les marqueurs)
    // --------------------------------------------------------
    public function index(): void
    {
        try {
            $pdo  = Database::connect();
            $stmt = $pdo->query(
                'SELECT s.id, s.name, s.description, s.latitude, s.longitude, s.region, s.species,
                s.rating, s.user_id, u.is_admin AS creator_is_admin, u.username AS creator_name
                FROM fishing_spots s
                JOIN users u ON u.id = s.user_id
                ORDER BY s.created_at DESC'
            );
            $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupère les notes personnelles de l'utilisateur connecté (1 seule requête)
            $myScores = [];
            if (!empty($_SESSION['user_id'])) {
                $stmt = $pdo->prepare('SELECT spot_id, score FROM spot_reviews WHERE user_id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $myScores[$row['spot_id']] = (int) $row['score'];
                }
            }

            foreach ($spots as &$spot) {
                $spot['my_score']         = $myScores[$spot['id']] ?? 0;
                $spot['creator_is_admin'] = (bool) $spot['creator_is_admin'];
                $spot['can_delete']       = $this->canDelete($spot);
            }

            $this->json($spots);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // GET /api/spot?id=5 → détail d'un spot + ses avis écrits
    // --------------------------------------------------------
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            $this->json(['error' => 'ID manquant.'], 400);
            return;
        }

        try {
            $pdo = Database::connect();

            $stmt = $pdo->prepare(
                'SELECT s.*, u.is_admin AS creator_is_admin, u.username AS creator_name
                FROM fishing_spots s
                JOIN users u ON u.id = s.user_id
                WHERE s.id = ?'
            );
            $stmt->execute([$id]);
            $spot = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$spot) {
                $this->json(['error' => 'Spot introuvable.'], 404);
                return;
            }

            $spot['creator_is_admin'] = (bool) $spot['creator_is_admin'];
            $spot['can_delete']       = $this->canDelete($spot);

            // Avis écrits uniquement (ceux qui ont un commentaire texte),
            // chacun avec sa note en étoiles pour permettre le filtrage côté client
            $stmt = $pdo->prepare(
                "SELECT r.score, r.comment, r.created_at, u.username
                 FROM spot_reviews r
                 JOIN users u ON u.id = r.user_id
                 WHERE r.spot_id = ? AND r.comment IS NOT NULL AND r.comment <> ''
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute([$id]);
            $spot['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Note personnelle de l'utilisateur connecté pour ce spot (pré-remplit les étoiles)
            $spot['my_score'] = 0;
            if (!empty($_SESSION['user_id'])) {
                $stmt = $pdo->prepare('SELECT score FROM spot_reviews WHERE user_id = ? AND spot_id = ?');
                $stmt->execute([$_SESSION['user_id'], $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $spot['my_score'] = $row ? (int) $row['score'] : 0;
            }

            $this->json($spot);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/spots → crée un nouveau spot
    // Seul le nom est obligatoire — tout le reste est facultatif
    // --------------------------------------------------------
    public function store(): void
{
    if (empty($_SESSION['user_id'])) {
        $this->json(['error' => 'Vous devez être connecté pour ajouter un spot.'], 401);
        return;
    }

    $name        = trim($_POST['name']        ?? '');
    $latitude    = $_POST['latitude']          ?? null;
    $longitude   = $_POST['longitude']         ?? null;
    $region      = trim($_POST['region']      ?? '');
    $species     = trim($_POST['species']     ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $latitude === null || $longitude === null) {
        $this->json(['error' => 'Le nom et la position sur la carte sont obligatoires.'], 400);
        return;
    }

    try {
        $pdo = Database::connect();

        // ── Anti-spam : 1 spot par heure (les admins sont exemptés) ──
        // La requête UPDATE est atomique : impossible de contourner la limite
        // en envoyant 2 requêtes en même temps.
        if (empty($_SESSION['is_admin'])) {
            $claim = $pdo->prepare(
                'UPDATE users SET last_spot_at = NOW()
                 WHERE id = ?
                   AND (last_spot_at IS NULL
                        OR last_spot_at <= NOW() - INTERVAL ' . self::SPOT_COOLDOWN_SECONDS . ' SECOND)'
            );
            $claim->execute([$_SESSION['user_id']]);

            if ($claim->rowCount() === 0) {
                $this->json([
                    'error'       => 'Vous pouvez ajouter un spot par heure.',
                    'cooldown'    => true,
                    'retry_after' => $this->cooldownRemaining($pdo, (int) $_SESSION['user_id']),
                ], 429);
                return;
            }
        }

        $stmt = $pdo->prepare(
            'INSERT INTO fishing_spots (user_id, name, description, latitude, longitude, region, species)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['user_id'],
            $name,
            $description !== '' ? $description : null,
            $latitude,
            $longitude,
            $region !== '' ? $region : null,
            $species !== '' ? $species : null,
        ]);

        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM fishing_spots WHERE id = ?');
        $stmt->execute([$newId]);
        $spot = $stmt->fetch(PDO::FETCH_ASSOC);

        $spot['creator_is_admin'] = !empty($_SESSION['is_admin']);
        $spot['creator_name']     = $_SESSION['username'] ?? '';
        $spot['can_delete']       = true;
        $spot['my_score']         = 0;

        $this->json($spot, 201);
    } catch (\Throwable $e) {
        $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
    }
}

    // --------------------------------------------------------
    // POST /api/spot/rate → note rapide en étoiles (1 à 5), sans texte
    // Un seul vote par utilisateur et par spot (on peut le changer)
    // --------------------------------------------------------
    public function rate(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté pour voter.'], 401);
            return;
        }

        $spotId = (int) ($_POST['spot_id'] ?? 0);
        $score  = (int) ($_POST['score']   ?? 0);

        if (!$spotId || $score < 1 || $score > 5) {
            $this->json(['error' => 'La note doit être comprise entre 1 et 5 étoiles.'], 400);
            return;
        }

        try {
            $pdo = Database::connect();

            // Si l'utilisateur avait déjà laissé un avis avec texte, on garde
            // son commentaire — on ne met à jour que la note (score)
            $stmt = $pdo->prepare(
                'INSERT INTO spot_reviews (user_id, spot_id, score)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE score = ?'
            );
            $stmt->execute([$_SESSION['user_id'], $spotId, $score, $score]);

            $avg = $this->recalculateRating($pdo, $spotId);

            $this->json(['rating' => $avg]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/spot/comment → publie un avis complet (note + texte)
    // --------------------------------------------------------
    public function comment(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté pour laisser un avis.'], 401);
            return;
        }

        $spotId  = (int) ($_POST['spot_id'] ?? 0);
        $score   = (int) ($_POST['score']   ?? 0);
        $comment = trim($_POST['comment']  ?? '');

        if (!$spotId || $comment === '') {
            $this->json(['error' => 'Votre avis ne peut pas être vide.'], 400);
            return;
        }

        if ($score < 1 || $score > 5) {
            $this->json(['error' => 'Merci de donner une note de 1 à 5 étoiles avant de publier votre avis.'], 400);
            return;
        }

        try {
            $pdo  = Database::connect();
            $stmt = $pdo->prepare(
                'INSERT INTO spot_reviews (user_id, spot_id, score, comment)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE score = ?, comment = ?'
            );
            $stmt->execute([$_SESSION['user_id'], $spotId, $score, $comment, $score, $comment]);

            $this->recalculateRating($pdo, $spotId);

            $this->json([
                'score'      => $score,
                'comment'    => $comment,
                'username'   => $_SESSION['username'],
                'created_at' => date('Y-m-d H:i:s'),
            ], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/spot/delete → supprime un spot
    // Autorisé uniquement pour : le créateur du spot, OU un compte admin
    // --------------------------------------------------------
    public function destroy(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté.'], 401);
            return;
        }

        $spotId = (int) ($_POST['spot_id'] ?? 0);
        if (!$spotId) {
            $this->json(['error' => 'ID manquant.'], 400);
            return;
        }

        try {
            $pdo  = Database::connect();
            $stmt = $pdo->prepare('SELECT user_id FROM fishing_spots WHERE id = ?');
            $stmt->execute([$spotId]);
            $spot = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$spot) {
                $this->json(['error' => 'Spot introuvable.'], 404);
                return;
            }

            if (!$this->canDelete($spot)) {
                $this->json(['error' => "Vous n'avez pas le droit de supprimer ce spot."], 403);
                return;
            }

            // Les avis (spot_reviews) liés sont supprimés automatiquement
            // grâce à la contrainte ON DELETE CASCADE en base
            $pdo->prepare('DELETE FROM fishing_spots WHERE id = ?')->execute([$spotId]);

            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // GET /api/spots/cooldown → temps restant avant de pouvoir ajouter un spot
public function cooldown(): void
{
    if (empty($_SESSION['user_id'])) {
        $this->json(['error' => 'Non connecté.'], 401);
        return;
    }
    if (!empty($_SESSION['is_admin'])) {
        $this->json(['remaining' => 0]);
        return;
    }
    try {
        $pdo = Database::connect();
        $this->json(['remaining' => $this->cooldownRemaining($pdo, (int) $_SESSION['user_id'])]);
    } catch (\Throwable $e) {
        $this->json(['remaining' => 0]); // le serveur re-vérifiera de toute façon dans store()
    }
}

private function cooldownRemaining(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT TIMESTAMPDIFF(SECOND, last_spot_at, NOW()) FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $elapsed = $stmt->fetchColumn();

    if ($elapsed === false || $elapsed === null) {
        return 0;
    }
    return max(0, self::SPOT_COOLDOWN_SECONDS - (int) $elapsed);
}

    // --------------------------------------------------------
    // Un spot peut être supprimé par son créateur, ou par un admin
    // --------------------------------------------------------
    private function canDelete(array $spot): bool
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }

        if (!empty($_SESSION['is_admin'])) {
            return true;
        }

        return (int) $spot['user_id'] === (int) $_SESSION['user_id'];
    }

    // --------------------------------------------------------
    // Recalcule la note moyenne d'un spot et la met en cache
    // dans fishing_spots.rating (évite de recalculer à chaque affichage)
    // --------------------------------------------------------
    private function recalculateRating(PDO $pdo, int $spotId): float
    {
        $stmt = $pdo->prepare('SELECT ROUND(AVG(score), 1) AS avg_rating FROM spot_reviews WHERE spot_id = ?');
        $stmt->execute([$spotId]);
        $avg = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'] ?? 0);

        $pdo->prepare('UPDATE fishing_spots SET rating = ? WHERE id = ?')->execute([$avg, $spotId]);

        return $avg;
    }

    // --------------------------------------------------------
    // Petit utilitaire pour répondre en JSON
    // --------------------------------------------------------
    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}