<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

$hierarchieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-01-01');
$dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-12-31');
$includeGradients = !isset($_GET['include_gradients']) || $_GET['include_gradients'] !== '0';
$includeJalons = !isset($_GET['include_jalons']) || $_GET['include_jalons'] !== '0';

if ($hierarchieId <= 0) {
    http_response_code(400);
    die('Niveau de hierarchie invalide.');
}

$roleRank = array(
    'lecteur' => 1,
    'modificateur' => 2,
);

$scope = 'hierarchie:' . $hierarchieId;
$currentRole = isset($currentUser['roles'][$scope]) ? $currentUser['roles'][$scope] : null;
$canRead = ($currentRole !== null && isset($roleRank[$currentRole]) && $roleRank[$currentRole] >= 1);

if (!$canRead) {
    http_response_code(403);
    die('Acces refuse. Vous ne pouvez pas exporter ce niveau.');
}

$level = Hierarchie::getById($hierarchieId, false);
if (!$level) {
    http_response_code(404);
    die('Niveau de hierarchie introuvable.');
}

$subTree = Hierarchie::getLevel($hierarchieId, false);
if (empty($subTree)) {
    http_response_code(404);
    die('Niveau de hierarchie introuvable.');
}

$collectLevels = function($nodes) use (&$collectLevels) {
    $flat = array();
    foreach ($nodes as $node) {
        $flat[] = $node;
        if (!empty($node['subItems']) && is_array($node['subItems'])) {
            $flat = array_merge($flat, $collectLevels($node['subItems']));
        }
    }
    return $flat;
};

$allLevels = $collectLevels($subTree);
$accessibleLevels = array();
foreach ($allLevels as $node) {
    $scopeNode = 'hierarchie:' . (int)$node['id'];
    $nodeRole = isset($currentUser['roles'][$scopeNode]) ? $currentUser['roles'][$scopeNode] : null;
    $nodeCanRead = ($nodeRole !== null && isset($roleRank[$nodeRole]) && $roleRank[$nodeRole] >= 1);

    if ($nodeCanRead) {
        $accessibleLevels[] = $node;
    }
}

if (empty($accessibleLevels)) {
    http_response_code(403);
    die('Acces refuse. Aucun niveau accessible pour cet export.');
}

$colorLabel = function($code) {
    $map = array(
        'vert' => 'Vert',
        'jaune' => 'Jaune',
        'orange' => 'Orange',
        'rouge' => 'Rouge',
    );

    return isset($map[$code]) ? $map[$code] : 'Non defini';
};

$dateColorClass = function($code) {
    $map = array(
        'vert' => 'date-couleur-vert',
        'jaune' => 'date-couleur-jaune',
        'orange' => 'date-couleur-orange',
        'rouge' => 'date-couleur-rouge',
    );

    return isset($map[$code]) ? $map[$code] : 'date-couleur-defaut';
};

Journal::logModification(
    $currentUser['username'],
    Journal::getIp(),
    'EXPORT',
    'pdf',
    $hierarchieId,
    'Export PDF niveau hierarchie: ' . $level['nom'] .
    ' (gradients=' . ($includeGradients ? '1' : '0') . ', jalons=' . ($includeJalons ? '1' : '0') . ')'
);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export PDF - <?php echo htmlspecialchars($level['nom'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/pdc.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #222; margin: 20px; line-height: 1.35; }
        h1, h2, h3, h4 { margin: 0 0 8px; }
        h1 { font-size: 26px; }
        h2 { font-size: 20px; margin-top: 18px; }
        h3 { font-size: 16px; margin-top: 14px; }
        .meta { color: #555; margin-bottom: 16px; }
        .no-print { margin-bottom: 14px; }
        .cover {
            border: 1px solid #d6dce5;
            border-radius: 10px;
            padding: 24px;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 42%);
            min-height: 180mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cover-header { border-bottom: 2px solid #27578a; padding-bottom: 12px; }
        .cover-kicker { color: #27578a; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; }
        .cover-title { font-size: 34px; margin: 6px 0 4px; color: #1f2f45; }
        .cover-subtitle { color: #5a6778; font-size: 15px; }
        .cover-grid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .cover-item {
            border: 1px solid #dbe3ee;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
        }
        .cover-label { font-size: 11px; color: #607086; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .cover-value { font-size: 16px; font-weight: bold; color: #1f2f45; word-break: break-word; }
        .cover-options { margin-top: 12px; }
        .cover-options ul { margin: 8px 0 0 18px; }
        .cover-footer {
            margin-top: 18px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            color: #59677a;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toc { border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
        .toc table { width: 100%; border-collapse: collapse; }
        .toc th, .toc td { border-bottom: 1px solid #ececec; padding: 6px 4px; text-align: left; font-size: 13px; }
        .toc .page-col { width: 110px; text-align: right; }
        .level-page { page-break-before: always; }
        .level-header { border-bottom: 2px solid #ddd; margin-bottom: 12px; padding-bottom: 8px; }
        .domaine { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 14px; overflow: hidden; }
        .domaine h3 { background: #f7f7f7; margin: 0; padding: 10px 12px; page-break-after: avoid; break-after: avoid-page; }
        .projet { padding: 10px 12px 12px; border-top: 1px solid #eee; }
        .projet:first-of-type { border-top: none; }
        .projet-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .projet-titre { font-weight: bold; margin-bottom: 20px;}
        .projet-dates { color: #555; font-size: 12px; }
        .projet-commentaire {
            margin-top: 6px;
            margin-bottom: 30px;
            padding: 8px 10px;
            border-left: 3px solid #b7c7da;
            background: #f7fafc;
            color: #334155;
            font-size: 12px;
            white-space: pre-wrap;
        }
        .pdc-frise-container { margin-top: 10px; }
        .pdc-frise { break-inside: avoid; page-break-inside: avoid; }
        .table-wrap { margin-top: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th, table.data-table td { border: 1px solid #e8e8e8; padding: 6px 8px; text-align: left; font-size: 12px; }
        table.data-table th { background: #f3f3f3; }
        table.data-table tbody tr:nth-child(even) { background: #f8fafc; }
        table.data-table th.col-date,
        table.data-table td.col-date {
            width: 1%;
            white-space: nowrap;
            text-align: center;
        }
        .date-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: bold;
            font-size: 11px;
            line-height: 1.3;
            border: 1px solid transparent;
            text-align: center;
        }
        .date-couleur-vert { background: #e8f6ef; color: #1b8f4b; border-color: #b8e2ca; }
        .date-couleur-jaune { background: #fff7dc; color: #9f7a00; border-color: #f3e2a1; }
        .date-couleur-orange { background: #fff0e4; color: #c25a00; border-color: #f2cba9; }
        .date-couleur-rouge { background: #fdeaea; color: #b62d2d; border-color: #efb6b6; }
        .date-couleur-defaut { background: #eef2f7; color: #334155; border-color: #d7dfe9; }
        .muted { color: #666; font-size: 12px; }
        .domaine-page-break { page-break-before: always; break-before: page; }
        .projet-page-break { page-break-before: always; break-before: page; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none; }
            body { margin: 10mm; }
            .domaine, .projet, .pdc-frise, .table-wrap { break-inside: avoid; page-break-inside: avoid; }
            .domaine h3 { page-break-after: avoid; break-after: avoid-page; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <section class="cover">
        <div>
            <div class="cover-header">
                <div class="cover-kicker">Plan de charge</div>
                <h1 class="cover-title">Rapport d'export PDF</h1>
                <div class="cover-subtitle">Synthese structuree des domaines et projets par niveau accessible.</div>
            </div>

            <div class="cover-grid">
                <div class="cover-item">
                    <div class="cover-label">Niveau courant</div>
                    <div class="cover-value"><?php echo htmlspecialchars($level['nom'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="cover-item">
                    <div class="cover-label">Niveaux inclus</div>
                    <div class="cover-value"><?php echo (int)count($accessibleLevels); ?></div>
                </div>
                <div class="cover-item">
                    <div class="cover-label">Date de debut</div>
                    <div class="cover-value"><?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="cover-item">
                    <div class="cover-label">Date de fin</div>
                    <div class="cover-value"><?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <div class="cover-options">
                <h3>Perimetre d'export</h3>
                <ul>
                    <li>Gradients: <?php echo $includeGradients ? 'Oui' : 'Non'; ?></li>
                    <li>Jalons: <?php echo $includeJalons ? 'Oui' : 'Non'; ?></li>
                </ul>
            </div>
        </div>

        <div class="cover-footer">
            <span>Document genere automatiquement depuis l'application PDC.</span>
            <span>Genere le <?php echo date('d/m/Y H:i'); ?></span>
        </div>
    </section>

    <section class="toc level-page">
        <h2>Table des matieres</h2>
        <table>
            <thead>
                <tr>
                    <th>Niveau</th>
                    <th class="page-col">Page (indicative)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accessibleLevels as $idx => $tocLevel): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tocLevel['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="page-col"><?php echo (int)($idx + 3); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php foreach ($accessibleLevels as $node): ?>
    <?php $domaines = Hierarchie::getDomainesByLevel((int)$node['id']); ?>
    <section class="level-page">
        <div class="level-header">
            <h2>Niveau: <?php echo htmlspecialchars($node['nom'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="meta">Domaines: <?php echo count($domaines); ?></div>
        </div>

        <?php if (empty($domaines)): ?>
        <p class="muted">Aucun domaine sur ce niveau.</p>
        <?php else: ?>
        <?php foreach ($domaines as $domaine): ?>
        <?php $projets = Projet::getByDomaine($domaine['id']); ?>
        <article class="domaine domaine-page-break">
            <h3><?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>

            <?php if (empty($projets)): ?>
            <div class="projet muted">Aucun projet.</div>
            <?php else: ?>
            <?php foreach ($projets as $projetIndex => $projet): ?>
            <?php
                $gradients = Projet::getGradients((int)$projet['id']);
                $jalons = Projet::getJalons((int)$projet['id']);
                if (!$includeGradients) {
                    $gradients = array();
                }
                if (!$includeJalons) {
                    $jalons = array();
                }
            ?>
            <section class="projet<?php echo ($projetIndex > 0) ? ' projet-page-break' : ''; ?>">
                <div class="projet-header">
                    <div class="projet-titre"><?php echo htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="projet-dates">
                        <?php echo htmlspecialchars(Helper::formatDate($projet['date_debut']), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(Helper::formatDate($projet['date_fin']), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <?php if (!empty($projet['commentaire'])): ?>
                <div class="projet-commentaire"><?php echo $projet['commentaire']; ?></div>
                <?php endif; ?>

                <div class="pdc-frise-container">
                    <div class="pdc-frise"
                        data-projet-id="<?php echo (int)$projet['id']; ?>"
                        data-date-debut="<?php echo htmlspecialchars($projet['date_debut'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-date-fin="<?php echo htmlspecialchars($projet['date_fin'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-periode-debut="<?php echo htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8'); ?>"
                        data-periode-fin="<?php echo htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8'); ?>"
                        data-gradients='<?php echo json_encode($gradients); ?>'
                        data-jalons='<?php echo json_encode($jalons); ?>'>
                    </div>
                </div>

                <?php if ($includeGradients): ?>
                <div class="table-wrap">
                    <h4>Tableau des gradients</h4>
                    <?php if (empty($gradients)): ?>
                    <p class="muted">Aucun gradient.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-date">Date</th>
                                <th>Libelle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gradients as $gradient): ?>
                            <tr>
                                <td class="col-date"><span class="date-badge <?php echo htmlspecialchars($dateColorClass($gradient['couleur']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(Helper::formatDate($gradient['date_gradient']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(isset($gradient['libelle']) ? $gradient['libelle'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($includeJalons): ?>
                <div class="table-wrap">
                    <h4>Tableau des jalons</h4>
                    <?php if (empty($jalons)): ?>
                    <p class="muted">Aucun jalon.</p>
                    <?php else: ?>
                    <?php
                        $jalonsById = array();
                        foreach ($jalons as $jalonItem) {
                            if (isset($jalonItem['id'])) {
                                $jalonsById[(int)$jalonItem['id']] = $jalonItem;
                            }
                        }
                    ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-date">Date</th>
                                <th>Libelle</th>
                                <th class="col-date">Date jalon precedent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jalons as $jalon): ?>
                            <?php
                                $previousDate = '';
                                $previousDateColorClass = 'date-couleur-defaut';
                                $refId = isset($jalon['jalon_reference_id']) ? (int)$jalon['jalon_reference_id'] : 0;
                                if ($refId > 0 && isset($jalonsById[$refId])) {
                                    $previousDate = Helper::formatDate($jalonsById[$refId]['date_jalon']);
                                    $previousDateColorClass = $dateColorClass(isset($jalonsById[$refId]['couleur']) ? $jalonsById[$refId]['couleur'] : '');
                                }
                            ?>
                            <tr>
                                <td class="col-date"><span class="date-badge <?php echo htmlspecialchars($dateColorClass($jalon['couleur']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(Helper::formatDate($jalon['date_jalon']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(isset($jalon['libelle']) ? $jalon['libelle'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="col-date">
                                    <?php if ($previousDate !== ''): ?>
                                    <span class="date-badge <?php echo htmlspecialchars($previousDateColorClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($previousDate, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
            <?php endforeach; ?>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>

    <script>
    var PDC = {
        appUrl: '<?php echo APP_URL; ?>',
        id: <?php echo (int)$hierarchieId; ?>,
        dateDebut: '<?php echo htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8'); ?>',
        dateFin: '<?php echo htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8'); ?>',
        hierarchieId: <?php echo (int)$hierarchieId; ?>,
        currentProjetId: null,
        currentDomaineId: null,
        readOnly: true
    };
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/jquery-ui.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/fontawesome.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/pdc.js"></script>
</body>
</html>