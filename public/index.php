<?php
// ============================================================
// public/index.php — Routeur principal
// Toutes les URLs passent par ici grâce au .htaccess
// ============================================================

// Charge l'autoloader de Composer (indispensable pour les classes avec namespace, ex: View)
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// ============================================================
// "SE SOUVENIR DE MOI" — reconnexion automatique
// Si la session a expiré (navigateur fermé) mais qu'un jeton valide
// existe encore dans le cookie, on reconnecte l'utilisateur tout seul.
// ============================================================
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../src/config/database.php';

    try {
        $pdo       = Database::connect();
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);

        $stmt = $pdo->prepare(
            'SELECT u.id, u.username, u.email, u.is_admin
             FROM remember_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = ? AND t.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['is_admin'] = !empty($user['is_admin']);
        } else {
            // Jeton invalide ou expiré : on supprime le cookie inutile
            setcookie('remember_token', '', time() - 3600, '/');
        }
    } catch (\Throwable $e) {
        // Si la table n'existe pas encore (migration pas faite) on ignore
        // simplement — l'utilisateur devra juste se reconnecter normalement
    }
}

// On récupère l'URL tapée dans le navigateur
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ============================================================
// ROUTES
// ============================================================
switch ($uri) {

    // Page d'accueil publique (landing page)
    // → redirige directement vers la carte si déjà connecté
    case '/':
        if (!empty($_SESSION['user_id'])) {
            header('Location: /accueil');
            exit;
        }
        require_once __DIR__ . '/../src/controllers/HomeController.php';
        $ctrl = new HomeController();
        $ctrl->index();
        break;

    // Connexion
    case '/login':
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->login();
        break;

    // Inscription
    case '/register':
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->register();
        break;

    // Déconnexion
    case '/logout':
        require_once __DIR__ . '/../src/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->logout();
        break;

    // Page principale après connexion (carte)
    case '/accueil':
        require_once __DIR__ . '/../src/views/accueil.php';
        break;
    // Page communauté (anciennement "profil", renommée par l'utilisateur)
    case '/communaute':
        require_once __DIR__ . '/../src/views/communaute.php';
        break;

        // Page paramètres de l'utilisateur connecté
    case '/parametres':
    case '/parametre':
        require_once __DIR__ . '/../src/controllers/ParametresController.php';
        (new ParametresController())->show();
        break;

    // API — Paramètres
    case '/api/check-username':
        require_once __DIR__ . '/../src/controllers/ParametresController.php';
        (new ParametresController())->checkUsername();
        break;

    case '/api/parametres/update':
        require_once __DIR__ . '/../src/controllers/ParametresController.php';
        (new ParametresController())->updateProfile();
        break;

    case '/api/parametres/password':
        require_once __DIR__ . '/../src/controllers/ParametresController.php';
        (new ParametresController())->updatePassword();
        break;

    case '/api/parametres/delete':
        require_once __DIR__ . '/../src/controllers/ParametresController.php';
        (new ParametresController())->deleteAccount();
        break;
    // Page de documentation / aide — accessible à tous
    case '/documentation':
        require_once __DIR__ . '/../src/views/documentation.php';
        break;

    // --------------------------------------------------------
    // API — Communauté (posts texte + commentaires)
    // --------------------------------------------------------

    // GET[?search=...] : liste les posts | POST : crée un post
    case '/api/posts':
        require_once __DIR__ . '/../src/controllers/CommunityController.php';
        $ctrl = new CommunityController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->index();
        }
        break;

    // POST : ajoute un commentaire à un post
    case '/api/post/comment':
        require_once __DIR__ . '/../src/controllers/CommunityController.php';
        $ctrl = new CommunityController();
        $ctrl->comment();
        break;

    // POST : supprime un post (auteur du post OU compte admin uniquement)
    case '/api/post/delete':
        require_once __DIR__ . '/../src/controllers/CommunityController.php';
        $ctrl = new CommunityController();
        $ctrl->destroy();
        break;

        // GET ?post_id=X : commentaires d'un post (chargés à l'ouverture)
    case '/api/post/comments':
        require_once __DIR__ . '/../src/controllers/CommunityController.php';
        (new CommunityController())->comments();
        break;

    // POST : réaction (pouce bleu / rouge) sur un post
    case '/api/post/react':
        require_once __DIR__ . '/../src/controllers/CommunityController.php';
        (new CommunityController())->react();
        break;

    // --------------------------------------------------------
    // API — Spots de pêche
    // --------------------------------------------------------

        // GET : temps restant avant de pouvoir ajouter un spot (anti-spam)
    case '/api/spots/cooldown':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        (new SpotController())->cooldown();
        break;

    // GET  : liste tous les spots (pour les marqueurs)
    // POST : crée un nouveau spot
    case '/api/spots':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        $ctrl = new SpotController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->index();
        }
        break;

    // GET ?id=X : détail d'un spot (description + avis)
    case '/api/spot':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        $ctrl = new SpotController();
        $ctrl->show();
        break;

    // POST : like / dislike un spot
    case '/api/spot/rate':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        $ctrl = new SpotController();
        $ctrl->rate();
        break;

    // POST : ajoute un avis (commentaire) sur un spot
    case '/api/spot/comment':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        $ctrl = new SpotController();
        $ctrl->comment();
        break;

    // POST : supprime un spot (auteur du spot OU compte admin uniquement)
    case '/api/spot/delete':
        require_once __DIR__ . '/../src/controllers/SpotController.php';
        $ctrl = new SpotController();
        $ctrl->destroy();
        break;

    // URL inconnue → 404
    default:
        http_response_code(404);
        echo '<h1>404 — Page introuvable</h1>';
        break;
}