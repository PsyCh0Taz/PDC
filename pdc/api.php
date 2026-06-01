<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$currentUser = Auth::requireLogin();
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Vérifier les droits selon l'action
$isAdmin = false;
foreach ($currentUser['roles'] as $role) {
    if ($role === 'admin') {
        $isAdmin = true;
        break;
    }
}

try {
    switch ($action) {

        // ---- Domaines ----

        case 'create_domaine':
            $serviceId = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            if (empty($nom)) throw new Exception('Nom requis');

            $id = Organisation::createDomaine($serviceId, $nom);
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
            if (empty($nom)) throw new Exception('Nom requis');

            Organisation::updateDomaine($id, $nom);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'UPDATE',
                'domaine',
                $id,
                'Modification domaine : ' . $nom
            );
            echo json_encode(array('success' => true));
            break;

        case 'delete_domaine':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            Organisation::deleteDomaine($id);
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
            Organisation::updateDomainesOrdre($ordres);
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

            if (empty($titre) || empty($dateDebut) || empty($dateFin)) {
                throw new Exception('Données manquantes');
            }

            $id = Projet::create($domaineId, $titre, $dateDebut, $dateFin);
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

            if (empty($titre) || empty($dateDebut) || empty($dateFin)) {
                throw new Exception('Données manquantes');
            }

            Projet::update($id, $titre, $dateDebut, $dateFin);

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
            $users = $db->fetchAll('SELECT DISTINCT username, displayname, dn FROM utilisateurs ORDER BY displayname');
            $roles = $db->fetchAll('SELECT * FROM utilisateurs_roles');
            
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
                'roles' => $rolesMap
            ));
            break;

        case 'set_user_role':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $dn = isset($_POST['dn']) ? trim($_POST['dn']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] : 0;
            
            if (!$username || !in_array($role, array('admin', 'responsable', 'modificateur', 'lecteur'))) {
                throw new Exception('Paramètres invalides');
            }
            
            $db = Database::getInstance();
            
            if ($enabled) {
                $db->execute('DELETE FROM utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
                    array($username, $dn, $role));
                $db->insert(
                    'INSERT INTO utilisateurs_roles (username, role_dn, role) VALUES (?, ?, ?)',
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
                $db->execute('DELETE FROM utilisateurs_roles WHERE username = ? AND role_dn = ? AND role = ?',
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

        case 'move_projet':
            $projetId = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
            $domaineId = isset($_POST['domaine_id']) ? (int)$_POST['domaine_id'] : 0;
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

        // ---- Partage ----

        case 'create_share_link':
            $urlParams = isset($_POST['url_params']) ? $_POST['url_params'] : '';
            $token = ShareLink::create($urlParams, $currentUser['username']);
            $url = ShareLink::buildUrl($token);
            echo json_encode(array('success' => true, 'url' => $url));
            break;

        // ---- Organisation (Admin only) ----

        case 'get_ldap_organization':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $ldapOrg = LDAP::getFullOrganization();
            echo json_encode(array('success' => true, 'organization' => $ldapOrg));
            break;

        case 'create_entreprise':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $ldapDn = isset($_POST['ldap_dn']) ? trim($_POST['ldap_dn']) : '';
            
            if (empty($nom)) throw new Exception('Nom requis');
            
            $id = Organisation::createEntreprise($nom, $ldapDn);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'entreprise',
                $id,
                'Création entreprise : ' . $nom
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'delete_entreprise':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            Organisation::deleteEntreprise($id);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'entreprise',
                $id,
                'Suppression entreprise'
            );
            echo json_encode(array('success' => true));
            break;

        case 'create_departement':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $entrepriseId = isset($_POST['entreprise_id']) ? (int)$_POST['entreprise_id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $ldapDn = isset($_POST['ldap_dn']) ? trim($_POST['ldap_dn']) : '';
            
            if (empty($nom)) throw new Exception('Nom requis');
            
            $id = Organisation::createDepartement($entrepriseId, $nom, $ldapDn);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'departement',
                $id,
                'Création département : ' . $nom
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'delete_departement':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            Organisation::deleteDepartement($id);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'departement',
                $id,
                'Suppression département'
            );
            echo json_encode(array('success' => true));
            break;

        case 'create_service':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $departementId = isset($_POST['departement_id']) ? (int)$_POST['departement_id'] : 0;
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $ldapDn = isset($_POST['ldap_dn']) ? trim($_POST['ldap_dn']) : '';
            
            if (empty($nom)) throw new Exception('Nom requis');
            
            $id = Organisation::createService($departementId, $nom, $ldapDn);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'CREATE',
                'service',
                $id,
                'Création service : ' . $nom
            );
            echo json_encode(array('success' => true, 'id' => $id));
            break;

        case 'delete_service':
            if (!$isAdmin) throw new Exception('Accès refusé');
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            Organisation::deleteService($id);
            Journal::logModification(
                $currentUser['username'],
                Journal::getIp(),
                'DELETE',
                'service',
                $id,
                'Suppression service'
            );
            echo json_encode(array('success' => true));
            break;

        default:
            throw new Exception('Action inconnue');
    }

} catch (Exception $e) {
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}