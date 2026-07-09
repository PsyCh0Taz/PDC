<?php
require_once __DIR__ . '/includes/init.php';

$page_title = 'Connexion';
$error      = null;

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $login    = isset($_POST['login'])    ? trim($_POST['login'])    : '';
    $password = isset($_POST['password']) ? $_POST['password']       : '';

    $result = ldap_login($login, $password);
    if ($result['success']) {
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : APP_URL . '/index.php';
        // Sécurité : n'autoriser que les redirections relatives
        if (strpos($redirect, APP_URL) !== 0) {
            $redirect = APP_URL . '/index.php';
        }
        redirect($redirect);
    } else {
        $error = $result['error'];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">
                    <i class="fas fa-crosshairs text-danger mr-2"></i><?php echo h(APP_NAME); ?>
                </h4>

                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="login">Identifiant</label>
                        <input type="text" id="login" name="login" class="form-control" required autofocus
                               value="<?php echo isset($_POST['login']) ? h($_POST['login']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-sign-in-alt mr-1"></i>Se connecter
                    </button>
                </form>
            </div>
        </div>
        <p class="text-center text-muted mt-3 small">Authentification via annuaire LDAP</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
