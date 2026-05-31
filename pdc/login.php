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
<div class="pdc-login-container">
    <div class="pdc-login-logo">
        <i class="fa fa-calendar-check-o fa-3x"></i>
        <h1><?php echo APP_NAME; ?></h1>
    </div>
    <div class="panel panel-default pdc-login-panel">
        <div class="panel-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>
            <form method="post" action="" autocomplete="off">
                <div class="form-group">
                    <label for="username"><i class="fa fa-user"></i> Identifiant</label>
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="Votre identifiant"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                           autofocus required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fa fa-lock"></i> Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Votre mot de passe" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fa fa-sign-in"></i> Se connecter
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-muted"><small>Authentification LDAP</small></p>
</div>
<script src="<?php echo APP_URL; ?>/assets/js/jquery.min.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/bootstrap.min.js"></script>
</body>
</html>