<?php
// ============================================================
// PDC — Classe Auth (LDAP)
// ============================================================

class Auth {

    /**
     * Authentification offline pour l'utilisateur 'taz' (développement)
     * Retourne les infos utilisateur ou false
     */
    private static function loginOffline($username, $password) {
        // Seul l'utilisateur taz peut se connecter en mode offline
        if ($username !== 'taz' || $password !== 'taz') {
            return false;
        }

        $info = array(
            'username'    => 'taz',
            'dn'          => 'cn=taz,ou=ccoa,ou=ec2sa,ou=ba118,ou=users,dc=a,dc=c,dc=d,dc=fr',
            'displayname' => 'Utilisateur Offline',
            'mail'        => 'taz@offline.local',
        );

        // Charger les rôles depuis MySQL
        $info['roles'] = self::loadRoles('taz');

        return $info;
    }

    /**
     * Tente de connecter un utilisateur via LDAP ou mode offline.
     * Retourne un tableau avec les infos utilisateur ou false.
     */
    public static function login($username, $password) {
        // Sanity
        $username = self::sanitizeUsername($username);
        if (empty($username) || empty($password)) {
            return false;
        }

        // Mode offline activé
        if (OFFLINE_MODE) {
            return self::loginOffline($username, $password);
        }

        $info = LDAP::authenticateUser($username, $password);
        if ($info === false) {
            return false;
        }

        // Charger les rôles depuis MySQL
        $info['roles'] = self::loadRoles($username);

        return $info;
    }

    /**
     * Charge les rôles de l'utilisateur depuis la base
     */
    public static function loadRoles($username) {
        $db   = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT role_dn, role FROM pdc_utilisateurs_roles WHERE username = ?',
            array($username)
        );
        $roles = array();
        foreach ($rows as $row) {
            $roles[$row['role_dn']] = $row['role'];
        }
        return $roles;
    }

    /**
     * Démarre ou reprend la session sécurisée
     */
    public static function startSession() {
        session_name(SESSION_NAME);
        session_set_cookie_params('lifetime',SESSION_LIFETIME);
        session_set_cookie_params('path','/');
        session_set_cookie_params('httponly',true);
        session_set_cookie_params('samesite','Lax');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Retourne l'utilisateur connecté ou redirige vers le login
     */
    public static function requireLogin() {
        self::startSession();
        if (empty($_SESSION['user'])) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
        // Rafraîchit les rôles
        $_SESSION['user']['roles'] = self::loadRoles($_SESSION['user']['username']);
        return $_SESSION['user'];
    }

    /**
     * Connecte et sauvegarde en session
     */
    public static function setUser($userInfo) {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user'] = $userInfo;
    }

    /**
     * Déconnecte
     */
    public static function logout() {
        self::startSession();
        $_SESSION = array();
        session_destroy();
    }

    /**
     * Sanity sur le login
     */
    private static function sanitizeUsername($str) {
        return preg_replace('/[^a-zA-Z0-9._\-@]/', '', trim($str));
    }

    /**
     * Recherche des utilisateurs LDAP par login, nom ou e-mail.
     */
    public static function searchUsers($query, $limit = 20) {
        return LDAP::searchUsers($query, $limit);
    }

}