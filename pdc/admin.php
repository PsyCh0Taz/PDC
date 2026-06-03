<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

// Vérifier les droits d'accès
$isAdmin = false;
$isResponsable = false;
foreach ($currentUser['roles'] as $dn => $role) {
    if ($role === 'admin') $isAdmin = true;
    if ($role === 'responsable') $isResponsable = true;
}

if (!$isAdmin && !$isResponsable) {
    http_response_code(403);
    die('Accès refusé. Vous n\'avez pas les permissions d\'administration.');
}

$pageTitle = 'Administration';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'services';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    $ip = Journal::getIp();
    
    switch ($action) {
        case 'toggle_service':
            if ($isAdmin || $isResponsable) {
                $serviceId = (int)$_POST['service_id'];
                $active = (int)$_POST['active'];
                $service = Organisation::getServiceById($serviceId);
                if ($service) {
                    $db = Database::getInstance();
                    $db->execute(
                        'UPDATE services SET actif = ? WHERE id = ?',
                        array($active, $serviceId)
                    );
                    Journal::logModification(
                        $currentUser['username'],
                        $ip,
                        $active ? 'ACTIVER' : 'DESACTIVER',
                        'service',
                        $serviceId,
                        'Service ' . $service['nom'] . ' ' . ($active ? 'activé' : 'désactivé')
                    );
                }
            }
            break;

        case 'set_role':
            if ($isAdmin || $isResponsable) {
                $username = isset($_POST['username']) ? $_POST['username'] : '';
                $dn = isset($_POST['dn']) ? $_POST['dn'] : '';
                $role = isset($_POST['role']) ? $_POST['role'] : '';
                $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;

                if ($username && $dn && in_array($role, array('admin', 'responsable', 'modificateur', 'lecteur'))) {
                    $db = Database::getInstance();
                    
                    if ($enabled) {
                        // Retirer l'ancien rôle s'il existe
                        $db->execute('DELETE FROM utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?', 
                            array($username, $dn, $role));
                        // Ajouter le nouveau rôle
                        $db->insert(
                            'INSERT INTO utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
                            array($username, $dn, $role)
                        );
                        Journal::logModification(
                            $currentUser['username'],
                            $ip,
                            'ASSIGN_ROLE',
                            'user',
                            0,
                            "Rôle '$role' assigné à $username"
                        );
                    } else {
                        // Retirer le rôle
                        $db->execute('DELETE FROM utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                            array($username, $dn, $role));
                        Journal::logModification(
                            $currentUser['username'],
                            $ip,
                            'REVOKE_ROLE',
                            'user',
                            0,
                            "Rôle '$role' retiré à $username"
                        );
                    }
                }
            }
            break;

        case 'update_settings':
            if ($isAdmin) {
                $logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
                $titre = isset($_POST['titre']) ? $_POST['titre'] : '';

                if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/assets/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $filename = 'logo_' . time() . '.' . pathinfo($logo['name'], PATHINFO_EXTENSION);
                    if (move_uploaded_file($logo['tmp_name'], $uploadDir . $filename)) {
                        $db = Database::getInstance();
                        $db->execute(
                            'UPDATE parametres SET valeur = ? WHERE cle = ?',
                            array( '/assets/uploads/' . $filename, 'logo_url')
                        );
                        Journal::logModification(
                            $currentUser['username'],
                            $ip,
                            'UPDATE_SETTINGS',
                            'parametres',
                            0,
                            'Logo mis à jour'
                        );
                    }
                }

                if (!empty($titre)) {
                    $db = Database::getInstance();
                    $db->execute(
                        'UPDATE parametres SET valeur = ? WHERE cle = ?',
                        array($titre, 'titre_pdf')
                    );
                    Journal::logModification(
                        $currentUser['username'],
                        $ip,
                        'UPDATE_SETTINGS',
                        'parametres',
                        0,
                        'Titre PDF mis à jour'
                    );
                }
            }
            break;

        case 'purge_log':
            if ($isAdmin) {
                $type = isset($_POST['log_type']) ? $_POST['log_type'] : '';
                
                if ($type === 'modifications') {
                    $db->execute('DELETE FROM journal_modifications', array());
                    Journal::logModification(
                        $currentUser['username'],
                        $ip,
                        'PURGE_LOG',
                        'journal_modifications',
                        0,
                        'Purge du journal des modifications'
                    );
                    $purgeMessage = 'Journal des modifications purgé.';
                } elseif ($type === 'connexions') {
                    $db->execute('DELETE FROM journal_connexions', array());
                    Journal::logModification(
                        $currentUser['username'],
                        $ip,
                        'PURGE_LOG',
                        'journal_connexions',
                        0,
                        'Purge du journal des connexions'
                    );
                    $purgeMessage = 'Journal des connexions purgé.';
                }
            }
            break;
    }
}

// Charger les données
$entreprises = $isAdmin ? Organisation::getAllEntreprises() : Organisation::getEntreprises();
$parametres = array();
$db = Database::getInstance();
$params = $db->fetchAll('SELECT cle, valeur FROM parametres');
foreach ($params as $p) {
    $parametres[$p['cle']] = $p['valeur'];
}

include __DIR__ . '/includes/header.php';

$purgeMessage = null;
?>

<div class="container-fluid pdc-container pdc-admin">
    <?php if (!empty($purgeMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($purgeMessage, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Onglets -->
    <ul class="nav nav-tabs pdc-admin-tabs" role="tablist">
        <li role="presentation" <?php echo $tab === 'services' ? 'class="active"' : ''; ?>>
            <a href="?tab=services" role="tab"><i class="fa-solid fa-building"></i> Services</a>
        </li>
        <li role="presentation" <?php echo $tab === 'droits' ? 'class="active"' : ''; ?>>
            <a href="?tab=droits" role="tab"><i class="fa-solid fa-user-lock"></i> Droits utilisateurs</a>
        </li>
        <?php if ($isAdmin): ?>
        <li role="presentation" <?php echo $tab === 'organisation' ? 'class="active"' : ''; ?>>
            <a href="?tab=organisation" role="tab"><i class="fa-solid fa-diagram-project"></i> Organisation</a>
        </li>
        <li role="presentation" <?php echo $tab === 'parametres' ? 'class="active"' : ''; ?>>
            <a href="?tab=parametres" role="tab"><i class="fa-solid fa-sliders"></i> Paramètres</a>
        </li>
        <li role="presentation" <?php echo $tab === 'journal' ? 'class="active"' : ''; ?>>
            <a href="?tab=journal" role="tab"><i class="fa-solid fa-book"></i> Journaux</a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content pdc-admin-content">

        <!-- TAB: Services -->
        <?php if ($tab === 'services'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Gestion des hiérarchies</h2>
                <p class="text-muted">Cochez ou décochez les services pour les activer/désactiver dans l'application.</p>

                <div class="table-responsive pdc-services-list">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Actif</th>
                                <th>Entreprise</th>
                                <th>Département</th>
                                <th>Service</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($entreprises as $entreprise):
                                $departements = $isAdmin ? 
                                    $db->fetchAll('SELECT * FROM departements WHERE entreprise_id = ? ORDER BY ordre, nom', array($entreprise['id'])) :
                                    Organisation::getDepartements($entreprise['id']);
                                
                                foreach ($departements as $dept):
                                    $services = $isAdmin ?
                                        $db->fetchAll('SELECT * FROM services WHERE departement_id = ? ORDER BY ordre, nom', array($dept['id'])) :
                                        Organisation::getServices($dept['id']);
                                    
                                    foreach ($services as $service):
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="service-toggle" 
                                        data-service-id="<?php echo $service['id']; ?>"
                                        <?php echo $service['actif'] ? 'checked' : ''; ?> />
                                </td>
                                <td><?php echo htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($dept['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($service['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <?php
                                    endforeach;
                                endforeach;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: Droits utilisateurs -->
        <?php if ($tab === 'droits'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Gestion des droits utilisateurs</h2>
                <p class="text-muted">Attribuez les rôles (Responsable, Modificateur, Lecteur) aux utilisateurs.</p>

                <div id="users-loading" class="alert alert-info">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement des utilisateurs...
                </div>

                <div class="table-responsive pdc-roles-list" id="users-container" style="display: none;">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th style="width: 120px;">Responsable</th>
                                <th style="width: 120px;">Modificateur</th>
                                <th style="width: 120px;">Lecteur</th>
                            </tr>
                        </thead>
                        <tbody id="utilisateurs-tbody">
                            <!-- Chargé dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: Organisation (Admin seulement) -->
        <?php if ($isAdmin && $tab === 'organisation'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Gestion de l'organisation</h2>
                <p class="text-muted">Gérez les entreprises, départements et services.</p>

                <div class="pdc-org-toolbar mb-3">
                    <button class="btn btn-info" id="btn-load-ldap">
                        <i class="fa-solid fa-sync"></i> Charger depuis LDAP
                    </button>
                    <button class="btn btn-success" id="btn-add-entreprise" data-bs-toggle="modal" data-bs-target="#modal-add-org-item" data-item-type="entreprise">
                        <i class="fa-solid fa-plus"></i> Ajouter une entreprise
                    </button>
                </div>

                <div id="org-loading" class="alert alert-info" style="display: none;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement de l'organisation...
                </div>

                <div id="org-container">
                    <div class="accordion" id="orgAccordion">
                        <?php
                        $entreprises = Organisation::getAllEntreprises();
                        foreach ($entreprises as $entreprise):
                            $departements = $db->fetchAll('SELECT * FROM departements WHERE entreprise_id = ? ORDER BY ordre ASC', array($entreprise['id']));
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ent-<?php echo $entreprise['id']; ?>">
                                    <i class="fa-solid fa-building"></i>
                                    <span class="ms-2"><?php echo htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="badge bg-secondary ms-auto">
                                        <i class="fa-solid fa-trash-can fa-sm" style="cursor: pointer;" onclick="deleteOrgItem('entreprise', <?php echo $entreprise['id']; ?>, event)"></i>
                                    </span>
                                </button>
                            </h2>
                            <div id="collapse-ent-<?php echo $entreprise['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#orgAccordion">
                                <div class="accordion-body">
                                    <button class="btn btn-sm btn-success mb-3" onclick="showAddOrgModal('departement', <?php echo $entreprise['id']; ?>)">
                                        <i class="fa-solid fa-plus"></i> Ajouter un département
                                    </button>
                                    <div class="accordion" id="orgAccordion-dept-<?php echo $entreprise['id']; ?>">
                                        <?php foreach ($departements as $departement):
                                            $services = $db->fetchAll('SELECT * FROM services WHERE departement_id = ? ORDER BY ordre ASC', array($departement['id']));
                                        ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-dept-<?php echo $departement['id']; ?>">
                                                    <i class="fa-solid fa-sitemap"></i>
                                                    <span class="ms-2"><?php echo htmlspecialchars($departement['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="badge bg-danger ms-auto">
                                                        <i class="fa-solid fa-trash-can fa-sm" style="cursor: pointer;" onclick="deleteOrgItem('departement', <?php echo $departement['id']; ?>, event)"></i>
                                                    </span>
                                                </button>
                                            </h2>
                                            <div id="collapse-dept-<?php echo $departement['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#orgAccordion-dept-<?php echo $entreprise['id']; ?>">
                                                <div class="accordion-body">
                                                    <button class="btn btn-sm btn-success mb-3" onclick="showAddOrgModal('service', <?php echo $departement['id']; ?>)">
                                                        <i class="fa-solid fa-plus"></i> Ajouter un service
                                                    </button>
                                                    <ul class="list-group">
                                                        <?php foreach ($services as $service): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span>
                                                                <i class="fa-solid fa-briefcase"></i>
                                                                <?php echo htmlspecialchars($service['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                            <button class="btn btn-sm btn-danger" onclick="deleteOrgItem('service', <?php echo $service['id']; ?>, event)">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: Paramètres (Admin seulement) -->
        <?php if ($isAdmin && $tab === 'parametres'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Paramètres généraux</h2>
                
                <form method="POST" enctype="multipart/form-data" class="pdc-settings-form">
                    <input type="hidden" name="action" value="update_settings" />
                    
                    <div class="form-group">
                        <label for="titre">Titre (pour les exports PDF)</label>
                        <input type="text" class="form-control" id="titre" name="titre" 
                            value="<?php echo htmlspecialchars(!empty($parametres['titre_pdf']) ? $parametres['titre_pdf'] : '', ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo (pour les exports PDF)</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" />
                        <?php if (!empty($parametres['logo_url'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo APP_URL . htmlspecialchars($parametres['logo_url'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    class="img-thumbnail" style="max-height: 100px;" />
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: Journaux (Admin seulement) -->
        <?php if ($isAdmin && $tab === 'journal'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Journaux système</h2>
                
                <!-- Sous-onglets -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="nav-item">
                        <a href="#journal-modifications" role="tab" class="nav-link active" data-bs-toggle="tab">
                            <i class="fa-solid fa-pen"></i> Modifications
                        </a>
                    </li>
                    <li role="presentation" class="nav-item">
                        <a href="#journal-connexions" role="tab" class="nav-link" data-bs-toggle="tab">
                            <i class="fa-solid fa-sign-in-alt"></i> Connexions
                        </a>
                    </li>
                </ul>

                <!-- Contenu des sous-onglets -->
                <div class="tab-content">

                    <!-- Modifications -->
                    <div role="tabpanel" class="tab-pane active" id="journal-modifications">
                        <div class="pdc-journal-actions" style="margin-bottom: 20px;">
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#purgeModificationsModal">
                                <i class="fa-solid fa-trash"></i> Purger les modifications
                            </button>
                        </div>
                        <div class="table-responsive pdc-journal-list">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Date/Heure</th>
                                        <th style="width: 120px;">Utilisateur</th>
                                        <th style="width: 100px;">Action</th>
                                        <th>Description</th>
                                        <th style="width: 100px;">IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $modifications = $db->fetchAll(
                                        'SELECT * FROM journal_modifications ORDER BY date_heure DESC LIMIT 500'
                                    );
                                    foreach ($modifications as $mod):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mod['date_heure'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($mod['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($mod['action'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars($mod['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><code><?php echo htmlspecialchars($mod['ip'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Connexions -->
                    <div role="tabpanel" class="tab-pane" id="journal-connexions">
                        <div class="pdc-journal-actions" style="margin-bottom: 20px;">
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#purgeConnexionsModal">
                                <i class="fa-solid fa-trash"></i> Purger les connexions
                            </button>
                        </div>
                        <div class="table-responsive pdc-journal-list">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Date/Heure</th>
                                        <th style="width: 120px;">Utilisateur</th>
                                        <th style="width: 100px;">Via partage</th>
                                        <th style="width: 100px;">IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $connexions = $db->fetchAll(
                                        'SELECT * FROM journal_connexions ORDER BY date_heure DESC LIMIT 500'
                                    );
                                    foreach ($connexions as $cnx):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cnx['date_heure'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($cnx['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($cnx['via_partage']): ?>
                                                <span class="badge bg-warning">Partage</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Directe</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($cnx['ip'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<!-- Script pour les actions AJAX -->
<script>
// Charger les utilisateurs et leurs rôles
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('utilisateurs-tbody')) {
        loadUsers();
    }
});

function loadUsers() {
    const formData = new FormData();
    formData.append('action', 'get_users_tree');
    
    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderUsersTable(data.users, data.roles);
        } else {
            console.error('Erreur:', data.error);
            document.getElementById('users-loading').innerHTML = '<div class="alert alert-danger">Erreur: ' + (data.error || 'Erreur inconnue') + '</div>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('users-loading').innerHTML = '<div class="alert alert-danger">Erreur de chargement</div>';
    });
}

function renderUsersTable(users, rolesMap) {
    const tbody = document.getElementById('utilisateurs-tbody');
    tbody.innerHTML = '';
    
    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong>${htmlEscape(user.displayname)}</strong>
                <br><small class="text-muted">${htmlEscape(user.username)}</small>
            </td>
            <td>
                <input type="checkbox" class="role-checkbox" 
                    data-username="${htmlEscape(user.username)}"
                    data-dn="${htmlEscape(user.dn)}"
                    data-role="responsable"
                    ${hasRole(rolesMap, user.username, user.dn, 'responsable') ? 'checked' : ''} />
            </td>
            <td>
                <input type="checkbox" class="role-checkbox"
                    data-username="${htmlEscape(user.username)}"
                    data-dn="${htmlEscape(user.dn)}"
                    data-role="modificateur"
                    ${hasRole(rolesMap, user.username, user.dn, 'modificateur') ? 'checked' : ''} />
            </td>
            <td>
                <input type="checkbox" class="role-checkbox"
                    data-username="${htmlEscape(user.username)}"
                    data-dn="${htmlEscape(user.dn)}"
                    data-role="lecteur"
                    ${hasRole(rolesMap, user.username, user.dn, 'lecteur') ? 'checked' : ''} />
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    // Attach event listeners
    document.querySelectorAll('.role-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', setUserRole);
    });
    
    // Hide loading, show table
    document.getElementById('users-loading').style.display = 'none';
    document.getElementById('users-container').style.display = 'block';
}

function hasRole(rolesMap, username, dn, role) {
    const key = username + '::' + dn;
    return rolesMap[key] && rolesMap[key].includes(role);
}

function htmlEscape(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function setUserRole(e) {
    const checkbox = e.target;
    const username = checkbox.dataset.username;
    const dn = checkbox.dataset.dn;
    const role = checkbox.dataset.role;
    const enabled = checkbox.checked ? 1 : 0;
    
    const formData = new FormData();
    formData.append('action', 'set_user_role');
    formData.append('username', username);
    formData.append('dn', dn);
    formData.append('role', role);
    formData.append('enabled', enabled);
    
    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Erreur:', data.error);
            checkbox.checked = !checkbox.checked; // Revert
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        checkbox.checked = !checkbox.checked; // Revert
    });
}

// Service toggles
document.querySelectorAll('.service-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const serviceId = this.dataset.serviceId;
        const active = this.checked ? 1 : 0;

        fetch('<?php echo APP_URL; ?>/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_service&service_id=' + serviceId + '&active=' + active
        }).catch(e => console.error('Erreur:', e));
    });
});

// Purge logs
function confirmPurge(logType) {
    const logTypeFr = logType === 'modifications' ? 'des modifications' : 'des connexions';
    if (confirm('Êtes-vous sûr de vouloir purger le journal ' + logTypeFr + '? Cette action est irréversible.')) {
        const formData = new FormData();
        formData.append('action', 'purge_log');
        formData.append('log_type', logType);
        
        fetch('<?php echo APP_URL; ?>/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Recharger la page pour voir les changements
            window.location.reload();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la purge');
        });
    }
}

// Organisation
var orgCurrentType = null;
var orgCurrentParentId = null;

function showAddOrgModal(itemType, parentId) {
    orgCurrentType = itemType;
    orgCurrentParentId = parentId;
    
    var titleLabel = itemType === 'entreprise' ? 'Entreprise' : (itemType === 'departement' ? 'Département' : 'Service');
    document.querySelector('#modal-add-org-item .modal-title').textContent = 'Ajouter une ' + titleLabel;
    
    var modal = new bootstrap.Modal(document.getElementById('modal-add-org-item'));
    modal.show();
}

function deleteOrgItem(itemType, itemId, event) {
    event.stopPropagation();
    if (!confirm('Êtes-vous certain de vouloir supprimer cet élément ?')) return;
    
    var formData = new FormData();
    formData.append('action', 'delete_' + itemType);
    formData.append('id', itemId);
    
    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(err => {
        console.error('Erreur:', err);
        alert('Erreur lors de la suppression');
    });
}

function saveOrgItem() {
    var nom = document.getElementById('org-nom').value.trim();
    var ldapDn = document.getElementById('org-ldap-dn').value.trim();
    
    if (!nom) {
        alert('Veuillez entrer un nom');
        return;
    }
    
    var action = 'create_' + orgCurrentType;
    var formData = new FormData();
    formData.append('action', action);
    formData.append('nom', nom);
    formData.append('ldap_dn', ldapDn);
    
    if (orgCurrentType === 'departement') {
        formData.append('entreprise_id', orgCurrentParentId);
    } else if (orgCurrentType === 'service') {
        formData.append('departement_id', orgCurrentParentId);
    }
    
    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal-add-org-item')).hide();
            location.reload();
        } else {
            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(err => {
        console.error('Erreur:', err);
        alert('Erreur lors de la création');
    });
}

function loadFromLDAP() {
    var btn = document.getElementById('btn-load-ldap');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Chargement...';
    
    var formData = new FormData();
    formData.append('action', 'get_ldap_organization');
    
    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log('Organisation LDAP chargée:', data.organization);
            alert('Organisation LDAP chargée. Voici les entités trouvées:\n\n' + 
                JSON.stringify(data.organization, null, 2).substring(0, 500) + '...');
        } else {
            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-sync"></i> Charger depuis LDAP';
    })
    .catch(err => {
        console.error('Erreur:', err);
        alert('Erreur lors du chargement de l\'organisation LDAP');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-sync"></i> Charger depuis LDAP';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var btnLoadLDAP = document.getElementById('btn-load-ldap');
    if (btnLoadLDAP) {
        btnLoadLDAP.addEventListener('click', loadFromLDAP);
    }
    
    var btnSaveOrg = document.getElementById('btn-save-org');
    if (btnSaveOrg) {
        btnSaveOrg.addEventListener('click', saveOrgItem);
    }
});
</script>

<!-- Modale : Ajouter une entité d'organisation -->
<div class="modal fade" id="modal-add-org-item" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ajouter une entité</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="org-nom">Nom</label>
                    <input type="text" class="form-control" id="org-nom" required>
                </div>
                <div class="form-group">
                    <label for="org-ldap-dn">DN LDAP (optionnel)</label>
                    <input type="text" class="form-control" id="org-ldap-dn">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-save-org">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modales de confirmation de purge -->
<div class="modal fade" id="purgeModificationsModal" tabindex="-1" aria-labelledby="purgeModificationsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purgeModificationsLabel"><i class="fa-solid fa-warning"></i> Purger les modifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Attention !</strong> Vous êtes sur le point de purger tous les journaux des modifications.</p>
                <p>Cette action est <strong>irréversible</strong> et supprimera toutes les traces des modifications effectuées.</p>
                <p>Êtes-vous certain de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="confirmPurge('modifications')">
                    <i class="fa-solid fa-trash"></i> Purger définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="purgeConnexionsModal" tabindex="-1" aria-labelledby="purgeConnexionsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purgeConnexionsLabel"><i class="fa-solid fa-warning"></i> Purger les connexions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Attention !</strong> Vous êtes sur le point de purger tous les journaux des connexions.</p>
                <p>Cette action est <strong>irréversible</strong> et supprimera toutes les traces des accès utilisateurs.</p>
                <p>Êtes-vous certain de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="confirmPurge('connexions')">
                    <i class="fa-solid fa-trash"></i> Purger définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</script>

<!-- Modales de confirmation de purge -->
<div class="modal fade" id="purgeModificationsModal" tabindex="-1" aria-labelledby="purgeModificationsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purgeModificationsLabel"><i class="fa-solid fa-warning"></i> Purger les modifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Attention !</strong> Vous êtes sur le point de purger tous les journaux des modifications.</p>
                <p>Cette action est <strong>irréversible</strong> et supprimera toutes les traces des modifications effectuées.</p>
                <p>Êtes-vous certain de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="confirmPurge('modifications')">
                    <i class="fa-solid fa-trash"></i> Purger définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="purgeConnexionsModal" tabindex="-1" aria-labelledby="purgeConnexionsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purgeConnexionsLabel"><i class="fa-solid fa-warning"></i> Purger les connexions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Attention !</strong> Vous êtes sur le point de purger tous les journaux des connexions.</p>
                <p>Cette action est <strong>irréversible</strong> et supprimera toutes les traces des accès utilisateurs.</p>
                <p>Êtes-vous certain de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="confirmPurge('connexions')">
                    <i class="fa-solid fa-trash"></i> Purger définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
