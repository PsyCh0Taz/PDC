<?php
// ============================================================
// PDC — Journalisation
// ============================================================

class Journal {

    /**
     * Journalise une connexion
     */
    public static function logConnexion($username, $ip, $viaPartage = false, $token = null) {
        $db = Database::getInstance();
        $db->insert(
            'INSERT INTO journal_connexions (username, ip, via_partage, share_token) VALUES (?, ?, ?, ?)',
            array($username, $ip, $viaPartage ? 1 : 0, $token)
        );
    }

    /**
     * Journalise une modification
     */
    public static function logModification($username, $ip, $action, $entite, $entiteId, $description) {
        $db = Database::getInstance();
        $db->insert(
            'INSERT INTO journal_modifications (username, ip, action, entite, entite_id, description) VALUES (?, ?, ?, ?, ?, ?)',
            array($username, $ip, $action, $entite, $entiteId, $description)
        );
    }

    /**
     * Retourne l'IP du client
     */
    public static function getIp() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}