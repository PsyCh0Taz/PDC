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

    private static function hasChildren($id) {
        $db = Database::getInstance();
        $row = $db->fetchOne('SELECT COUNT(*) AS c FROM hierarchie WHERE id_parent = ?', array((int)$id));
        return $row ? ((int)$row['c'] > 0) : false;
    }

    private static function hasDomaines($id) {
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

    public static function createDomaine($hierarchieId, $nom) {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM domaines WHERE hierarchie_id = ?', array((int)$hierarchieId));
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO domaines (hierarchie_id, nom, ordre) VALUES (?, ?, ?)',
            array((int)$hierarchieId, self::sanitize($nom), $ordre)
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

    // ---- Helpers ----

    private static function sanitize($s) {
        return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
    }
}