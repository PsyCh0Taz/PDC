<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

// Déterminer si admin ou responsable
$isAdmin = false;
$isResponsable = false;
foreach ($currentUser['roles'] as $dn => $role) {
    if ($role === 'admin') $isAdmin = true;
    if ($role === 'responsable') $isResponsable = true;
}

// Niveau de navigation : entreprise / departement / service / domaine
$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : 'entreprise';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Période affichée
$aujourdhui = new DateTime();
$moisCourant = (int)$aujourdhui->format('n');
$anneeCourante = (int)$aujourdhui->format('Y');

// Quadrimestre courant
if ($moisCourant >= 1 && $moisCourant <= 4) {
    $quadStart = $anneeCourante . '-01-01';
    $quadEnd   = $anneeCourante . '-04-30';
} elseif ($moisCourant >= 5 && $moisCourant <= 8) {
    $quadStart = $anneeCourante . '-05-01';
    $quadEnd   = $anneeCourante . '-08-31';
} else {
    $quadStart = $anneeCourante . '-09-01';
    $quadEnd   = $anneeCourante . '-12-31';
}

$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : $quadStart;
$dateFin   = isset($_GET['date_fin'])   ? $_GET['date_fin']   : $quadEnd;

$pageTitle = 'Plan de Charge';

// Breadcrumb
$breadcrumb = array();
$currentData = null;

switch ($niveau) {
    case 'entreprise':
        $entreprises = Organisation::getEntreprises();
        $currentData = array('type' => 'entreprises', 'items' => $entreprises);
        $breadcrumb[] = array('label' => 'Entreprises', 'link' => '?niveau=entreprise');
        break;

    case 'departement':
        if ($id) {
            $entreprise = Organisation::getEntrepriseById($id);
            $departements = Organisation::getDepartements($id);
            $currentData = array('type' => 'departements', 'items' => $departements, 'parent' => $entreprise);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '?niveau=entreprise');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '?niveau=departement&id=' . $id);
        }
        break;

    case 'service':
        if ($id) {
            $departement = Organisation::getDepartementById($id);
            $entreprise  = Organisation::getEntrepriseById($departement['entreprise_id']);
            $services    = Organisation::getServices($id);
            $currentData = array('type' => 'services', 'items' => $services, 'parent' => $departement);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '?niveau=entreprise');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '?niveau=departement&id=' . $entreprise['id']);
            $breadcrumb[] = array('label' => $departement['nom'], 'link' => '?niveau=service&id=' . $id);
        }
        break;

    case 'domaine':
        if ($id) {
            $service     = Organisation::getServiceById($id);
            $departement = Organisation::getDepartementById($service['departement_id']);
            $entreprise  = Organisation::getEntrepriseById($departement['entreprise_id']);
            $domaines    = Organisation::getDomainesByService($id);
            $currentData = array('type' => 'domaines', 'items' => $domaines, 'parent' => $service);
            $breadcrumb[] = array('label' => 'Entreprises', 'link' => '?niveau=entreprise');
            $breadcrumb[] = array('label' => $entreprise['nom'], 'link' => '?niveau=departement&id=' . $entreprise['id']);
            $breadcrumb[] = array('label' => $departement['nom'], 'link' => '?niveau=service&id=' . $departement['id']);
            $breadcrumb[] = array('label' => $service['nom'], 'link' => '?niveau=domaine&id=' . $id);
        }
        break;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pdc-container">

    <!-- Breadcrumb -->
    <ol class="breadcrumb pdc-breadcrumb">
        <?php foreach ($breadcrumb as $b): ?> 
            <li><a href="<?php echo htmlspecialchars($b['link'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
        <?php endforeach; ?>
    </ol>

    <!-- Barre d'outils -->
    <div class="pdc-toolbar">
        <div class="row">
            <div class="col-md-6">
                <form id="periode_form" method="get" class="form-inline pdc-periode-form">
                    <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($niveau, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                    <?php endif; ?>

                    <div class="form-group" style="display:flex; align-items:center;">
                        <label><i class="fa-regular fa-calendar"></i> Du</label>
                        <input type="text" class="form-control pdc-datepicker" name="date_debut" id="date_debut" value="<?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?>" required>
                        <label>au</label>
                        <input type="text" class="form-control pdc-datepicker" name="date_fin" id="date_fin" value="<?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?>" required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-info" id="btn-share" title="Générer un lien de partage">
                    <i class="fa-regular fa-share-from-square"></i> Partager
                </button>
                <button class="btn btn-success" id="btn-export-pdf" title="Exporter en PDF">
                    <i class="fa-regular fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Affichage selon le niveau -->
    <div class="pdc-content" id="pdc-content">
        <?php if ($currentData && $currentData['type'] === 'entreprises'): ?>
            <!-- Liste des entreprises -->
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3>
                        <a href="?niveau=departement&id=<?php echo $item['id']; ?>&date_debut=<?php echo urlencode($dateDebut); ?>&date_fin=<?php echo urlencode($dateFin); ?>">
                            <i class="fa-solid fa-building-columns"></i>
                            <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'departements'): ?>
            <!-- Liste des départements -->
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3>
                        <a href="?niveau=service&id=<?php echo $item['id']; ?>&date_debut=<?php echo urlencode($dateDebut); ?>&date_fin=<?php echo urlencode($dateFin); ?>">
                            <i class="fa-solid fa-sitemap"></i>
                            <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'services'): ?>
            <!-- Liste des services -->
            <div class="pdc-items-list">
                <?php foreach ($currentData['items'] as $item): ?>
                <div class="pdc-card">
                    <h3>
                        <a href="?niveau=domaine&id=<?php echo $item['id']; ?>&date_debut=<?php echo urlencode($dateDebut); ?>&date_fin=<?php echo urlencode($dateFin); ?>">
                            <i class="fa-solid fa-briefcase"></i>
                            <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentData && $currentData['type'] === 'domaines'): ?>
            <!-- Domaines et projets -->
            <div id="domaines-container" class="pdc-domaines-container">
                <?php foreach ($currentData['items'] as $domaine):
                    $projets = Projet::getByDomaine($domaine['id']);
                ?>
                <div class="pdc-domaine" data-domaine-id="<?php echo $domaine['id']; ?>">
                    <div class="pdc-domaine-header">
                        <h3 class="pdc-domaine-titre">
                            <?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            <button class="btn btn-xs btn-link pdc-edit-domaine" data-domaine-id="<?php echo $domaine['id']; ?>" title="Modifier le titre">
                                <i class="fa-solid fa-square-pen"></i>
                            </button>
                            <button class="btn btn-xs btn-link pdc-add-projet" data-domaine-id="<?php echo $domaine['id']; ?>" title="Ajouter un projet">
                                <i class="fa-solid fa-square-plus"></i>
                            </button>                            
                        </h3>
                    </div>
                    <div class="pdc-projets-list" data-domaine-id="<?php echo $domaine['id']; ?>">
                        <?php foreach ($projets as $projet):
                            $gradients = Projet::getGradients($projet['id']);
                            $jalons    = Projet::getJalons($projet['id']);
                        ?>
                        <div class="pdc-projet" data-projet-id="<?php echo $projet['id']; ?>">
                            <div class="pdc-projet-header">
                                <span class="pdc-projet-titre"><?php echo htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <button class="btn btn-xs btn-link pdc-edit-projet" data-projet-id="<?php echo $projet['id']; ?>" title="Modifier le projet">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                            </div>
                            <div class="pdc-frise-container">
                                <div class="pdc-frise" data-projet-id="<?php echo $projet['id']; ?>"
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
                        <div class="jjpdc-projet-placeholder"></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Bouton ajouter un domaine -->
                <?php
                // Vérifier si l'utilisateur peut modifier
                $canModify = false;
                if ($isAdmin) $canModify = true;
                elseif (isset($currentData['parent']['ldap_dn'])) {
                    $canModify = Auth::hasRole($currentUser['roles'], $currentData['parent']['ldap_dn'], 'modificateur');
                }
                ?>
                <?php if ($canModify): ?>
                <div class="pdc-add-domaine">
                    <button class="btn btn-success btn-lg" id="btn-add-domaine">
                        <i class="fa-solid fa-circle-plus"></i> Ajouter un domaine
                    </button>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
<!-- Modale : Ajouter un domaine -->

<div class="modal fade" id="modal-add-domaine" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nouveau domaine</h4>    
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new-domaine-nom">Nom du domaine</label>
                    <input type="text" class="form-control" id="new-domaine-nom" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-create-domaine">Créer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modale : Éditer un domaine -->
<div class="modal fade" id="modal-edit-domaine" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modifier le domaine</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="domaine-nom">Nom du domaine</label>
                    <input type="text" class="form-control" id="domaine-nom" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btn-delete-domaine">
                    <i class="fa-solid fa-trash-can"></i> Supprimer
                </button>
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-save-domaine">Enregistrer</button>
            </div>
        </div>
    </div>
</div>


<!-- Modale : Ajouter un projet -->
<div class="modal fade" id="modal-add-projet" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Créer un projet</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                        <div class="form-group">
                            <label for="projet-titre">Titre du projet</label>
                            <input type="text" class="form-control" id="new-projet-titre" required>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="projet-date-debut">Date de début</label>
                                    <input type="text" class="form-control pdc-datepicker" id="new-projet-date-debut" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="projet-date-fin">Date de fin</label>
                                    <input type="text" class="form-control pdc-datepicker" id="new-projet-date-fin" required>
                                </div>
                            </div>
                        </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                    <i class="fa-solid fa-x"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" id="btn-add-projet">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modale : Éditer un projet -->
<div class="modal fade" id="modal-edit-projet" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modifier le projet</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="projet-tabs">
                    <ul>
                        <li><a href="#tab-infos">Informations générales</a></li>
                        <li><a href="#tab-gradients">Gradients</a></li>
                        <li><a href="#tab-jalons">Jalons</a></li>
                    </ul>

                    <!-- Onglet : Informations générales -->
                    <div id="tab-infos">
                        <div class="form-group">
                            <label for="projet-titre">Titre du projet</label>
                            <input type="text" class="form-control" id="projet-titre" required>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="projet-date-debut">Date de début</label>
                                    <input type="text" class="form-control pdc-datepicker" id="projet-date-debut" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="projet-date-fin">Date de fin</label>
                                    <input type="text" class="form-control pdc-datepicker" id="projet-date-fin" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet : Gradients -->
                    <div id="tab-gradients">
                        <button type="button" class="btn btn-sm btn-success" id="btn-add-gradient">
                            <i class="fa-solid fa-circle-plus"></i> Ajouter un gradient
                        </button>
                        <table class="table table-bordered" id="gradients-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Couleur</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="gradients-list"></tbody>
                        </table>
                    </div>

                    <!-- Onglet : Jalons -->
                    <div id="tab-jalons">
                        <button type="button" class="btn btn-sm btn-success" id="btn-add-jalon">
                            <i class="fa-solid fa-circle-plus"></i> Ajouter un jalon
                        </button>
                        <table class="table table-bordered" id="jalons-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Couleur</th>
                                    <th>Libellé</th>
                                    <th>Jalon d'origine</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="jalons-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btn-delete-projet">
                    <i class="fa-solid fa-trash-can"></i> Supprimer le projet
                </button>
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                    <i class="fa-solid fa-x"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-projet">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modale : Générer lien de partage -->
<div class="modal fade" id="modal-share" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa-solid fa-share-from-square"></i></i> Lien de partage</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Ce lien donne accès en <strong>lecture seule</strong> à la vue courante.</p>
                <div class="form-group">
                    <label>URL de partage</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="share-url" readonly>
                        <span class="input-group-btn">
                            <button class="btn btn-default" id="btn-copy-share" title="Copier">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">00Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales pour le contexte
var PDC = {
    appUrl: '<?php echo APP_URL; ?>',
    niveau: '<?php echo htmlspecialchars($niveau, ENT_QUOTES, 'UTF-8'); ?>',
    id: <?php echo $id ? (int)$id : 'null'; ?>,
    dateDebut: '<?php echo htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8'); ?>',
    dateFin: '<?php echo htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8'); ?>',
    serviceId: <?php echo ($niveau === 'domaine' && $id) ? (int)$id : 'null'; ?>,
    currentProjetId: null,
    currentDomaineId: null,
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>