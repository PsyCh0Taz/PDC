<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

$hierarchieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-01-01');
$dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-12-31');

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

$domaines = Hierarchie::getDomainesByLevel($hierarchieId);

Journal::logModification(
    $currentUser['username'],
    Journal::getIp(),
    'EXPORT',
    'pdf',
    $hierarchieId,
    'Export PDF niveau hierarchie: ' . $level['nom']
);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - <?php echo htmlspecialchars($level['nom'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 24px; }
        h1 { margin: 0 0 6px; font-size: 26px; }
        .meta { color: #555; margin-bottom: 20px; }
        .domaine { border: 1px solid #ddd; border-radius: 6px; margin-bottom: 14px; }
        .domaine h2 { margin: 0; padding: 10px 12px; font-size: 18px; background: #f7f7f7; }
        .projet { padding: 10px 12px; border-top: 1px solid #eee; }
        .projet:first-of-type { border-top: none; }
        .projet-titre { font-weight: bold; }
        .projet-dates { color: #666; font-size: 12px; }
        .no-print { margin-bottom: 14px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <h1><?php echo htmlspecialchars($level['nom'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <div class="meta">
        Periode: du <?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?> au <?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?><br>
        Genere le <?php echo date('d/m/Y H:i'); ?>
    </div>

    <?php foreach ($domaines as $domaine): ?>
    <?php $projets = Projet::getByDomaine($domaine['id']); ?>
    <section class="domaine">
        <h2><?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (empty($projets)): ?>
        <div class="projet">Aucun projet.</div>
        <?php else: ?>
        <?php foreach ($projets as $projet): ?>
        <div class="projet">
            <div class="projet-titre"><?php echo htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="projet-dates">
                <?php echo htmlspecialchars(Helper::formatDate($projet['date_debut']), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(Helper::formatDate($projet['date_fin']), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>
</body>
</html>