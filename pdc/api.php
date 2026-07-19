<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$currentUser = Auth::requireLogin();
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Vérifier les droits selon l'action
$isAdmin = false;
foreach ($currentUser['roles'] as $dn => $role) {
    if ($dn === '*' && $role === 'admin') {
        $isAdmin = true;
    }
}

$roleRank = array(
    'lecteur' => 1,
    'modificateur' => 2,
);

$userHasMinRoleOnHierarchy = function($hierarchieId, $minRole) use ($currentUser, $roleRank) {
    $scope = 'hierarchie:' . (int)$hierarchieId;
    if (!isset($currentUser['roles'][$scope])) {
        return false;
    }

    $currentRole = $currentUser['roles'][$scope];
    if (!isset($roleRank[$currentRole]) || !isset($roleRank[$minRole])) {
        return false;
    }

    return $roleRank[$currentRole] >= $roleRank[$minRole];
};

$getHierarchyIdByDomaine = function($domaineId) {
    $db = Database::getInstance();
    $row = $db->fetchOne('SELECT hierarchie_id FROM pdc_domaines WHERE id = ?', array((int)$domaineId));
    return $row ? (int)$row['hierarchie_id'] : 0;
};

$getHierarchyIdByProjet = function($projetId) {
    $db = Database::getInstance();
    $row = $db->fetchOne(
        'SELECT d.hierarchie_id FROM pdc_projets p INNER JOIN pdc_domaines d ON d.id = p.domaine_id WHERE p.id = ?',
        array((int)$projetId)
    );
    return $row ? (int)$row['hierarchie_id'] : 0;
};

try {
    switch ($action) {

        // ---- Domaines ----

        case 'create_domaine':
            $hierarchieId = isset($_POST['hierarchie_id']) ? (int)$_POST['hierarchie_id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            if (empty($nom)) throw new Exception('Nom requis');
            if ($hierarchieId <= 0) throw new Exception('Niveau de hiérarchie invalide');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            $id = Hierarchie::createDomaine($hierarchieId, $nom);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'domaine',
                $id,
                'Création domaine : ' . $nom
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'update_domaine':
            $id  = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
            $newHierarchieId = isset($_POST['hierarchie_id']) ? (int)$_POST['hierarchie_id'] : 0;
            if (empty($nom)) throw new Exception('Nom requis');

            $currentHierarchieId = $getHierarchyIdByDomaine($id);
            if ($currentHierarchieId <= 0) throw new Exception('Domaine introuvable');
            if (!$userHasMinRoleOnHierarchy($currentHierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            if ($newHierarchieId <= 0) {
                $newHierarchieId = $currentHierarchieId;
            }

            $targetLevel = Hierarchie::getById($newHierarchieId, false);
            if (!$targetLevel) throw new Exception('Niveau hiérarchique cible introuvable');

            if ($newHierarchieId !== $currentHierarchieId && !$userHasMinRoleOnHierarchy($newHierarchieId, 'modificateur')) {
                throw new Exception('Accès refusé');
            }

            Hierarchie::updateDomaine($id, $nom, $newHierarchieId, $commentaire);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'domaine',
                $id,
                'Modification domaine : ' . $nom . ' (niveau: ' . $newHierarchieId . ')'
            );
            echo json_encode(array('success' => true));
            break;

        case 'delete_domaine':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            $hierarchieId = $getHierarchyIdByDomaine($id);
            if ($hierarchieId <= 0) throw new Exception('Domaine introuvable');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            Hierarchie::deleteDomaine($id);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'domaine',
                $id,
                'Suppression domaine'
            );
            echo json_encode(array('success' => true));
            break;

        case 'reorder_domaines':
            $ordres = isset($_POST['ordres']) ? $_POST['ordres'] : array();
            foreach ($ordres as $domaineId => $ordre) {
                $hierarchieId = $getHierarchyIdByDomaine($domaineId);
                if ($hierarchieId <= 0) throw new Exception('Domaine introuvable');
                if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');
            }
            Hierarchie::updateDomainesOrdre($ordres);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'domaines',
                null,
                'Réorganisation domaines'
            );
            echo json_encode(array('success' => true));
            break;

        // ---- Projets ----

        case 'create_projet':
            $domaineId = isset($_POST['domaine_id']) ? (int)$_POST['domaine_id'] : 0;
            $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
            $dateDebut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
            $dateFin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

            if (empty($titre) || empty($dateDebut) || empty($dateFin)) {
                throw new Exception('Données manquantes');
            }

            $hierarchieId = $getHierarchyIdByDomaine($domaineId);
            if ($hierarchieId <= 0) throw new Exception('Domaine introuvable');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            $id = Projet::create($domaineId, $titre, $dateDebut, $dateFin, $commentaire);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'projet',
                $id,
                'Création projet : ' . $titre
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'get_projet':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $projet = Projet::getById($id);
            if (!$projet) throw new Exception('Projet introuvable');

            $hierarchieId = $getHierarchyIdByProjet($id);
            if ($hierarchieId <= 0) throw new Exception('Projet introuvable');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'lecteur')) throw new Exception('Accès refusé');

            $gradients = Projet::getGradients($id);
            $jalons = Projet::getJalons($id);

            echo json_encode(array(
                'success' => true,
                'projet' => $projet,
                'gradients' => $gradients,
                'jalons' => $jalons,
            ));
            break;

        case 'update_projet':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
            $dateDebut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
            $dateFin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

            if (empty($titre) || empty($dateDebut) || empty($dateFin)) {
                throw new Exception('Données manquantes');
            }

            $hierarchieId = $getHierarchyIdByProjet($id);
            if ($hierarchieId <= 0) throw new Exception('Projet introuvable');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            Projet::update($id, $titre, $dateDebut, $dateFin, $commentaire);

            // Gradients
            $gradients = isset($_POST['gradients']) ? json_decode($_POST['gradients'], true) : array();
            Projet::saveGradients($id, $gradients);

            // Jalons
            $jalons = isset($_POST['jalons']) ? json_decode($_POST['jalons'], true) : array();
            Projet::saveJalons($id, $jalons);

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'projet',
                $id,
                'Modification projet : ' . $titre
            );
            echo json_encode(array('success' => true));
            break;

        case 'delete_projet':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            $hierarchieId = $getHierarchyIdByProjet($id);
            if ($hierarchieId <= 0) throw new Exception('Projet introuvable');
            if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');

            Projet::delete($id);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'projet',
                $id,
                'Suppression projet'
            );
            echo json_encode(array('success' => true));
            break;

        case 'reorder_projets':
            $ordres = isset($_POST['ordres']) ? $_POST['ordres'] : array();
            foreach ($ordres as $projetId => $ordre) {
                $hierarchieId = $getHierarchyIdByProjet($projetId);
                if ($hierarchieId <= 0) throw new Exception('Projet introuvable');
                if (!$userHasMinRoleOnHierarchy($hierarchieId, 'modificateur')) throw new Exception('Accès refusé');
            }
            Projet::updateOrdres($ordres);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'projets',
                null,
                'Réorganisation projets'
            );
            echo json_encode(array('success' => true));
            break;

        // ---- Users & Roles (Admin only) ----

        case 'get_users_tree':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $db = Database::getInstance();
            $users = $db->fetchAll('SELECT DISTINCT username, displayname, dn, email FROM pdc_utilisateurs ORDER BY displayname');
            $roles = $db->fetchAll('SELECT * FROM pdc_utilisateurs_roles');
            $hierarchie = Hierarchie::getAll(false);
            
            $rolesMap = array();
            foreach ($roles as $r) {
                $key = $r['username'] . '::' . $r['role_dn'];
                if (!isset($rolesMap[$key])) {
                    $rolesMap[$key] = array();
                }
                $rolesMap[$key][] = $r['role'];
            }
            
            echo json_encode(array(
                'success' => true,
                'users' => $users,
                'roles' => $rolesMap,
                'hierarchie' => $hierarchie
            ));
            break;

        case 'set_user_role':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $dn = isset($_POST['dn']) ? trim($_POST['dn']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;
            
            if (!$username || !in_array($role, array('admin', 'modificateur', 'lecteur'))) {
                throw new Exception('Paramètres invalides');
            }
            
            $db = Database::getInstance();
            
            if ($enabled) {
                $db->execute('DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                    array($username, $dn, $role));
                $db->insert(
                    'INSERT INTO pdc_utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
                    array($username, $dn, $role)
                );
                Journal::logModification(
                    $currentUser['username'],
                    Journal::getIp(),
                    'ASSIGN_ROLE',
                    'user',
                    0,
                    "Rôle '$role' assigné à $username"
                );
            } else {
                $db->execute('DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                    array($username, $dn, $role));
                Journal::logModification(
                    $currentUser['username'],
                    Journal::getIp(),
                    'REVOKE_ROLE',
                    'user',
                    0,
                    "Rôle '$role' retiré à $username"
                );
            }
            
            echo json_encode(array('success' => true));
            break;

        case 'set_user_scope_role':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $scope = isset($_POST['scope']) ? trim($_POST['scope']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';

            $allowedRoles = array('', 'lecteur', 'modificateur');
            if (!$username || !$scope || !in_array($role, $allowedRoles, true)) {
                throw new Exception('Paramètres invalides');
            }

            if (strpos($scope, 'hierarchie:') !== 0) {
                throw new Exception('Scope hiérarchique invalide');
            }

            $db = Database::getInstance();
            $db->execute('DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ?', array($username, $scope));

            if ($role !== '') {
                $db->insert(
                    'INSERT INTO pdc_utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
                    array($username, $scope, $role)
                );
            }

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'SET_SCOPE_ROLE',
                'user',
                0,
                "Rôle scope '$scope' pour $username : " . ($role !== '' ? $role : 'aucun')
            );

            echo json_encode(array('success' => true));
            break;

        case 'set_user_global_admin':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;

            if ($username === '') {
                throw new Exception('Utilisateur invalide');
            }

            $db = Database::getInstance();
            $db->execute('DELETE FROM pdc_utilisateurs_roles WHERE username = ? AND role_dn = ?', array($username, '*'));

            if ($enabled) {
                $db->insert(
                    'INSERT INTO pdc_utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
                    array($username, '*', 'admin')
                );
            }

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'SET_GLOBAL_ADMIN',
                'user',
                0,
                "Admin global pour $username : " . ($enabled ? 'activé' : 'désactivé')
            );

            echo json_encode(array('success' => true));
            break;

        case 'search_ldap_users':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $query = isset($_POST['query']) ? trim($_POST['query']) : '';
            if (strlen($query) < 2) {
                throw new Exception('La recherche doit contenir au moins 2 caractères');
            }

            $users = Auth::searchUsers($query, 25);
            echo json_encode(array('success' => true, 'users' => $users));
            break;

        case 'add_user_from_ldap':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $dn = isset($_POST['dn']) ? trim($_POST['dn']) : '';
            $displayname = isset($_POST['displayname']) ? trim($_POST['displayname']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            if ($username === '' || $dn === '') {
                throw new Exception('Utilisateur LDAP invalide');
            }

            if ($displayname === '') {
                $displayname = $username;
            }

            $db = Database::getInstance();
            $db->execute(
                'INSERT INTO pdc_utilisateurs (username, displayname, dn, email) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE displayname = VALUES(displayname), dn = VALUES(dn), email = VALUES(email)',
                array($username, $displayname, $dn, $email !== '' ? $email : null)
            );

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'ADD_USER',
                'utilisateur',
                0,
                "Ajout/Maj utilisateur LDAP : $username"
            );

            echo json_encode(array('success' => true));
            break;

        case 'delete_user':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            if ($username === '') {
                throw new Exception('Utilisateur invalide');
            }

            if ($username === $currentUser['username']) {
                throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
            }

            $db = Database::getInstance();
            $deleted = $db->execute('DELETE FROM pdc_utilisateurs WHERE username = ?', array($username));

            if ($deleted <= 0) {
                throw new Exception('Utilisateur introuvable');
            }

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE_USER',
                'utilisateur',
                0,
                "Suppression utilisateur : $username"
            );

            echo json_encode(array('success' => true));
            break;

        // ---- Hiérarchie ----

        case 'create_hierarchie_level':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';

            if ($nom === '') {
                throw new Exception('Nom requis');
            }

            $id = Hierarchie::createLevel($parentId, $nom);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'hierarchie',
                $id,
                'Création niveau hiérarchique : ' . $nom
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'update_hierarchie_level':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';

            if ($id <= 0) {
                throw new Exception('Niveau hiérarchique invalide');
            }
            if ($nom === '') {
                throw new Exception('Nom requis');
            }

            $node = Hierarchie::getById($id, false);
            if (!$node) {
                throw new Exception('Niveau hiérarchique introuvable');
            }

            Hierarchie::updateLevel($id, $nom);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'hierarchie',
                $id,
                'Renommage niveau hiérarchique : ' . $node['nom'] . ' -> ' . $nom
            );
            echo json_encode(array('success' => true));
            break;

        case 'delete_hierarchie_level':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $recursive = isset($_POST['recursive']) ? (int)$_POST['recursive'] : 0;
            if ($id <= 0) {
                throw new Exception('Niveau hiérarchique invalide');
            }

            $node = Hierarchie::getById($id, false);
            if (!$node) {
                throw new Exception('Niveau hiérarchique introuvable');
            }

            $deleted = Hierarchie::deleteLevel($id, $recursive === 1);
            if ($deleted <= 0) {
                throw new Exception('Suppression impossible');
            }

            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'hierarchie',
                $id,
                'Suppression niveau hiérarchique : ' . $node['nom'] . ($recursive === 1 ? ' (récursive)' : '')
            );
            echo json_encode(array('success' => true));
            break;

        case 'move_hierarchie_level':
            if (!$isAdmin) throw new Exception('Accès refusé');

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
            $ordre = isset($_POST['ordre']) ? (int)$_POST['ordre'] : 0;

            if ($id <= 0) {
                throw new Exception('Niveau hiérarchique invalide');
            }

            $node = Hierarchie::getById($id, false);
            if (!$node) {
                throw new Exception('Niveau hiérarchique introuvable');
            }

            Hierarchie::moveLevel($id, $parentId, $ordre);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'MOVE',
                'hierarchie',
                $id,
                'Déplacement niveau hiérarchique : ' . $node['nom'] . ' (parent=' . $parentId . ', ordre=' . $ordre . ')'
            );
            echo json_encode(array('success' => true));
            break;

        case 'move_projet':
            $projetId = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
            $domaineId = isset($_POST['domaine_id']) ? (int)$_POST['domaine_id'] : 0;

            $sourceHierarchyId = $getHierarchyIdByProjet($projetId);
            $targetHierarchyId = $getHierarchyIdByDomaine($domaineId);
            if ($sourceHierarchyId <= 0 || $targetHierarchyId <= 0) throw new Exception('Projet ou domaine introuvable');
            if (!$userHasMinRoleOnHierarchy($sourceHierarchyId, 'modificateur') || !$userHasMinRoleOnHierarchy($targetHierarchyId, 'modificateur')) {
                throw new Exception('Accès refusé');
            }

            Projet::moveToDomaine($projetId, $domaineId);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'projet',
                $projetId,
                'Déplacement projet vers domaine ' . $domaineId
            );
            echo json_encode(array('success' => true));
            break;

        default:
            throw new Exception('Action inconnue');
    }

} catch (Exception $e) {
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}