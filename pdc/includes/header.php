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
<nav class="navbar navbar-default navbar-fixed-top pdc-navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#pdc-nav">
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
                <i class="fa-regular fa-calendar-check"></i> <?php echo $pageTitle; ?>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="pdc-nav">
            <?php if (!empty($currentUser)): ?>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="<?php echo APP_URL; ?>/index.php"><i class="fa fa-home"></i> Plan de charge</a></li>
                <?php if (!empty($isAdmin) || !empty($isResponsable)): ?>
                <li><a href="<?php echo APP_URL; ?>/admin.php"><i class="fa fa-cog"></i> Administration</a></li>
                <?php endif; ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-user"></i>
                        <?php echo htmlspecialchars($currentUser['displayname'], ENT_QUOTES, 'UTF-8'); ?>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo APP_URL; ?>/logout.php"><i class="fa fa-sign-out"></i> Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="pdc-main-wrapper">