<?php
require_once __DIR__ . '/includes/bootstrap.php';

Auth::startSession();

// Déjà connecté ?
if (!empty($_SESSION['user'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = 'Veuillez saisir votre identifiant et votre mot de passe.';
    } else {
        try {
            $userInfo = Auth::login($username, $password);
            if ($userInfo === false) {
                $error = 'Identifiant ou mot de passe incorrect.';
                Journal::logConnexion($username . ' (échec)', Journal::getIp());
            } else {
                Auth::setUser($userInfo);
                Journal::logConnexion($userInfo['username'], Journal::getIp());
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : APP_URL . '/index.php';
                header('Location: ' . $redirect);
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Connexion';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/pdc.css">
</head>
<body class="pdc-login-page">
<div class="pdc-login-shell">
    <div class="pdc-login-container">
        <div class="pdc-login-logo">
            <div class="pdc-login-logo-icon"><i class="fa-regular fa-calendar-check"></i></div>
            <h1><?php echo APP_NAME; ?></h1>
            <p class="pdc-login-subtitle">Plan de charge</p>
        </div>

        <div class="pdc-login-panel">
            <div class="pdc-login-panel-head">
                <h2>Connexion</h2>
                <p>Authentifiez-vous pour acceder a l'application.</p>
            </div>

            <div class="pdc-login-panel-body">
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php endif; ?>

                <form method="post" action="" autocomplete="off" class="pdc-login-form">
                    <div class="mb-3">
                        <label class="form-label" for="username"><i class="fa-regular fa-user"></i> Utilisateur</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username"
                               placeholder="Votre identifiant Windows (p.nom)"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                               autofocus required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password"><i class="fa-solid fa-lock"></i> Mot de passe</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                               placeholder="Votre mot de passe Windows" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
                    </button>
                </form>
            </div>

            <div class="pdc-login-panel-foot">
                <small><i class="fa-solid fa-shield-halved"></i> Authentification LDAP</small>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo APP_URL; ?>/assets/js/jquery.min.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>