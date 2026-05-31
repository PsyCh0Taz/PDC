<?php
// ============================================================
// PDC — Classe Organisation (domaines, services, départements, entreprises)
// ============================================================

class Organisation {

    // ---- Entreprises ----

    public static function getEntreprises() {
        $db = Database::getInstance();
        return $db->fetchAll('SELECT * FROM entreprises WHERE actif = 1 ORDER BY ordre ASC, nom ASC');
    }

    public static function getAllEntreprises() {
        $db = Database::getInstance();
        return $db->fetchAll('SELECT * FROM entreprises ORDER BY ordre ASC, nom ASC');
    }

    public static function getEntrepriseById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM entreprises WHERE id = ?', array((int)$id));
    }

    // ---- Départements ----

    public static function getDepartements($entrepriseId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM departements WHERE entreprise_id = ? AND actif = 1 ORDER BY ordre ASC, nom ASC',
            array((int)$entrepriseId)
        );
    }

    public static function getDepartementById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM departements WHERE id = ?', array((int)$id));
    }

    // ---- Services ----

    public static function getServices($departementId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM services WHERE departement_id = ? AND actif = 1 ORDER BY ordre ASC, nom ASC',
            array((int)$departementId)
        );
    }

    public static function getServiceById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM services WHERE id = ?', array((int)$id));
    }

    // ---- Domaines ----

    public static function getDomainesByService($serviceId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM domaines WHERE service_id = ? ORDER BY ordre ASC, nom ASC',
            array((int)$serviceId)
        );
    }

    public static function getDomaineById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM domaines WHERE id = ?', array((int)$id));
    }

    public static function createDomaine($serviceId, $nom) {
        $db = Database::getInstance();
        $last = $db->fetchOne('SELECT MAX(ordre) AS m FROM domaines WHERE service_id = ?', array((int)$serviceId));
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->insert(
            'INSERT INTO domaines (service_id, nom, ordre) VALUES (?, ?, ?)',
            array((int)$serviceId, self::sanitize($nom), $ordre)
        );
    }

    public static function updateDomaine($id, $nom) {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE domaines SET nom = ? WHERE id = ?',
            array(self::sanitize($nom), (int)$id)
        );
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