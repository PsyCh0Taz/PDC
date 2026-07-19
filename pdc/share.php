<?php
require_once __DIR__ . '/includes/bootstrap.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
if ($token === '') {
    http_response_code(400);
    die('Token invalide.');
}

$link = ShareLink::getByToken($token);
if (!$link) {
    http_response_code(404);
    die('Lien de partage introuvable ou expire.');
}

$currentUser = Auth::requireLogin();

$params = array();
parse_str($link['url_params'], $params);

$hierarchieId = isset($params['id']) ? (int)$params['id'] : 0;
$dateDebut = isset($params['date_debut']) ? $params['date_debut'] : date('Y-01-01');
$dateFin = isset($params['date_fin']) ? $params['date_fin'] : date('Y-12-31');

if ($hierarchieId <= 0) {
    http_response_code(400);
    die('Niveau de hirarchie invalide.');
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
    die('Acces refuse. Vous ne pouvez pas consulter ce niveau.');
}

$level = Hierarchie::getById($hierarchieId, false);
if (!$level) {
    http_response_code(404);
    die('Niveau de hierarchie introuvable.');
}

Journal::logConnexion($currentUser['username'], Journal::getIp(), true, $token);

$domaines = Hierarchie::getDomainesByLevel($hierarchieId);

$pageTitle = 'Plan de Charge (lecture seule) - ' . $level['nom'];
$isAdmin = false;
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pdc-container">
    <div class="alert alert-info" role="alert">
        <i class="fa-solid fa-circle-info"></i>
        <strong>Mode lecture seule.</strong> Aucun ajout, modification ou suppression n'est autorise.
    </div>

    <div class="pdc-toolbar">
        <p><strong>Niveau:</strong> <?php echo htmlspecialchars($level['nom'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Periode:</strong> du <?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?> au <?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div id="domaines-container" class="pdc-domaines-container">
        <?php foreach ($domaines as $domaine): ?>
        <?php $projets = Projet::getByDomaine($domaine['id']); ?>
        <div class="pdc-domaine" data-domaine-id="<?php echo (int)$domaine['id']; ?>">
            <div class="pdc-domaine-header">
                <h3 class="pdc-domaine-titre"><?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
            </div>
            <div class="pdc-projets-list" data-domaine-id="<?php echo (int)$domaine['id']; ?>">
                <?php foreach ($projets as $projet): ?>
                <?php
                    $gradients = Projet::getGradients($projet['id']);
                    $jalons = Projet::getJalons($projet['id']);
                ?>
                <div class="pdc-projet" data-projet-id="<?php echo (int)$projet['id']; ?>">
                    <div class="pdc-projet-header">
                        <span class="pdc-projet-titre"><?php echo htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="badge bg-secondary pdc-projet-periode-badge">
                            <?php echo htmlspecialchars(Helper::formatDate($projet['date_debut']), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(Helper::formatDate($projet['date_fin']), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <?php if (!empty($projet['commentaire'])): ?>
                    <div class="pdc-commentaire"><?php echo $projet['commentaire']; ?></div>
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
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

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

<?php include __DIR__ . '/includes/footer.php'; ?>