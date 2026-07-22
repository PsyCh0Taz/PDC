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
    <meta name="color-scheme" content="light dark">
    <script>
        (function() {
            var theme = null;
            try { theme = localStorage.getItem('pdc.theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        }());
    </script>
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/pdc.css">
    <script src="<?php echo APP_URL; ?>/assets/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top pdc-navbar">
    <div class="container-fluid">
        <div class="pdc-navbar-shell">
            <div class="pdc-navbar-main">
                <?php if (isset($id)): ?>
                <button type="button" class="btn btn-outline-light pdc-navbar-sidebar-toggle" id="btn-toggle-sidebar" aria-expanded="true" title="Replier/afficher la sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <?php endif; ?>
                <?php if (!empty($breadcrumb) && is_array($breadcrumb)): ?>
                <div class="navbar-brand pdc-navbar-brand-trail">
                    <ol class="breadcrumb pdc-navbar-breadcrumb">
                        <?php foreach ($breadcrumb as $index => $b): ?>
                        <li class="<?php echo ($index === count($breadcrumb) - 1) ? 'active' : ''; ?>">
                            <?php if ($index === count($breadcrumb) - 1): ?>
                            <span>
                                <?php if ($index === 0): ?><i class="fa-solid fa-house"></i><?php endif; ?>
                                <?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php else: ?>
                            <a href="<?php echo htmlspecialchars($b['link'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($index === 0): ?><i class="fa-solid fa-house"></i><?php endif; ?>
                                <?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php else: ?>
                <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
                    <i class="fa-regular fa-calendar-check"></i> <?php echo $pageTitle; ?>
                </a>
                <?php endif; ?>
                <div class="collapse navbar-collapse justify-content-end" id="pdc-nav">
                    <button type="button" class="btn pdc-theme-toggle" data-pdc-theme-toggle aria-label="Activer le thème sombre" aria-pressed="false" title="Activer le thème sombre">
                        <i class="fa-solid fa-moon" aria-hidden="true"></i>
                        <span class="pdc-theme-toggle-label">Thème sombre</span>
                    </button>
                    <?php if (!empty($currentUser)): ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <button class="btn dropdown-toggle pdc-navbar-user" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-user"></i>
                                <?php echo htmlspecialchars($currentUser['displayname'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <?php if (!empty($isAdmin)): ?>
                                <a href="<?php echo APP_URL; ?>/admin.php" class="dropdown-item"><i class="fa-solid fa-gear"></i> Administration</a>
                                <hr class="dropdown-divider">
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/logout.php" class="dropdown-item"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
                            </div>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($breadcrumb, $currentContextLabel, $currentRoleLabel, $dateDebut, $dateFin)): ?>
            <div class="pdc-navbar-utility">
                <div class="pdc-topbar-context">
                    <div class="pdc-topbar-eyebrow">Navigation</div>
                    <div class="pdc-topbar-heading-row">
                        <h1 class="pdc-topbar-title"><?php echo htmlspecialchars($currentContextLabel, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <span class="pdc-topbar-badge"><?php echo htmlspecialchars($currentRoleLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <ol class="breadcrumb pdc-breadcrumb pdc-breadcrumb-inline">
                        <?php foreach ($breadcrumb as $index => $b): ?> 
                            <li class="<?php echo ($index === count($breadcrumb) - 1) ? 'active' : ''; ?>">
                                <a href="<?php echo htmlspecialchars($b['link'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($index === 0): ?><i class="fa-solid fa-house"></i><?php endif; ?>
                                    <?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

                <div class="pdc-topbar-actions">
                    <form id="periode_form" method="get" class="form-inline pdc-periode-form pdc-periode-form-inline">
                        <?php if (!empty($id)): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                        <?php endif; ?>

                        <div class="form-group pdc-period-filter-group">
                            <div class="pdc-filter-label"><i class="fa-regular fa-calendar"></i> Période affichée</div>
                            <div class="pdc-period-inputs">
                                <input type="text" class="form-control pdc-datepicker" name="date_debut" id="date_debut" value="<?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?>" required>
                                <span class="pdc-period-separator">au</span>
                                <input type="text" class="form-control pdc-datepicker" name="date_fin" id="date_fin" value="<?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?>" required>
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
                            </div>
                        </div>
                    </form>

                    <div class="pdc-topbar-buttons">
                        <?php if (!empty($canShareExportCurrentLevel)): ?>
                        <button class="btn btn-success" id="btn-export-pdf" title="Exporter en PDF">
                            <i class="fa-regular fa-file-pdf"></i> Export PDF
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="pdc-main-wrapper">
