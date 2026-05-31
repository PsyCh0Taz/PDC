<?php
// ============================================================
// PDC — Liens de partage
// ============================================================

class ShareLink {

    /**
     * Crée un lien de partage et retourne le token
     */
    public static function create($urlParams, $createdBy) {
        $db    = Database::getInstance();
        $token = bin2hex(random_bytes(32)); // 64 chars hex
        $db->insert(
            'INSERT INTO share_links (token, url_params, created_by) VALUES (?, ?, ?)',
            array($token, $urlParams, $createdBy)
        );
        return $token;
    }

    /**
     * Retourne le lien par token
     */
    public static function getByToken($token) {
        $token = preg_replace('/[^a-f0-9]/', '', $token);
        $db    = Database::getInstance();
        return $db->fetchOne('SELECT * FROM share_links WHERE token = ?', array($token));
    }

    /**
     * Construit l'URL publique d'un lien
     */
    public static function buildUrl($token) {
        return APP_URL . '/share.php?token=' . urlencode($token);
    }
}