<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

$isAdmin = true;
foreach ($currentUser['roles'] as $dn => $role) {
    if ($dn === '*' && $role === 'admin') $isAdmin = true;
}

if (!$isAdmin) {
    http_response_code(403);
    die('Accès refusé. Vous n\'avez pas les permissions d\'administration.');
}

$pageTitle = 'Administration';
$breadcrumb = array(
    array('label' => APP_NAME, 'link' => APP_URL . '/index.php?id=0'),
    array('label' => 'Administration', 'link' => APP_URL . '/admin.php')
);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'hierarchie';
$purgeMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    $ip = Journal::getIp();

    switch ($action) {
        case 'toggle_hierarchie':
            if ($isAdmin) {
                $hierarchieId = isset($_POST['hierarchie_id']) ? (int)$_POST['hierarchie_id'] : 0;
                $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
                $node = Hierarchie::getById($hierarchieId);

                if ($node) {
                    Hierarchie::setActif($hierarchieId, $active);
                    Journal::logModification(
                        $currentUser['username'],
                        $ip,
                        $active ? 'ACTIVER' : 'DESACTIVER',
                        'hierarchie',
                        $hierarchieId,
                        'Niveau ' . $node['nom'] . ' ' . ($active ? 'activé' : 'désactivé')
                    );
                }
            }
            break;

        case 'set_role':
            if ($isAdmin) {
                $username = isset($_POST['username']) ? $_POST['username'] : '';
                $dn = isset($_POST['dn']) ? $_POST['dn'] : '';
                $role = isset($_POST['role']) ? $_POST['role'] : '';
                $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;

                if ($username && $dn && in_array($role, array('admin', 'modificateur', 'lecteur'))) {
                    $db = Database::getInstance();

                    if ($enabled) {
                        $db->execute(
                            'DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                            array($username, $dn, $role)
                        );
                        $db->insert(
                            'INSERT INTO pdc_utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
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
                        $db->execute(
                            'DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                            array($username, $dn, $role)
                        );
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
                            'UPDATE pdc_parametres SET valeur = ? WHERE cle = ?',
                            array('/assets/uploads/' . $filename, 'logo_url')
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
                        'UPDATE pdc_parametres SET valeur = ? WHERE cle = ?',
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
                    $db = Database::getInstance();
                    $db->execute('DELETE FROM pdc_journal_modifications', array());
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
                    $db = Database::getInstance();
                    $db->execute('DELETE FROM pdc_journal_connexions', array());
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

$db = Database::getInstance();
$hierarchie = Hierarchie::getAll(false);
$parametres = array();
$params = $db->fetchAll('SELECT cle, valeur FROM pdc_parametres');
foreach ($params as $p) {
    $parametres[$p['cle']] = $p['valeur'];
}

function renderHierarchyTree(array $nodes, $parentId = 0) {
    $html = '<ul class="pdc-hierarchy-tree" data-parent-id="' . (int)$parentId . '">';

    foreach ($nodes as $node) {
        $html .= '<li class="pdc-tree-node" data-level-id="' . (int)$node['id'] . '">';
        $html .= '<div class="pdc-tree-item pdc-tree-item-group' . (!empty($node['actif']) ? '' : ' is-inactive') . '">';
        $html .= '<label class="pdc-tree-label">';
        $html .= '<button type="button" class="btn btn-xs btn-link pdc-tree-drag-handle" title="Glisser-déposer pour déplacer"><i class="fa-solid fa-grip-vertical"></i></button>';
        $html .= '<input type="checkbox" class="hierarchie-toggle" data-hierarchie-id="' . (int)$node['id'] . '"' . (!empty($node['actif']) ? ' checked' : '') . ' />';
        $html .= '<i class="fa-solid fa-sitemap"></i>';
        $html .= ' <span class="pdc-tree-name">' . htmlspecialchars($node['nom'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '</label>';
        $html .= '<div class="pdc-tree-actions">';
        $html .= '<button type="button" class="btn btn-xs btn-link pdc-hier-add-child" data-parent-id="' . (int)$node['id'] . '" title="Ajouter un sous-niveau"><i class="fa-solid fa-square-plus"></i></button>';
        $html .= '<button type="button" class="btn btn-xs btn-link pdc-hier-edit" data-id="' . (int)$node['id'] . '" data-nom="' . htmlspecialchars($node['nom'], ENT_QUOTES, 'UTF-8') . '" title="Renommer ce niveau"><i class="fa-solid fa-square-pen"></i></button>';
        $html .= '<button type="button" class="btn btn-xs btn-link text-danger pdc-hier-delete" data-id="' . (int)$node['id'] . '" data-nom="' . htmlspecialchars($node['nom'], ENT_QUOTES, 'UTF-8') . '" title="Supprimer ce niveau"><i class="fa-solid fa-trash-can"></i></button>';
        $html .= '</div>';
        $html .= '</div>';

        if (!empty($node['subItems'])) {
            $html .= renderHierarchyTree($node['subItems'], (int)$node['id']);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pdc-container pdc-admin">
    <?php if (!empty($purgeMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($purgeMessage, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs pdc-admin-tabs" role="tablist">
        <li role="presentation" <?php echo $tab === 'hierarchie' ? 'class="active"' : ''; ?>>
            <a href="?tab=hierarchie" role="tab"><i class="fa-solid fa-diagram-project"></i> Hiérarchie</a>
        </li>
        <li role="presentation" <?php echo $tab === 'droits' ? 'class="active"' : ''; ?>>
            <a href="?tab=droits" role="tab"><i class="fa-solid fa-user-lock"></i> Droits utilisateurs</a>
        </li>
        <?php if ($isAdmin): ?>
        <li role="presentation" <?php echo $tab === 'parametres' ? 'class="active"' : ''; ?>>
            <a href="?tab=parametres" role="tab"><i class="fa-solid fa-sliders"></i> Paramètres</a>
        </li>
        <li role="presentation" <?php echo $tab === 'journal' ? 'class="active"' : ''; ?>>
            <a href="?tab=journal" role="tab"><i class="fa-solid fa-book"></i> Journaux</a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content pdc-admin-content">
        <?php if ($tab === 'hierarchie'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Arborescence hiérarchique</h2>
                <p class="text-muted">Vue arborescente des nœuds définis par la classe Hierarchie.</p>

                <div class="pdc-hierarchy-toolbar">
                    <button type="button" class="btn btn-primary" id="btn-add-root-level">
                        <i class="fa-solid fa-plus"></i> Ajouter un niveau racine
                    </button>
                </div>

                <div class="pdc-hierarchy-list">
                    <?php if (!empty($hierarchie)): ?>
                        <?php echo renderHierarchyTree($hierarchie); ?>
                    <?php else: ?>
                        <div class="alert alert-info">Aucune hiérarchie n'a encore été définie.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($tab === 'droits'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Gestion des droits utilisateurs</h2>
                <p class="text-muted">Sélectionnez un utilisateur à gauche puis définissez son rôle pour chaque niveau hiérarchique.</p>

                <div class="pdc-roles-toolbar">
                    <button type="button" class="btn btn-primary" id="btn-open-ldap-user-search">
                        <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur LDAP
                    </button>
                </div>

                <div id="ldap-user-search-panel" class="pdc-ldap-search" style="display: none;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="ldap-user-query" placeholder="Rechercher par login, nom ou e-mail">
                        <button type="button" class="btn btn-primary" id="btn-search-ldap-user">
                            <i class="fa-solid fa-magnifying-glass"></i> Rechercher
                        </button>
                    </div>
                    <div id="ldap-search-results" class="pdc-ldap-results"></div>
                </div>

                <div id="users-loading" class="alert alert-info">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement des utilisateurs...
                </div>

                <div class="pdc-roles-layout" id="users-container" style="display: none;">
                    <aside class="pdc-roles-users">
                        <h4>Utilisateurs</h4>
                        <div id="users-list" class="list-group"></div>
                    </aside>
                    <section class="pdc-roles-rights">
                        <h4 id="selected-user-title">Aucun utilisateur sélectionné</h4>
                        <div id="selected-user-global-admin" class="pdc-global-admin-toggle"></div>
                        <div class="table-responsive pdc-roles-list">
                            <table class="table table-striped table-hover" id="roles-matrix-table">
                                <thead>
                                    <tr>
                                        <th>Niveau hiérarchique</th>
                                        <th style="width: 360px;">Rôle</th>
                                    </tr>
                                </thead>
                                <tbody id="roles-matrix-body"></tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <?php endif; ?>

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

        <?php if ($isAdmin && $tab === 'journal'): ?>
        <div role="tabpanel" class="tab-pane active">
            <div class="pdc-admin-section">
                <h2>Journaux système</h2>

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

                <div class="tab-content">
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
                                    $modifications = $db->fetchAll('SELECT * FROM pdc_journal_modifications ORDER BY date_heure DESC LIMIT 500');
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
                                    $connexions = $db->fetchAll('SELECT * FROM pdc_journal_connexions ORDER BY date_heure DESC LIMIT 500');
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

<script>
var PDC_USERS_STATE = {
    users: [],
    roles: {},
    hierarchie: [],
    selectedUsername: null,
};

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('users-list')) {
        loadUsers();
        initLdapUserSearch();
    }

    initHierarchyLevelManagement();
});

function initHierarchyLevelManagement() {
    const modalElement = document.getElementById('hierarchyLevelModal');
    const form = document.getElementById('hierarchy-level-form');
    const modeInput = document.getElementById('hierarchy-form-mode');
    const idInput = document.getElementById('hierarchy-level-id');
    const parentInput = document.getElementById('hierarchy-parent-id');
    const nameInput = document.getElementById('hierarchy-level-name');
    const title = document.getElementById('hierarchyLevelModalLabel');

    if (!modalElement || !form || !modeInput || !idInput || !parentInput || !nameInput || !title) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);

    initHierarchyDragDrop();
    initHierarchyActionHoverHighlight();

    const rootBtn = document.getElementById('btn-add-root-level');
    if (rootBtn) {
        rootBtn.addEventListener('click', function() {
            modeInput.value = 'create';
            idInput.value = '0';
            parentInput.value = '0';
            nameInput.value = '';
            title.textContent = 'Ajouter un niveau racine';
            modal.show();
            setTimeout(function() { nameInput.focus(); }, 150);
        });
    }

    document.querySelectorAll('.pdc-hier-add-child').forEach(function(btn) {
        btn.addEventListener('click', function() {
            modeInput.value = 'create';
            idInput.value = '0';
            parentInput.value = this.dataset.parentId || '0';
            nameInput.value = '';
            title.textContent = 'Ajouter un sous-niveau';
            modal.show();
            setTimeout(function() { nameInput.focus(); }, 150);
        });
    });

    document.querySelectorAll('.pdc-hier-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            modeInput.value = 'edit';
            idInput.value = this.dataset.id || '0';
            parentInput.value = '0';
            nameInput.value = this.dataset.nom || '';
            title.textContent = 'Renommer un niveau';
            modal.show();
            setTimeout(function() { nameInput.focus(); }, 150);
        });
    });

    document.querySelectorAll('.pdc-hier-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id || '0';
            const nom = this.dataset.nom || '';
            if (!id || id === '0') {
                return;
            }
            if (!confirm('Supprimer le niveau "' + nom + '" ?')) {
                return;
            }

            const recursive = confirm('Souhaitez-vous une suppression recursive ?\n\nOK: supprimer aussi les sous-niveaux, domaines et projets.\nAnnuler: suppression simple (refusee si le niveau contient des enfants ou des domaines).');

            const formData = new FormData();
            formData.append('action', 'delete_hierarchie_level');
            formData.append('id', id);
            formData.append('recursive', recursive ? '1' : '0');

            fetch('<?php echo APP_URL; ?>/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Erreur inconnue');
                }
                window.location.reload();
            })
            .catch(error => {
                console.error('Erreur suppression niveau:', error);
                alert('Suppression impossible: ' + error.message);
            });
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const nom = nameInput.value.trim();
        if (!nom) {
            alert('Le nom du niveau est requis.');
            nameInput.focus();
            return;
        }

        const mode = modeInput.value;
        const formData = new FormData();
        if (mode === 'edit') {
            formData.append('action', 'update_hierarchie_level');
            formData.append('id', idInput.value || '0');
            formData.append('nom', nom);
        } else {
            formData.append('action', 'create_hierarchie_level');
            formData.append('parent_id', parentInput.value || '0');
            formData.append('nom', nom);
        }

        fetch('<?php echo APP_URL; ?>/api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Erreur inconnue');
            }
            modal.hide();
            window.location.reload();
        })
        .catch(error => {
            console.error('Erreur sauvegarde niveau:', error);
            alert('Erreur lors de l\'enregistrement: ' + error.message);
        });
    });
}

function initHierarchyActionHoverHighlight() {
    document.querySelectorAll('.pdc-tree-actions button').forEach(function(btn) {
        btn.addEventListener('mouseenter', function() {
            var treeItem = this.closest('.pdc-tree-item');
            if (treeItem) {
                treeItem.classList.add('is-action-hover');
            }
        });

        btn.addEventListener('mouseleave', function() {
            var treeItem = this.closest('.pdc-tree-item');
            if (treeItem) {
                treeItem.classList.remove('is-action-hover');
            }
        });
    });
}

function initHierarchyDragDrop() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.sortable) {
        return;
    }

    const $trees = jQuery('.pdc-hierarchy-tree');
    if (!$trees.length) {
        return;
    }

    $trees.sortable({
        items: '> .pdc-tree-node',
        connectWith: '.pdc-hierarchy-tree',
        handle: '.pdc-tree-item',
        cancel: '.hierarchie-toggle, .pdc-tree-actions button, .pdc-tree-drag-handle, button, input, select, textarea, a',
        placeholder: 'pdc-tree-node-placeholder',
        tolerance: 'pointer',
        distance: 5,
        start: function(event, ui) {
            const $item = ui.item;
            $item.data('old-parent-id', parseInt($item.parent().data('parent-id') || 0, 10));
            $item.data('old-index', $item.index());
            $item.addClass('is-dragging');
        },
        stop: function(event, ui) {
            const $item = ui.item;
            $item.removeClass('is-dragging');
            const levelId = parseInt($item.data('level-id') || 0, 10);
            const $newParentTree = $item.parent('.pdc-hierarchy-tree');
            const newParentId = parseInt($newParentTree.data('parent-id') || 0, 10);
            const newIndex = $item.index();

            const oldParentId = parseInt($item.data('old-parent-id') || 0, 10);
            const oldIndex = parseInt($item.data('old-index') || 0, 10);

            if (!levelId) {
                window.location.reload();
                return;
            }

            if (levelId === newParentId || $item.find('.pdc-tree-node[data-level-id="' + newParentId + '"]').length > 0) {
                alert('Déplacement invalide: impossible de déplacer un niveau dans son sous-arbre.');
                window.location.reload();
                return;
            }

            if (oldParentId === newParentId && oldIndex === newIndex) {
                return;
            }

            jQuery.post('<?php echo APP_URL; ?>/api.php', {
                action: 'move_hierarchie_level',
                id: levelId,
                parent_id: newParentId,
                ordre: newIndex
            }, function(data) {
                if (!data || !data.success) {
                    alert('Erreur de déplacement: ' + (data && data.error ? data.error : 'Erreur inconnue'));
                    window.location.reload();
                }
            }, 'json').fail(function() {
                alert('Erreur de déplacement.');
                window.location.reload();
            });
        }
    });
}

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
            PDC_USERS_STATE.users = data.users || [];
            PDC_USERS_STATE.roles = data.roles || {};
            PDC_USERS_STATE.hierarchie = data.hierarchie || [];

            if (!PDC_USERS_STATE.selectedUsername && PDC_USERS_STATE.users.length > 0) {
                PDC_USERS_STATE.selectedUsername = PDC_USERS_STATE.users[0].username;
            }

            renderUsersList();
            renderRolesMatrix();
        } else {
            document.getElementById('users-loading').innerHTML = '<div class="alert alert-danger">Erreur: ' + (data.error || 'Erreur inconnue') + '</div>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('users-loading').innerHTML = '<div class="alert alert-danger">Erreur de chargement</div>';
    });
}

function renderUsersList() {
    const list = document.getElementById('users-list');
    list.innerHTML = '';

    PDC_USERS_STATE.users.forEach(function(user) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action pdc-user-item' + (user.username === PDC_USERS_STATE.selectedUsername ? ' active' : '');
        button.dataset.username = user.username;
        var canDelete = user.username !== '<?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>';
        var deleteButtonHtml = canDelete
            ? '<button type="button" class="btn btn-xs btn-link pdc-user-delete" data-username="' + htmlEscape(user.username) + '" title="Supprimer l\'utilisateur"><i class="fa-solid fa-trash-can"></i></button>'
            : '';
        button.innerHTML = '<div class="pdc-user-item-row">' +
            '<div class="pdc-user-item-main"><strong>' + htmlEscape(user.displayname || user.username) + '</strong><br><small>' + htmlEscape(user.username) + '</small></div>' +
            deleteButtonHtml +
        '</div>';
        button.addEventListener('click', function(e) {
            if (e.target.closest('.pdc-user-delete')) {
                return;
            }
            PDC_USERS_STATE.selectedUsername = this.dataset.username;
            renderUsersList();
            renderRolesMatrix();
        });
        list.appendChild(button);
    });

    document.querySelectorAll('.pdc-user-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteUser(this.dataset.username);
        });
    });

    document.getElementById('users-loading').style.display = 'none';
    document.getElementById('users-container').style.display = 'block';
}

function flattenHierarchy(nodes, depth, out) {
    nodes.forEach(function(node) {
        out.push({
            id: node.id,
            nom: node.nom,
            actif: parseInt(node.actif, 10) === 1,
            depth: depth
        });
        if (node.subItems && node.subItems.length) {
            flattenHierarchy(node.subItems, depth + 1, out);
        }
    });
}

function getRoleForScope(username, scope) {
    var key = username + '::' + scope;
    if (!PDC_USERS_STATE.roles[key] || !PDC_USERS_STATE.roles[key].length) {
        return '';
    }
    return PDC_USERS_STATE.roles[key][0];
}

function renderRolesMatrix() {
    const tbody = document.getElementById('roles-matrix-body');
    const title = document.getElementById('selected-user-title');
    const globalAdminBox = document.getElementById('selected-user-global-admin');
    tbody.innerHTML = '';

    if (!PDC_USERS_STATE.selectedUsername) {
        title.textContent = 'Aucun utilisateur sélectionné';
        globalAdminBox.innerHTML = '';
        return;
    }

    var selectedUser = null;
    for (var i = 0; i < PDC_USERS_STATE.users.length; i++) {
        if (PDC_USERS_STATE.users[i].username === PDC_USERS_STATE.selectedUsername) {
            selectedUser = PDC_USERS_STATE.users[i];
            break;
        }
    }

    title.textContent = selectedUser ? ('Droits de ' + (selectedUser.displayname || selectedUser.username)) : ('Droits de ' + PDC_USERS_STATE.selectedUsername);

    var isGlobalAdmin = getRoleForScope(PDC_USERS_STATE.selectedUsername, '*') === 'admin';
    globalAdminBox.innerHTML =
        '<label class="pdc-global-admin-label">' +
            '<input type="checkbox" id="global-admin-checkbox" ' + (isGlobalAdmin ? 'checked' : '') + '>' +
            '<span>Administrateur application (global)</span>' +
        '</label>';

    var globalCheckbox = document.getElementById('global-admin-checkbox');
    if (globalCheckbox) {
        globalCheckbox.addEventListener('change', function() {
            setUserGlobalAdmin(PDC_USERS_STATE.selectedUsername, this.checked ? 1 : 0, this);
        });
    }

    var rows = [];
    flattenHierarchy(PDC_USERS_STATE.hierarchie, 0, rows);

    rows.forEach(function(item) {
        var scope = 'hierarchie:' + item.id;
        var role = getRoleForScope(PDC_USERS_STATE.selectedUsername, scope);
        var tr = document.createElement('tr');
        tr.className = item.actif ? '' : 'pdc-role-row-inactive';
        var radioName = 'role_scope_' + item.id;

        var label = '<span class="pdc-hier-level" style="padding-left:' + (item.depth * 16) + 'px">' +
            (item.depth > 0 ? '<span style="opacity:.35;margin-right:6px;">↳</span>' : '') +
            htmlEscape(item.nom) +
            (item.actif ? '' : ' <span class="badge bg-secondary">Inactif</span>') +
            '</span>';

        tr.innerHTML =
            '<td>' + label + '</td>' +
            '<td>' +
                '<div class="pdc-role-radios" data-username="' + htmlEscape(PDC_USERS_STATE.selectedUsername) + '" data-scope="' + htmlEscape(scope) + '" data-old-value="' + htmlEscape(role) + '">' +
                    '<label class="pdc-role-radio-option">' +
                        '<input type="radio" class="role-radio" name="' + radioName + '" value=""' + (role === '' ? ' checked' : '') + '> Aucun' +
                    '</label>' +
                    '<label class="pdc-role-radio-option">' +
                        '<input type="radio" class="role-radio" name="' + radioName + '" value="lecteur"' + (role === 'lecteur' ? ' checked' : '') + '> Lecteur' +
                    '</label>' +
                    '<label class="pdc-role-radio-option">' +
                        '<input type="radio" class="role-radio" name="' + radioName + '" value="modificateur"' + (role === 'modificateur' ? ' checked' : '') + '> Modificateur' +
                    '</label>' +
                '</div>' +
            '</td>';
        tbody.appendChild(tr);
    });

    document.querySelectorAll('.role-radio').forEach(function(radio) {
        radio.addEventListener('change', setUserScopeRole);
    });
}

function setUserGlobalAdmin(username, enabled, checkbox) {
    const oldChecked = !enabled;
    const formData = new FormData();
    formData.append('action', 'set_user_global_admin');
    formData.append('username', username);
    formData.append('enabled', enabled ? 1 : 0);

    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.error || 'Erreur inconnue');
        }
        const key = username + '::*';
        if (enabled) {
            PDC_USERS_STATE.roles[key] = ['admin'];
        } else {
            delete PDC_USERS_STATE.roles[key];
        }
    })
    .catch(error => {
        console.error('Erreur admin global:', error);
        checkbox.checked = oldChecked;
        alert('Erreur lors de la mise à jour du rôle admin global: ' + error.message);
    });
}

function htmlEscape(text) {
    if (text === null || text === undefined) {
        return '';
    }
    text = String(text);
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function setUserScopeRole(e) {
    const radio = e.target;
    const wrapper = radio.closest('.pdc-role-radios');
    if (!wrapper) {
        return;
    }

    const username = wrapper.dataset.username;
    const scope = wrapper.dataset.scope;
    const role = radio.value;
    const oldValue = wrapper.dataset.oldValue !== undefined ? wrapper.dataset.oldValue : '';

    const formData = new FormData();
    formData.append('action', 'set_user_scope_role');
    formData.append('username', username);
    formData.append('scope', scope);
    formData.append('role', role);

    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const key = username + '::' + scope;
            if (role === '') {
                delete PDC_USERS_STATE.roles[key];
            } else {
                PDC_USERS_STATE.roles[key] = [role];
            }
            wrapper.dataset.oldValue = role;
        } else {
            console.error('Erreur:', data.error);
            const oldInput = wrapper.querySelector('input.role-radio[value="' + oldValue + '"]');
            if (oldInput) {
                oldInput.checked = true;
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        const oldInput = wrapper.querySelector('input.role-radio[value="' + oldValue + '"]');
        if (oldInput) {
            oldInput.checked = true;
        }
    });
}

function initLdapUserSearch() {
    const panel = document.getElementById('ldap-user-search-panel');
    const openBtn = document.getElementById('btn-open-ldap-user-search');
    const searchBtn = document.getElementById('btn-search-ldap-user');
    const queryInput = document.getElementById('ldap-user-query');

    if (!panel || !openBtn || !searchBtn || !queryInput) {
        return;
    }

    openBtn.addEventListener('click', function() {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        if (panel.style.display === 'block') {
            queryInput.focus();
        }
    });

    searchBtn.addEventListener('click', searchLdapUsers);
    queryInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchLdapUsers();
        }
    });
}

function searchLdapUsers() {
    const queryInput = document.getElementById('ldap-user-query');
    const results = document.getElementById('ldap-search-results');
    const query = queryInput.value.trim();

    if (query.length < 2) {
        results.innerHTML = '<div class="alert alert-warning">Saisissez au moins 2 caractères.</div>';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'search_ldap_users');
    formData.append('query', query);

    results.innerHTML = '<div class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Recherche LDAP...</div>';

    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            results.innerHTML = '<div class="alert alert-danger">Erreur: ' + htmlEscape(data.error || 'Erreur inconnue') + '</div>';
            return;
        }

        if (!data.users || data.users.length === 0) {
            results.innerHTML = '<div class="alert alert-info">Aucun utilisateur trouvé.</div>';
            return;
        }

        var html = '<div class="list-group">';
        data.users.forEach(function(user) {
            var alreadyInApp = PDC_USERS_STATE.users.some(function(u) { return u.username === user.username; });
            html += '<div class="list-group-item d-flex justify-content-between align-items-center">' +
                '<div><strong>' + htmlEscape(user.displayname || user.username) + '</strong><br><small>' + htmlEscape(user.username) + '</small></div>' +
                '<button type="button" class="btn btn-sm ' + (alreadyInApp ? 'btn-secondary' : 'btn-primary') + ' btn-add-ldap-user" ' +
                    (alreadyInApp ? 'disabled' : '') +
                    ' data-username="' + htmlEscape(user.username) + '" data-displayname="' + htmlEscape(user.displayname || user.username) + '" data-dn="' + htmlEscape(user.dn) + '" data-email="' + htmlEscape(user.email || '') + '">' +
                    (alreadyInApp ? 'Déjà ajouté' : 'Ajouter') +
                '</button>' +
            '</div>';
        });
        html += '</div>';
        results.innerHTML = html;

        document.querySelectorAll('.btn-add-ldap-user').forEach(function(btn) {
            btn.addEventListener('click', addLdapUserToApp);
        });
    })
    .catch(error => {
        console.error('Erreur LDAP:', error);
        results.innerHTML = '<div class="alert alert-danger">Erreur lors de la recherche LDAP.</div>';
    });
}

function addLdapUserToApp(e) {
    const btn = e.currentTarget;
    const formData = new FormData();
    formData.append('action', 'add_user_from_ldap');
    formData.append('username', btn.dataset.username);
    formData.append('displayname', btn.dataset.displayname);
    formData.append('dn', btn.dataset.dn);
    formData.append('email', btn.dataset.email || '');

    btn.disabled = true;
    btn.textContent = 'Ajout...';

    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.error || 'Erreur inconnue');
        }
        PDC_USERS_STATE.selectedUsername = btn.dataset.username;
        loadUsers();
    })
    .catch(error => {
        console.error('Erreur ajout utilisateur:', error);
        btn.disabled = false;
        btn.textContent = 'Ajouter';
        alert('Erreur lors de l\'ajout utilisateur: ' + error.message);
    });
}

function deleteUser(username) {
    if (!username) {
        return;
    }

    if (!confirm('Supprimer l\'utilisateur ' + username + ' et tous ses droits ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('username', username);

    fetch('<?php echo APP_URL; ?>/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.error || 'Erreur inconnue');
        }

        if (PDC_USERS_STATE.selectedUsername === username) {
            PDC_USERS_STATE.selectedUsername = null;
        }
        loadUsers();
    })
    .catch(error => {
        console.error('Erreur suppression utilisateur:', error);
        alert('Erreur lors de la suppression utilisateur: ' + error.message);
    });
}

document.querySelectorAll('.hierarchie-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const hierarchieId = this.dataset.hierarchieId;
        const active = this.checked ? 1 : 0;
        const treeItem = this.closest('.pdc-tree-item-group');

        if (treeItem) {
            treeItem.classList.toggle('is-inactive', active !== 1);
        }

        fetch('<?php echo APP_URL; ?>/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_hierarchie&hierarchie_id=' + encodeURIComponent(hierarchieId) + '&active=' + active
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .catch(error => {
            console.error('Erreur:', error);
            this.checked = !this.checked;
            if (treeItem) {
                treeItem.classList.toggle('is-inactive', !this.checked);
            }
        });
    });
});

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
        .then(() => window.location.reload())
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la purge');
        });
    }
}
</script>

<div class="modal fade" id="hierarchyLevelModal" tabindex="-1" aria-labelledby="hierarchyLevelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hierarchy-level-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="hierarchyLevelModalLabel">Ajouter un niveau hiérarchique</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="hierarchy-form-mode" value="create">
                    <input type="hidden" id="hierarchy-level-id" value="0">
                    <input type="hidden" id="hierarchy-parent-id" value="0">
                    <div class="mb-3">
                        <label for="hierarchy-level-name" class="form-label">Nom du niveau</label>
                        <input type="text" class="form-control" id="hierarchy-level-name" maxlength="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="hierarchy-level-submit-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

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