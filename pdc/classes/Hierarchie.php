<?php
// ============================================================
// PDC — Classe Hierarchie
// ============================================================

class Hierarchie {

    public static function getAll($onlyActive = true) {
        $tArray = self::getSubLevel(0, $onlyActive);

        foreach ($tArray as &$item) {
            $item['subItems'] = self::getSubLevel($item['id'], $onlyActive);
        }
        return $tArray;
    }

    public static function getSubLevel($hierarchieId, $onlyActive = true) {
        $db = Database::getInstance();
        $sql = 'SELECT * FROM hierarchie WHERE id_parent = ?';
        if ($onlyActive) {
            $sql .= ' AND actif = 1';
        }
        $sql .= ' ORDER BY ordre ASC, nom ASC';
        $tArray = $db->fetchAll(
            $sql,
            array((int)$hierarchieId)
        );
        foreach ($tArray as &$item) {
            $item['subItems'] = self::getSubLevel($item['id'], $onlyActive);
        }
        return $tArray;
    }   
    
    public static function getLevel($hierarchieId, $onlyActive = true) {
        $db = Database::getInstance();
        $sql = 'SELECT * FROM hierarchie WHERE id = ?';
        if ($onlyActive) {
            $sql .= ' AND actif = 1';
        }
        $tArray = $db->fetchAll(
            $sql,
            array((int)$hierarchieId)
        );
        if (empty($tArray)) {
            return array();
        }
        $tArray[0]['subItems'] = self::getSubLevel($hierarchieId, $onlyActive);
        return $tArray;
    }   


    public static function getUpperLevel($tree, $searchId, $path=array()){
        if ($searchId === null) {
            return null;
        }
        foreach ($tree as $node) {

            $currentPath = $path;
            $currentPath[] = $node;

            if ($node['id'] == $searchId) {
                return $currentPath;
            }

            if (!empty($node['subItems'])) {

                $result = self::getUpperLevel(
                    $node['subItems'],
                    $searchId,
                    $currentPath
                );

                if ($result !== null) {
                    return $result;
                }
            }
        }
    return null;
    }

    public static function getById($id, $onlyActive = false) {
        $db = Database::getInstance();
        $sql = 'SELECT * FROM hierarchie WHERE id = ?';
        if ($onlyActive) {
            $sql .= ' AND actif = 1';
        }
        return $db->fetchOne(
            $sql,
            array((int)$id)
        );
    }

    public static function getEntreprises($onlyActive = true) {
        return self::getAll($onlyActive);
    }

    public static function getEntrepriseById($id, $onlyActive = true) {
        $level = self::getLevel($id, $onlyActive);
        return !empty($level[0]) ? $level[0] : null;
    }

    public static function getDepartements($hierarchieId, $onlyActive = true) {
        return self::getSubLevel($hierarchieId, $onlyActive);
    }

    public static function getDepartementById($id, $onlyActive = true) {
        return self::getById($id, $onlyActive);
    }

    public static function getServices($hierarchieId, $onlyActive = true) {
        return self::getSubLevel($hierarchieId, $onlyActive);
    }

    public static function getServiceById($id, $onlyActive = true) {
        return self::getById($id, $onlyActive);
    }

    public static function setActif($hierarchieId, $actif) {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE hierarchie SET actif = ? WHERE id = ?',
            array($actif ? 1 : 0, (int)$hierarchieId)
        );
    }

    private static function normalizeSiblingOrder($parentId) {
        $db = Database::getInstance();
        $siblings = $db->fetchAll(
            'SELECT id FROM hierarchie WHERE id_parent = ? ORDER BY ordre ASC, id ASC',
            array((int)$parentId)
        );

        $pos = 1;
        foreach ($siblings as $sibling) {
            $db->execute(
                'UPDATE hierarchie SET ordre = ? WHERE id = ?',
                array($pos, (int)$sibling['id'])
            );
            $pos++;
        }
    }

    private static function isAncestorOf($ancestorId, $nodeId) {
        $db = Database::getInstance();
        $ancestorId = (int)$ancestorId;
        $nodeId = (int)$nodeId;

        if ($ancestorId <= 0 || $nodeId <= 0) {
            return false;
        }

        $current = self::getById($nodeId, false);
        while ($current && (int)$current['id_parent'] > 0) {
            $parentId = (int)$current['id_parent'];
            if ($parentId === $ancestorId) {
                return true;
            }
            $current = $db->fetchOne('SELECT id, id_parent FROM hierarchie WHERE id = ?', array($parentId));
        }

        return false;
    }

    public static function moveLevel($id, $newParentId, $newOrder) {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        $id = (int)$id;
        $newParentId = (int)$newParentId;
        $newOrder = (int)$newOrder;

        if ($id <= 0) {
            throw new Exception('Niveau hiérarchique invalide');
        }
        if ($newParentId < 0) {
            throw new Exception('Parent hiérarchique invalide');
        }
        if ($id === $newParentId) {
            throw new Exception('Déplacement invalide');
        }

        $node = self::getById($id, false);
        if (!$node) {
            throw new Exception('Niveau hiérarchique introuvable');
        }

        if ($newParentId > 0) {
            $parent = self::getById($newParentId, false);
            if (!$parent) {
                throw new Exception('Parent hiérarchique introuvable');
            }
        }

        if (self::isAncestorOf($id, $newParentId)) {
            throw new Exception('Déplacement invalide: impossible de déplacer un niveau dans son sous-arbre');
        }

        $oldParentId = (int)$node['id_parent'];

        $pdo->beginTransaction();
        try {
            $db->execute(
                'UPDATE hierarchie SET id_parent = ? WHERE id = ?',
                array($newParentId, $id)
            );

            if ($oldParentId !== $newParentId) {
                self::normalizeSiblingOrder($oldParentId);
            }

            $siblings = $db->fetchAll(
                'SELECT id FROM hierarchie WHERE id_parent = ? ORDER BY ordre ASC, id ASC',
                array($newParentId)
            );

            $orderedIds = array();
            foreach ($siblings as $sibling) {
                $siblingId = (int)$sibling['id'];
                if ($siblingId !== $id) {
                    $orderedIds[] = $siblingId;
                }
            }

            if ($newOrder < 0) {
                $newOrder = 0;
            }
            if ($newOrder > count($orderedIds)) {
                $newOrder = count($orderedIds);
            }

            array_splice($orderedIds, $newOrder, 0, array($id));

            $pos = 1;
            foreach ($orderedIds as $siblingId) {
                $db->execute(
                    'UPDATE hierarchie SET ordre = ? WHERE id = ?',
                    array($pos, (int)$siblingId)
                );
                $pos++;
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function createLevel($parentId, $nom) {
        $db = Database::getInstance();
        $parentId = (int)$parentId;

        if ($parentId > 0) {
            $parent = self::getById($parentId, false);
            if (!$parent) {
                throw new Exception('Parent hiérarchique introuvable');
            }
        }

        $last = $db->fetchOne(
            'SELECT MAX(ordre) AS m FROM hierarchie WHERE id_parent = ?',
            array($parentId)
        );
        $ordre = $last && $last['m'] !== null ? (int)$last['m'] + 1 : 1;

        return $db->insert(
            'INSERT INTO hierarchie (nom, id_parent, ordre, actif) VALUES (?, ?, ?, 1)',
            array(self::sanitize($nom), $parentId, $ordre)
        );
    }

    public static function updateLevel($id, $nom) {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE hierarchie SET nom = ? WHERE id = ?',
            array(self::sanitize($nom), (int)$id)
        );
    }

    public static function hasChildren($id) {
        $db = Database::getInstance();
        $row = $db->fetchOne('SELECT COUNT(*) AS c FROM hierarchie WHERE id_parent = ?', array((int)$id));
        return $row ? ((int)$row['c'] > 0) : false;
    }

    public static function hasDomaines($id) {
        $db = Database::getInstance();
        $row = $db->fetchOne('SELECT COUNT(*) AS c FROM domaines WHERE hierarchie_id = ?', array((int)$id));
        return $row ? ((int)$row['c'] > 0) : false;
    }

    private static function collectSubtreeIds($id, &$ids) {
        $db = Database::getInstance();
        $children = $db->fetchAll('SELECT id FROM hierarchie WHERE id_parent = ?', array((int)$id));
        foreach ($children as $child) {
            $childId = (int)$child['id'];
            self::collectSubtreeIds($childId, $ids);
            $ids[] = $childId;
        }
    }

    private static function deleteDomainesAndProjectsByLevel($levelId) {
        $db = Database::getInstance();
        $domaines = $db->fetchAll('SELECT id FROM domaines WHERE hierarchie_id = ?', array((int)$levelId));

        foreach ($domaines as $domaine) {
            $domaineId = (int)$domaine['id'];
            $projets = $db->fetchAll('SELECT id FROM projets WHERE domaine_id = ?', array($domaineId));

            foreach ($projets as $projet) {
                $projetId = (int)$projet['id'];
                $db->execute('DELETE FROM projet_gradients WHERE projet_id = ?', array($projetId));
                $db->execute('DELETE FROM projet_jalons WHERE projet_id = ?', array($projetId));
                $db->execute('DELETE FROM projets WHERE id = ?', array($projetId));
            }

            $db->execute('DELETE FROM domaines WHERE id = ?', array($domaineId));
        }
    }

    public static function deleteLevel($id, $recursive = false) {
        $db = Database::getInstance();
        $levelId = (int)$id;
        $recursive = (bool)$recursive;

        $level = self::getById($levelId, false);
        if (!$level) {
            throw new Exception('Niveau hiérarchique introuvable');
        }

        if (!$recursive) {
            if (self::hasChildren($levelId)) {
                throw new Exception('Suppression impossible: ce niveau contient des sous-niveaux');
            }
            if (self::hasDomaines($levelId)) {
                throw new Exception('Suppression impossible: ce niveau contient des domaines');
            }

            // Nettoyage des droits associés à ce scope hiérarchique.
            $db->execute('DELETE FROM utilisateurs_roles WHERE role_dn = ?', array('hierarchie:' . $levelId));

            return $db->execute('DELETE FROM hierarchie WHERE id = ?', array($levelId));
        }

        $subtreeIds = array();
        self::collectSubtreeIds($levelId, $subtreeIds);
        $subtreeIds[] = $levelId;

        foreach ($subtreeIds as $nodeId) {
            self::deleteDomainesAndProjectsByLevel((int)$nodeId);
        }

        foreach ($subtreeIds as $nodeId) {
            $db->execute('DELETE FROM utilisateurs_roles WHERE role_dn = ?', array('hierarchie:' . (int)$nodeId));
        }

        $deleted = 0;
        foreach ($subtreeIds as $nodeId) {
            $deleted += (int)$db->execute('DELETE FROM hierarchie WHERE id = ?', array((int)$nodeId));
        }

        return $deleted;
    }
    // ---- Domaines ----

    public static function getDomainesByLevel($hierarchieId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM domaines WHERE hierarchie_id = ? ORDER BY ordre ASC, nom ASC',
            array((int)$hierarchieId)
        );
    }

    public static function getDomaineById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM domaines WHERE id = ?', array((int)$id));
    }

    public static function createDomaine($serviceId, $nom) {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM domaines WHERE hierarchie_id = ?', array((int)$serviceId));
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO domaines (hierarchie_id, nom, ordre) VALUES (?, ?, ?)',
            array((int)$serviceId, self::sanitize($nom), $ordre)
        );
    }

    private static function normalizeDomainesOrderByHierarchy($hierarchieId) {
        $db = Database::getInstance();
        $domaines = $db->fetchAll(
            'SELECT id FROM domaines WHERE hierarchie_id = ? ORDER BY ordre ASC, id ASC',
            array((int)$hierarchieId)
        );

        $ordre = 0;
        foreach ($domaines as $domaine) {
            $db->execute(
                'UPDATE domaines SET ordre = ? WHERE id = ?',
                array($ordre, (int)$domaine['id'])
            );
            $ordre++;
        }
    }

    public static function updateDomaine($id, $nom, $hierarchieId = null) {
        $db = Database::getInstance();
        $pdo = $db->getPdo();

        $id = (int)$id;
        $existing = $db->fetchOne('SELECT id, hierarchie_id FROM domaines WHERE id = ?', array($id));
        if (!$existing) {
            throw new Exception('Domaine introuvable');
        }

        $currentHierarchyId = (int)$existing['hierarchie_id'];
        $targetHierarchyId = ($hierarchieId === null || (int)$hierarchieId <= 0)
            ? $currentHierarchyId
            : (int)$hierarchieId;

        if ($targetHierarchyId === $currentHierarchyId) {
            return $db->execute(
                'UPDATE domaines SET nom = ? WHERE id = ?',
                array(self::sanitize($nom), $id)
            );
        }

        $targetLevel = self::getById($targetHierarchyId, false);
        if (!$targetLevel) {
            throw new Exception('Niveau hiérarchique cible introuvable');
        }

        $pdo->beginTransaction();
        try {
            $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM domaines WHERE hierarchie_id = ?', array($targetHierarchyId));
            $targetOrder = ($last && $last['m'] !== null) ? ((int)$last['m'] + 1) : 0;

            $db->execute(
                'UPDATE domaines SET nom = ?, hierarchie_id = ?, ordre = ? WHERE id = ?',
                array(self::sanitize($nom), $targetHierarchyId, $targetOrder, $id)
            );

            self::normalizeDomainesOrderByHierarchy($currentHierarchyId);
            self::normalizeDomainesOrderByHierarchy($targetHierarchyId);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function deleteDomaine($id) {
        $db = Database::getInstance();
        return $db->execute('DELETE FROM domaines WHERE id = ?', array((int)$id));
    }

    public static function updateDomainesOrdre($ordres) {
        $db = Database::getInstance();
        foreach ($ordres as $id => $ordre) {
            $db->execute('UPDATE domaines SET ordre = ? WHERE id = ?', array((int)$ordre, (int)$id));
        }
    }

    // ---- Administration : activation services ----

    public static function setServiceActif($serviceId, $actif) {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE services SET actif = ? WHERE id = ?',
            array($actif ? 1 : 0, (int)$serviceId)
        );
    }

    // ---- Création / Suppression Entreprises ----

    public static function createEntreprise($nom, $ldapDn = '') {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM entreprises');
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO entreprises (nom, ldap_dn, ordre, actif) VALUES (?, ?, ?, 1)',
            array(self::sanitize($nom), $ldapDn, $ordre)
        );
    }

    public static function updateEntreprise($id, $nom, $ldapDn = '') {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE entreprises SET nom = ?, ldap_dn = ? WHERE id = ?',
            array(self::sanitize($nom), $ldapDn, (int)$id)
        );
    }

    public static function deleteEntreprise($id) {
        $db = Database::getInstance();
        return $db->execute('DELETE FROM entreprises WHERE id = ?', array((int)$id));
    }

    // ---- Création / Suppression Départements ----

    public static function createDepartement($entrepriseId, $nom, $ldapDn = '') {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM departements WHERE entreprise_id = ?', array((int)$entrepriseId));
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO departements (entreprise_id, nom, ldap_dn, ordre, actif) VALUES (?, ?, ?, ?, 1)',
            array((int)$entrepriseId, self::sanitize($nom), $ldapDn, $ordre)
        );
    }

    public static function updateDepartement($id, $nom, $ldapDn = '') {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE departements SET nom = ?, ldap_dn = ? WHERE id = ?',
            array(self::sanitize($nom), $ldapDn, (int)$id)
        );
    }

    public static function deleteDepartement($id) {
        $db = Database::getInstance();
        return $db->execute('DELETE FROM departements WHERE id = ?', array((int)$id));
    }

    // ---- Création / Suppression Services ----

    public static function createService($departementId, $nom, $ldapDn = '') {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM services WHERE departement_id = ?', array((int)$departementId));
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO services (departement_id, nom, ldap_dn, ordre, actif) VALUES (?, ?, ?, ?, 1)',
            array((int)$departementId, self::sanitize($nom), $ldapDn, $ordre)
        );
    }

    public static function updateService($id, $nom, $ldapDn = '') {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE services SET nom = ?, ldap_dn = ? WHERE id = ?',
            array(self::sanitize($nom), $ldapDn, (int)$id)
        );
    }

    public static function deleteService($id) {
        $db = Database::getInstance();
        return $db->execute('DELETE FROM services WHERE id = ?', array((int)$id));
    }

    // ---- Synchronisation LDAP → MySQL ----

    /**
     * Importe / met à jour la structure LDAP dans la base
     * (à appeler depuis l'administration)
     */
    public static function syncLdap() {
        // Cette méthode est appelée manuellement depuis la vue admin
        // Elle parcourt l'arbre LDAP et crée/met à jour les entités
        // La logique complète dépend de la structure LDAP spécifique
        // Implémentation simplifiée ci-dessous
        return true;
    }

    // ---- Paramètres ----

    public static function getParam($cle) {
        $db = Database::getInstance();
        $row = $db->fetchOne('SELECT valeur FROM parametres WHERE cle = ?', array($cle));
        return $row ? $row['valeur'] : '';
    }

    public static function setParam($cle, $valeur) {
        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = ?',
            array($cle, $valeur, $valeur)
        );
    }

    // ---- Helpers ----

    private static function sanitize($s) {
        return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
    }
}