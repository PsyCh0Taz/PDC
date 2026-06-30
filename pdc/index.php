<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

// Déterminer si admin ou responsable
$isAdmin = false;
foreach ($currentUser['roles'] as $dn => $role) {
    if ($dn === '*' && $role === 'admin') $isAdmin = true;
}
$roleRank = array('lecteur' => 1, 'modificateur' => 2);
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$tmpHierarchie=($id ? Hierarchie::getLevel($id) : Hierarchie::getAll());
$tmpRevHierarchie=($id ? Hierarchie::getUpperLevel(Hierarchie::getAll(), $id) : null);

if ($id && empty($tmpHierarchie)) {
    $id = 0;
    $tmpHierarchie = Hierarchie::getAll();
    $tmpRevHierarchie = null;
}

$hierarchieItems = $id
    ? (!empty($tmpHierarchie[0]['subItems']) ? $tmpHierarchie[0]['subItems'] : array())
    : $tmpHierarchie;

$currentLevelScope = $id > 0 ? ('hierarchie:' . $id) : null;
$currentLevelRole = ($id > 0 && isset($currentUser['roles'][$currentLevelScope])) ? $currentUser['roles'][$currentLevelScope] : null;
$canReadCurrentLevel = ($id > 0) && (
    ($currentLevelRole !== null && isset($roleRank[$currentLevelRole]) && $roleRank[$currentLevelRole] >= 1)
);
$showNoReadAlert = ($id > 0 && !$canReadCurrentLevel);
$canModifyCurrentLevel = ($id > 0) && (
    ($currentLevelRole !== null && isset($roleRank[$currentLevelRole]) && $roleRank[$currentLevelRole] >= 2)
);
$canShareExportCurrentLevel = ($id > 0) && $canReadCurrentLevel;

$editableHierarchyIds = array();
foreach ($currentUser['roles'] as $scope => $role) {
    if ($role !== 'modificateur') {
        continue;
    }
    if (strpos($scope, 'hierarchie:') !== 0) {
        continue;
    }
    $scopeId = (int)substr($scope, strlen('hierarchie:'));
    if ($scopeId > 0) {
        $editableHierarchyIds[$scopeId] = true;
    }
}

function flattenHierarchyForSelect(array $nodes, $depth = 0, array $allowedIds = array()) {
    $result = array();
    foreach ($nodes as $node) {
        $nodeId = (int)$node['id'];
        $result[] = array(
            'id' => $nodeId,
            'nom' => $node['nom'],
            'depth' => (int)$depth,
            'can_edit' => isset($allowedIds[$nodeId]),
        );
        if (!empty($node['subItems'])) {
            $children = flattenHierarchyForSelect($node['subItems'], $depth + 1, $allowedIds);
            foreach ($children as $child) {
                $result[] = $child;
            }
        }
    }
    return $result;
}

$editableHierarchyOptions = flattenHierarchyForSelect(Hierarchie::getAll(false), 0, $editableHierarchyIds);

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



// Breadcrumb
$breadcrumb = array();
$currentData = null;

$breadcrumb[] = array('label' => APP_NAME, 'link' => '?id=0');
if ( $tmpRevHierarchie ) {
    foreach( $tmpRevHierarchie as $item ) {
        $breadcrumb[] = array(
            'label' => $item['nom'],
            'link'  => '?id=' . $item['id']
        );
    }
}
$pageTitle = "Plan de charge" . (($id && !empty($tmpHierarchie[0]['nom'])) ? " - " . $tmpHierarchie[0]['nom'] : "");

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pdc-container">

    <div class="pdc-topbar">
        <ol class="breadcrumb pdc-breadcrumb pdc-breadcrumb-inline">
            <?php foreach ($breadcrumb as $b): ?> 
                <li><a href="<?php echo htmlspecialchars($b['link'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($b['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            <?php endforeach; ?>
        </ol>

        <div class="pdc-topbar-actions">
            <form id="periode_form" method="get" class="form-inline pdc-periode-form pdc-periode-form-inline">
                <?php if ($id): ?>
                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label><i class="fa-regular fa-calendar"></i> Du</label>
                    <input type="text" class="form-control pdc-datepicker" name="date_debut" id="date_debut" value="<?php echo htmlspecialchars(Helper::formatDate($dateDebut), ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label>&nbsp;au&nbsp;</label>
                    <input type="text" class="form-control pdc-datepicker" name="date_fin" id="date_fin" value="<?php echo htmlspecialchars(Helper::formatDate($dateFin), ENT_QUOTES, 'UTF-8'); ?>" required>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
                </div>
            </form>

            <div class="pdc-topbar-buttons">
                <?php if ($canShareExportCurrentLevel): ?>
                <button class="btn btn-info" id="btn-share" title="Générer un lien de partage">
                    <i class="fa-regular fa-share-from-square"></i> Partager
                </button>
                <button class="btn btn-success" id="btn-export-pdf" title="Exporter en PDF">
                    <i class="fa-regular fa-file-pdf"></i> Export PDF
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="pdc-content" id="pdc-content">
        <div class="pdc-items-list">
                <?php if (!empty($hierarchieItems)): ?>
                <?php foreach ( $hierarchieItems as $item): ?>
                <?php $subLevelCount = !empty($item['subItems']) ? count($item['subItems']) : 0; ?>
                <div class="pdc-card">
                    <h3>
                        <a href="?id=<?php echo $item['id']; ?>&date_debut=<?php echo urlencode($dateDebut); ?>&date_fin=<?php echo urlencode($dateFin); ?>">
                            <i class="fa-solid fa-diagram-project"></i>
                            <?php echo htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($subLevelCount > 0): ?>
                            <span class="badge bg-secondary" style="display:table; margin:6px 0 0 auto; font-size:0.7rem; padding:0.2rem 0.45rem; line-height:1;">
                                <?php echo $subLevelCount; ?> sous-niveau<?php echo ($subLevelCount > 1 ? 'x' : ''); ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </h3>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
        </div>

        <?php if ($showNoReadAlert): ?>
        <br>
        <div class="alert alert-warning" role="alert" style="margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Vous n'avez pas les droits de lecture des domaines et projets sur ce niveau.
        </div>
        <?php endif; ?>
            <div id="domaines-container" class="pdc-domaines-container">
            <?php 
                $domainesList = $canReadCurrentLevel ? Hierarchie::getDomainesByLevel($id) : array();
                foreach ($domainesList as $domaine):
                $projets = Projet::getByDomaine($domaine['id']);
            ?>
            <div class="pdc-domaine" data-domaine-id="<?php echo $domaine['id']; ?>">
                <div class="pdc-domaine-header">
                    <h3 class="pdc-domaine-titre">
                        <?php echo htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($canModifyCurrentLevel): ?>
                        <button class="btn btn-xs btn-link pdc-edit-domaine" data-domaine-id="<?php echo $domaine['id']; ?>" title="Modifier le titre">
                            <i class="fa-solid fa-square-pen"></i>
                        </button>
                        <button class="btn btn-xs btn-link pdc-add-projet" data-domaine-id="<?php echo $domaine['id']; ?>" title="Ajouter un projet">
                            <i class="fa-solid fa-square-plus"></i>
                        </button>
                        <?php endif; ?>
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
                            <span class="badge bg-secondary pdc-projet-periode-badge">
                                <?php echo htmlspecialchars(Helper::formatDate($projet['date_debut']), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(Helper::formatDate($projet['date_fin']), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if ($canModifyCurrentLevel): ?>
                            <button class="btn btn-xs btn-link pdc-edit-projet" data-projet-id="<?php echo $projet['id']; ?>" title="Modifier le projet">
                                <i class="fa-solid fa-square-pen"></i>
                            </button>
                            <?php endif; ?>
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
                    <div></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Bouton ajouter un domaine -->
            <?php if ($canModifyCurrentLevel): ?>
            <div class="pdc-add-domaine">
                <button class="btn btn-success btn-lg" id="btn-add-domaine" data-hierarchie-id="<?php echo (int)$id; ?>">
                    <i class="fa-solid fa-circle-plus"></i> Ajouter un domaine
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php if ($canModifyCurrentLevel): ?>
<!-- Modale : Ajouter un domaine -->

<div class="modal fade" id="modal-add-domaine" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nouveau domaine</h4>    
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new-domaine-nom">Nom du domaine</label>
                    <input type="text" class="form-control" id="new-domaine-nom" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-create-domaine"><i class="fa-solid fa-floppy-disk"></i> Créer</button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="domaine-nom">Nom du domaine</label>
                    <input type="text" class="form-control" id="domaine-nom" required>
                </div>
                <div class="form-group">
                    <label for="domaine-hierarchie-id">Niveau hiérarchique</label>
                    <select class="form-control" id="domaine-hierarchie-id" required>
                        <?php foreach ($editableHierarchyOptions as $hierOption): ?>
                        <option value="<?php echo (int)$hierOption['id']; ?>"<?php echo ((int)$hierOption['id'] === (int)$id ? ' selected' : ''); ?><?php echo (!empty($hierOption['can_edit']) ? '' : ' disabled'); ?>>
                            <?php echo str_repeat('-- ', (int)$hierOption['depth']) . htmlspecialchars($hierOption['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btn-delete-domaine">
                    <i class="fa-solid fa-trash-can"></i> Supprimer
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-domaine"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-primary" id="btn-add-projet"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-primary" id="btn-save-projet"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canShareExportCurrentLevel): ?>
<!-- Modale : Générer lien de partage -->
<div class="modal fade" id="modal-share" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa-solid fa-share-from-square"></i></i> Lien de partage</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
<?php endif; ?>

<script>
// Variables globales pour le contexte
var PDC = {
    appUrl: '<?php echo APP_URL; ?>',
    niveau: 'hierarchie',
    id: <?php echo (int)$id; ?>,
    dateDebut: '<?php echo htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8'); ?>',
    dateFin: '<?php echo htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8'); ?>',
    hierarchieId: <?php echo (int)$id; ?>,
    currentProjetId: null,
    currentDomaineId: null,
};

// Création de projet - Convertir dates du format français (dd/mm/yyyy) au format ISO (yyyy-mm-dd)
function convertDateToISO(dateStr) {
    if (!dateStr) return '';
    if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) return dateStr; // Déjà au format ISO
    var parts = dateStr.split('/');
    if (parts.length !== 3) return dateStr;
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    if (year.length === 2) {
        year = (year < 50) ? '20' + year : '19' + year;
    }
    return year + '-' + month + '-' + day;
}

document.addEventListener('DOMContentLoaded', function() {
    // Clic sur bouton "Ajouter un projet"
    document.querySelectorAll('.pdc-add-projet').forEach(btn => {
        btn.addEventListener('click', function() {
            PDC.currentDomaineId = parseInt(this.dataset.domaineId);
            // Réinitialiser le formulaire
            document.getElementById('new-projet-titre').value = '';
            document.getElementById('new-projet-date-debut').value = '';
            document.getElementById('new-projet-date-fin').value = '';
            // Afficher la modale
            var modal = new bootstrap.Modal(document.getElementById('modal-add-projet'));
            modal.show();
        });
    });

    // Clic sur bouton "Enregistrer" dans la modale
    var btnAddProjet = document.getElementById('btn-add-projet');
    if (btnAddProjet) {
        btnAddProjet.addEventListener('click', function() {
            var titre = document.getElementById('new-projet-titre').value.trim();
            var dateDebut = convertDateToISO(document.getElementById('new-projet-date-debut').value);
            var dateFin = convertDateToISO(document.getElementById('new-projet-date-fin').value);

            if (!titre || !dateDebut || !dateFin) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            if (new Date(dateDebut) > new Date(dateFin)) {
                alert('La date de début doit être antérieure à la date de fin');
                return;
            }

            // Envoyer à l'API
            var formData = new FormData();
            formData.append('action', 'create_projet');
            formData.append('domaine_id', PDC.currentDomaineId);
            formData.append('titre', titre);
            formData.append('date_debut', dateDebut);
            formData.append('date_fin', dateFin);

            fetch(PDC.appUrl + '/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fermer la modale et recharger
                    bootstrap.Modal.getInstance(document.getElementById('modal-add-projet')).hide();
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la création du projet');
            });
        });
    }
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>