<?php
// ============================================================
// src/controllers/ParametresController.php
//
// GET  /parametre|/parametres          → show()
// POST /api/parametres/update          → updateProfile()
// POST /api/parametres/password        → updatePassword()
// POST /api/parametres/delete          → deleteAccount()
// GET  /api/check-username             → checkUsername()
// ============================================================

require_once __DIR__ . '/../config/database.php';

class ParametresController
{
    /** Affiche la page paramètres */
    public function show(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login'); exit;
        }

        $pdo  = Database::connect();
        $stmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) { header('Location: /logout'); exit; }

        require_once __DIR__ . '/../views/parametres.php';
    }

    /** Modifie le username et/ou l'email */
    public function updateProfile(): void
    {
        $this->jsonHeader();
        if (empty($_SESSION['user_id'])) { $this->err(401, 'Non authentifié.'); return; }

        $body     = $this->body();
        $username = trim($body['username'] ?? '');
        $email    = trim($body['email']    ?? '');

        if ($username === '' && $email === '') {
            $this->err(422, 'Aucune modification à enregistrer.'); return;
        }

        $pdo = Database::connect();

        // Validation + unicité username
        if ($username !== '') {
            if (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $username)) {
                $this->err(422, 'Nom invalide. Utilisez uniquement a-z A-Z 0-9 . - _ (3 à 50 caractères).'); return;
            }
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$username, $_SESSION['user_id']]);
            if ($stmt->fetch()) { $this->err(409, 'Ce nom d\'utilisateur est déjà pris.'); return; }
        }

        // Validation + unicité email
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->err(422, 'Adresse email invalide.'); return;
            }
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) { $this->err(409, 'Cet email est déjà utilisé.'); return; }
        }

        // Mise à jour
        if ($username !== '' && $email !== '') {
            $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?')
                ->execute([$username, $email, $_SESSION['user_id']]);
        } elseif ($username !== '') {
            $pdo->prepare('UPDATE users SET username = ? WHERE id = ?')
                ->execute([$username, $_SESSION['user_id']]);
        } else {
            $pdo->prepare('UPDATE users SET email = ? WHERE id = ?')
                ->execute([$email, $_SESSION['user_id']]);
        }

        if ($username !== '') $_SESSION['username'] = $username;
        if ($email    !== '') $_SESSION['email']    = $email;

        echo json_encode(['success' => true, 'username' => $_SESSION['username'], 'email' => $_SESSION['email']]);
    }

    /** Change le mot de passe */
    public function updatePassword(): void
    {
        $this->jsonHeader();
        if (empty($_SESSION['user_id'])) { $this->err(401, 'Non authentifié.'); return; }

        $body    = $this->body();
        $current = $body['current_password'] ?? '';
        $newPass = $body['new_password']     ?? '';
        $confirm = $body['confirm_password'] ?? '';

        if ($current === '' || $newPass === '' || $confirm === '') {
            $this->err(422, 'Remplissez tous les champs.'); return;
        }
        if ($newPass !== $confirm) {
            $this->err(422, 'Les nouveaux mots de passe ne correspondent pas.'); return;
        }
        if (strlen($newPass) < 8) {
            $this->err(422, 'Le mot de passe doit faire au moins 8 caractères.'); return;
        }

        $pdo  = Database::connect();
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($current, $row['password'])) {
            $this->err(403, 'Mot de passe actuel incorrect.'); return;
        }

        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([password_hash($newPass, PASSWORD_BCRYPT), $_SESSION['user_id']]);

        echo json_encode(['success' => true]);
    }

    /** Supprime définitivement le compte */
    public function deleteAccount(): void
    {
        $this->jsonHeader();
        if (empty($_SESSION['user_id'])) { $this->err(401, 'Non authentifié.'); return; }

        $body     = $this->body();
        $password = $body['password'] ?? '';

        if ($password === '') {
            $this->err(422, 'Entrez votre mot de passe pour confirmer.'); return;
        }

        try {
            $pdo  = Database::connect();

            // Vérifie le mot de passe avant toute suppression
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->err(404, 'Compte introuvable.'); return;
            }
            if (!password_verify($password, $row['password'])) {
                $this->err(403, 'Mot de passe incorrect.'); return;
            }

            $userId = (int) $_SESSION['user_id'];

            // Supprime le compte (les FK ON DELETE CASCADE gèrent les tables liées)
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

            // Supprime le jeton "se souvenir de moi" en base si encore présent
            // (normalement déjà supprimé par la CASCADE sur remember_tokens)
            setcookie('remember_token', '', time() - 3600, '/');

            // Détruit la session PHP
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 3600,
                    $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();

            echo json_encode(['success' => true]);

        } catch (\Throwable $e) {
            $this->err(500, 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /** Vérifie si un username est disponible (appelé en temps réel depuis le JS) */
    public function checkUsername(): void
    {
        $this->jsonHeader();
        $username = trim($_GET['username'] ?? '');

        if (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $username)) {
            echo json_encode(['available' => false, 'reason' => 'format']); return;
        }

        $pdo  = Database::connect();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $_SESSION['user_id'] ?? 0]);

        echo json_encode(['available' => !$stmt->fetch()]);
    }

    // ── Helpers privés ─────────────────────────────────────────

    private function jsonHeader(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    private function body(): array
    {
        $raw = file_get_contents('php://input');
        $d   = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }

    private function err(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
    }
}