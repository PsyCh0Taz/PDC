<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo isset($page_title) ? h($page_title) . ' - ' : ''; echo h(APP_NAME); ?></title>
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- FontAwesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand font-weight-bold" href="<?php echo APP_URL; ?>/index.php">
        <i class="fas fa-crosshairs mr-1"></i><?php echo h(APP_NAME); ?>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                    <i class="fas fa-home mr-1"></i>Accueil
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo APP_URL; ?>/calendar.php">
                    <i class="fas fa-calendar-alt mr-1"></i>Calendrier
                </a>
            </li>
            <?php if (is_admin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navAdmin" data-toggle="dropdown">
                    <i class="fas fa-cog mr-1"></i>Administration
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/index.php">
                        <i class="fas fa-tachometer-alt mr-1"></i>Tableau de bord
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/tirs.php">
                        <i class="fas fa-bullseye mr-1"></i>Séances de tir
                    </a>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/categories_tir.php">
                        <i class="fas fa-layer-group mr-1"></i>Catégories de tir
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/armes.php">
                        <i class="fas fa-gun mr-1"></i>Armes
                    </a>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/categories_armes.php">
                        <i class="fas fa-tags mr-1"></i>Catégories d'armes
                    </a>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/raisons.php">
                        <i class="fas fa-list mr-1"></i>Raisons
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/articles.php">
                        <i class="fas fa-newspaper mr-1"></i>Articles
                    </a>
                </div>
            </li>
            <?php endif; ?>
            <?php endif; ?>
        </ul>

        <ul class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <i class="fas fa-user-circle mr-1"></i>
                    <?php echo h(trim($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?: $_SESSION['uid']); ?>
                    <?php if (is_admin()): ?>
                        <span class="badge badge-warning ml-1">Admin</span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/logout.php">
                        <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                    </a>
                </div>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo APP_URL; ?>/login.php">
                    <i class="fas fa-sign-in-alt mr-1"></i>Connexion
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container-fluid mt-3">
<?php flash_render(); ?>
