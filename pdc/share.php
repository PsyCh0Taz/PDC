<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Récupérer le token
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    die('Token invalide.');
}

$link = ShareLink::getByToken($token);
if (!$link) {
    die('Lien de partage introuvable ou expiré.');
}

// Authentification requise
$currentUser = Auth::requireLogin();

// Journaliser l'accès
Journal::logConnexion($currentUser['username'], Journal::getIp(), true, $token);

// Décoder les paramètres URL
parse_str($link['url_params'], $params);

$niveau    = isset($params['niveau']) ? $params['niveau'] : 'entreprise';
$id        = isset($params['id']) ? (int)$params['id'] : null;
$dateDebut = isset($params['date_debut']) ? $params['date_debut'] : date('Y-01-01');
$dateFin   = isset($params['date_fin']) ? $params['date_fin'] : date('Y-12-31');

// Forcer le mode lecture seule
$readOnly = true;
$isAdmin = false;
$isResponsable = false;

// Charger les données (copier la logique de index.php)
$breadcrumb = array();
$currentData = null;

switch ($niveau) {
    case 'entreprise':
        $entreprises = Organisation::getEntreprises();
        $currentData = array('type' => 'entreprises', 'items' => $entreprises);
        $breadcrumb[] = array('label' => 'Entreprises', 'link' => '#');
        break;

    case 'departement':
        if ($id) {
            $entreprise = Organisation::getEntrepriseById($id);
            $departements = Organisation::getDepartements($id);
            $currentData = array('type' => 'departements', 'items' => $departements, 'parent' => $entreprise);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '#');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '#');
        }
        break;

    case 'service':
        if ($id) {
            $departement = Organisation::getDepartementById($id);
            $entreprise  = Organisation::getEntrepriseById($departement['entreprise_id']);
            $services    = Organisation::getServices($id);
            $currentData = array('type' => 'services', 'items' => $services, 'parent' => $departement);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '#');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '#');
            $breadcrumb[] = array('label' => $departement['nom'], 'link' => '#');
        }
        break;

    case 'domaine':
        if ($id) {
            $service     = Organisation::getServiceById($id);
            $departement = Organisation::getDepartementById($service['departement_id']);
            $entreprise  = Organisation::getEntrepriseById($departement['entreprise_id']);
            $domaines    = Organisation::getDomainesByService($id);
            $currentData = array('type' => 'domaines', 'items' => $domaines, 'parent' => $service);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '#');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '#');
            $breadcrumb[] = array('label' => $departement['nom'], 'link' => '#');
            $breadcrumb[] = array('label' => $service['nom'], 'link' => '#');
        }
        break;
}

$pageTitle = 'Plan de Charge (lecture seule)';
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pdc-container">

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        <strong>Mode lecture seule.</strong> Vous consultez un lien de partage. Aucune modification n'est possible.
    </div>

    <!-- Breadcrumb -->
    <ol class="breadcrumb pdc-breadcrumb">
        <?php foreach ($breadcrumb as $b): ?>
            <li><?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ol>

    <!-- Période -->
    <div class="pdc-toolbar">
        <div class="row">
            <div class="col-md-12">
                <p><strong>Période affichée :</strong> du <?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?> au <?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>

    <!-- Affichage selon le niveau (copie de index.php sans les boutons d'édition) -->
    <div class="pdc-content" id="pdc-content">
        <?php if ($currentData && $currentData['type'] === 'entreprises'): ?>
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3><i class="fa fa-building-o"></i> <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'departements'): ?>
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3><i class="fa fa-sitemap"></i> <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'services'): ?>
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3><i class="fa fa-briefcase"></i> <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'domaines'): ?>
            <div id="domaines-container" class="pdc-domaines-container">
                <?php foreach ($currentData['items'] as $domaine):
                    $projets = Projet::getByDomaine($domaine['id']);
                ?>
                <div class="pdc-domaine">
                    <div class="pdc-domaine-header">
                        <h3 class="pdc-domaine-titre"><?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    </div>
                    <div class="pdc-projets-list">
                        <?php foreach ($projets as $projet):
                            $gradients = Projet::getGradients($projet['id']);
                            $jalons    = Projet::getJalons($projet['id']);
                        ?>
                        <div class="pdc-projet">
                            <div class="pdc-projet-header">
                                <span class="pdc-projet-titre"><?php echo htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="pdc-frise-container">
                                <div class="pdc-frise"
                                     data-date-debut="<?php echo $projet['date_debut']; ?>"
                                     data-date-fin="<?php echo $projet['date_fin']; ?>"
                                     data-periode-debut="<?php echo $dateDebut; ?>"
                                     data-periode-fin="<?php echo $dateFin; ?>"
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
        <?php endif; ?>
    </div>

</div>

<script>
var PDC = {
    appUrl: '<?php echo APP_URL; ?>',
    niveau: '<?php echo htmlspecialchars($niveau, ENT_QUOTES, 'UTF-8'); ?>',
    id: <?php echo $id ? (int)$id : 'null'; ?>,
    dateDebut: '<?php echo htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8'); ?>',
    dateFin: '<?php echo htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8'); ?>',
    readOnly: true,
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>