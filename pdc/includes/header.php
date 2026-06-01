<?php
// includes/header.php
// Appelé avec $pageTitle défini
if (!isset($pageTitle)) $pageTitle = APP_NAME;
$pageTitle = ( OFFLINE_MODE  ? $pageTitle . ' (Mode Offline)' : $pageTitle );

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/pdc.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top pdc-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
            <i class="fa-regular fa-calendar-check"></i> <?php echo $pageTitle; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pdc-nav" aria-controls="pdc-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="pdc-nav">
            <?php if (!empty($currentUser)): ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/index.php"><i class="fa-solid fa-house"></i> Plan de charge</a></li>
                <?php if (!empty($isAdmin) || !empty($isResponsable)): ?>
                <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>/admin.php"><i class="fa-solid fa-gear"></i> Administration</a></li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <button class="btn dropdown-toggle" style="background-color: transparent; color: white;" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                        <?php echo htmlspecialchars($currentUser['displayname'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="<?php echo APP_URL; ?>/logout.php" class="dropdown-item"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
                    </div>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="pdc-main-wrapper">