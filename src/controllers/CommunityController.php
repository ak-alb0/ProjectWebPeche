<?php
// ============================================================
// src/controllers/CommunityController.php
// Fil de la communauté : posts (avec type), réactions, commentaires
//
// GET  /api/posts            → liste paginée + filtres  { posts, has_more }
// POST /api/posts            → crée un post
// GET  /api/post/comments    → commentaires d'un post (chargés à la demande)
// POST /api/post/comment     → ajoute un commentaire
// POST /api/post/react       → pouce bleu / rouge
// POST /api/post/delete      → supprime un post
// ============================================================

require_once __DIR__ . '/../config/database.php';

class CommunityController
{
    private const PAGE_SIZE             = 15;
    private const TITLE_MAX             = 100;
    private const CONTENT_MAX           = 1500;
    private const COMMENT_MAX           = 300;
    private const POST_COOLDOWN_SECONDS = 30;   // délai minimum entre 2 posts (hors admin)
    private const CATEGORIES            = ['sortie', 'avis', 'question', 'astuce', 'prise', 'autre'];

    // --------------------------------------------------------
    // GET /api/posts?search=&categories=sortie,avis&support=1&before=ID
    // --------------------------------------------------------
    public function index(): void
    {
        try {
            $pdo    = Database::connect();
            $where  = [];
            $params = [];

            $search = trim((string) ($_GET['search'] ?? ''));
            if ($search !== '') {
                $like = '%' . addcslashes(mb_substr($search, 0, 100), '%_\\') . '%';
                $where[] = '(p.title LIKE ? OR p.content LIKE ?)';
                array_push($params, $like, $like);
            }

            $requested = array_filter(explode(',', (string) ($_GET['categories'] ?? '')));
            $cats      = array_values(array_intersect(self::CATEGORIES, $requested));
            if ($cats) {
                $where[] = 'p.category IN (' . implode(',', array_fill(0, count($cats), '?')) . ')';
                array_push($params, ...$cats);
            }

            if (!empty($_GET['support'])) {
                $where[] = 'u.is_admin = 1';
            }

            // Pagination par curseur : on demande les posts plus anciens que "before"
            $before = (int) ($_GET['before'] ?? 0);
            if ($before > 0) {
                $where[]  = 'p.id < ?';
                $params[] = $before;
            }

            // On demande 1 post de plus que la page pour savoir s'il en reste
            $rows    = $this->queryPosts($pdo, implode(' AND ', $where), $params, self::PAGE_SIZE + 1);
            $hasMore = count($rows) > self::PAGE_SIZE;
            if ($hasMore) {
                array_pop($rows);
            }

            $this->json(['posts' => $rows, 'has_more' => $hasMore]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/posts → publie un nouveau post
    // --------------------------------------------------------
    public function store(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté pour publier.'], 401);
            return;
        }

        $title           = trim($_POST['title']   ?? '');
        $content         = trim($_POST['content'] ?? '');
        $category        = $_POST['category'] ?? '';
        $commentsEnabled = (($_POST['comments_enabled'] ?? '1') === '1') ? 1 : 0;

        if ($title === '' || $content === '') {
            $this->json(['error' => 'Le titre et le message sont obligatoires.'], 400);
            return;
        }
        if (!in_array($category, self::CATEGORIES, true)) {
            $this->json(['error' => 'Choisissez un type de post.'], 400);
            return;
        }
        if (mb_strlen($title) > self::TITLE_MAX) {
            $this->json(['error' => 'Le titre est limité à ' . self::TITLE_MAX . ' caractères.'], 400);
            return;
        }
        if (mb_strlen($content) > self::CONTENT_MAX) {
            $this->json(['error' => 'Le message est limité à ' . self::CONTENT_MAX . ' caractères.'], 400);
            return;
        }

        try {
            $pdo = Database::connect();

            // Anti-spam léger : délai minimum entre deux posts (les admins sont exemptés)
            if (empty($_SESSION['is_admin'])) {
                $stmt = $pdo->prepare(
                    'SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) FROM community_posts WHERE user_id = ?'
                );
                $stmt->execute([$_SESSION['user_id']]);
                $elapsed = $stmt->fetchColumn();

                if ($elapsed !== false && $elapsed !== null && (int) $elapsed < self::POST_COOLDOWN_SECONDS) {
                    $wait = self::POST_COOLDOWN_SECONDS - (int) $elapsed;
                    $this->json(['error' => "Doucement ! Réessayez dans {$wait} seconde" . ($wait > 1 ? 's' : '') . '.'], 429);
                    return;
                }
            }

            $stmt = $pdo->prepare(
                'INSERT INTO community_posts (user_id, title, content, category, comments_enabled)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$_SESSION['user_id'], $title, $content, $category, $commentsEnabled]);
            $newId = (int) $pdo->lastInsertId();

            // On renvoie le post avec exactement la même forme que dans la liste
            $rows = $this->queryPosts($pdo, 'p.id = ?', [$newId], 1);
            $this->json($rows[0] ?? ['error' => 'Post introuvable.'], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // GET /api/post/comments?post_id=X → commentaires d'un post
    // --------------------------------------------------------
    public function comments(): void
    {
        $postId = (int) ($_GET['post_id'] ?? 0);
        if (!$postId) {
            $this->json(['error' => 'ID manquant.'], 400);
            return;
        }

        try {
            $pdo  = Database::connect();
            $stmt = $pdo->prepare(
                'SELECT c.content, c.created_at, u.username, u.is_admin
                 FROM community_comments c
                 JOIN users u ON u.id = c.user_id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC
                 LIMIT 300'
            );
            $stmt->execute([$postId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                $row['is_admin'] = (bool) $row['is_admin'];
            }

            $this->json($rows);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/post/comment → ajoute un commentaire
    // --------------------------------------------------------
    public function comment(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté pour commenter.'], 401);
            return;
        }

        $postId  = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content']  ?? '');

        if (!$postId || $content === '') {
            $this->json(['error' => 'Le commentaire ne peut pas être vide.'], 400);
            return;
        }
        if (mb_strlen($content) > self::COMMENT_MAX) {
            $this->json(['error' => 'Le commentaire est limité à ' . self::COMMENT_MAX . ' caractères.'], 400);
            return;
        }

        try {
            $pdo  = Database::connect();
            $stmt = $pdo->prepare('SELECT comments_enabled FROM community_posts WHERE id = ?');
            $stmt->execute([$postId]);
            $enabled = $stmt->fetchColumn();

            if ($enabled === false) {
                $this->json(['error' => 'Post introuvable.'], 404);
                return;
            }
            if ((int) $enabled !== 1) {
                $this->json(['error' => "Les commentaires sont désactivés sur ce post."], 403);
                return;
            }

            $pdo->prepare('INSERT INTO community_comments (user_id, post_id, content) VALUES (?, ?, ?)')
                ->execute([$_SESSION['user_id'], $postId, $content]);

            $this->json([
                'content'    => $content,
                'username'   => $_SESSION['username'],
                'is_admin'   => !empty($_SESSION['is_admin']),
                'created_at' => date('Y-m-d H:i:s'),
            ], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/post/react  (post_id, reaction = like | dislike)
    // Re-cliquer sur sa réaction la retire ; cliquer sur l'autre la remplace
    // --------------------------------------------------------
    public function react(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté pour réagir.'], 401);
            return;
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $type   = $_POST['reaction'] ?? '';
        $value  = $type === 'like' ? 1 : ($type === 'dislike' ? -1 : 0);

        if (!$postId || !$value) {
            $this->json(['error' => 'Requête invalide.'], 400);
            return;
        }

        try {
            $pdo = Database::connect();

            $stmt = $pdo->prepare('SELECT 1 FROM community_posts WHERE id = ?');
            $stmt->execute([$postId]);
            if (!$stmt->fetchColumn()) {
                $this->json(['error' => 'Post introuvable.'], 404);
                return;
            }

            $stmt = $pdo->prepare('SELECT reaction FROM post_reactions WHERE user_id = ? AND post_id = ?');
            $stmt->execute([$_SESSION['user_id'], $postId]);
            $current = $stmt->fetchColumn();

            if ($current !== false && (int) $current === $value) {
                $pdo->prepare('DELETE FROM post_reactions WHERE user_id = ? AND post_id = ?')
                    ->execute([$_SESSION['user_id'], $postId]);
                $mine = 0;
            } else {
                $pdo->prepare(
                    'INSERT INTO post_reactions (user_id, post_id, reaction) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE reaction = ?'
                )->execute([$_SESSION['user_id'], $postId, $value, $value]);
                $mine = $value;
            }

            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(reaction = 1), 0) AS likes, COALESCE(SUM(reaction = -1), 0) AS dislikes
                 FROM post_reactions WHERE post_id = ?'
            );
            $stmt->execute([$postId]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->json([
                'likes'       => (int) $counts['likes'],
                'dislikes'    => (int) $counts['dislikes'],
                'my_reaction' => $mine,
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // POST /api/post/delete → supprime un post (auteur OU admin)
    // --------------------------------------------------------
    public function destroy(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Vous devez être connecté.'], 401);
            return;
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        if (!$postId) {
            $this->json(['error' => 'ID manquant.'], 400);
            return;
        }

        try {
            $pdo  = Database::connect();
            $stmt = $pdo->prepare('SELECT user_id FROM community_posts WHERE id = ?');
            $stmt->execute([$postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                $this->json(['error' => 'Post introuvable.'], 404);
                return;
            }
            if (!$this->canDelete($post)) {
                $this->json(['error' => "Vous n'avez pas le droit de supprimer ce post."], 403);
                return;
            }

            // Commentaires et réactions liés : supprimés par ON DELETE CASCADE
            $pdo->prepare('DELETE FROM community_posts WHERE id = ?')->execute([$postId]);

            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------
    // Requête unique pour lister des posts : auteur, nombre de commentaires,
    // compteurs de réactions et réaction de l'utilisateur connecté (aucune
    // requête supplémentaire par post)
    // --------------------------------------------------------
    private function queryPosts(PDO $pdo, string $whereSql, array $whereParams, int $limit): array
    {
        $uid = (int) ($_SESSION['user_id'] ?? 0);

        $sql = 'SELECT p.id, p.user_id, p.title, p.content, p.category, p.comments_enabled, p.created_at,
                       u.username, u.is_admin,
                       (SELECT COUNT(*) FROM community_comments c WHERE c.post_id = p.id) AS comment_count,
                       (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id AND r.reaction = 1)  AS likes,
                       (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id AND r.reaction = -1) AS dislikes,
                       (SELECT r.reaction FROM post_reactions r WHERE r.post_id = p.id AND r.user_id = ?) AS my_reaction
                FROM community_posts p
                JOIN users u ON u.id = p.user_id '
             . ($whereSql !== '' ? 'WHERE ' . $whereSql . ' ' : '')
             . 'ORDER BY p.id DESC LIMIT ' . (int) $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$uid], $whereParams));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['id']               = (int) $row['id'];
            $row['comments_enabled'] = (bool) $row['comments_enabled'];
            $row['is_admin']         = (bool) $row['is_admin'];
            $row['comment_count']    = (int) $row['comment_count'];
            $row['likes']            = (int) $row['likes'];
            $row['dislikes']         = (int) $row['dislikes'];
            $row['my_reaction']      = (int) ($row['my_reaction'] ?? 0);
            $row['can_delete']       = $this->canDelete($row);
        }

        return $rows;
    }

    private function canDelete(array $post): bool
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }
        if (!empty($_SESSION['is_admin'])) {
            return true;
        }
        return (int) $post['user_id'] === (int) $_SESSION['user_id'];
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}