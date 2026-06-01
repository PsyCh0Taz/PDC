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
            'groups'      => array(),
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

        $ldap = @ldap_connect(LDAP_HOST, LDAP_PORT);
        if (!$ldap) {
            throw new Exception('Impossible de se connecter au serveur LDAP.');
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        // Bind compte de service
        $bound = @ldap_bind($ldap, LDAP_USER_DN, LDAP_USER_DN_PASS);
        if (!$bound) {
            ldap_close($ldap);
            throw new Exception('Impossible de s\'authentifier sur le serveur LDAP.');
        }

        // Recherche de l'utilisateur
        $filter   = '(|(uid=' . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . ')(sAMAccountName=' . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . '))';
        $search   = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('dn','cn','mail','displayName','ou','memberOf'));
        if (!$search) {
            ldap_close($ldap);
            return false;
        }

        $entries = ldap_get_entries($ldap, $search);
        if ($entries['count'] === 0) {
            ldap_close($ldap);
            return false;
        }

        $userDn = $entries[0]['dn'];

        // Bind utilisateur pour vérifier le mot de passe
        $userBound = @ldap_bind($ldap, $userDn, $password);
        if (!$userBound) {
            ldap_close($ldap);
            return false;
        }

        $info = array(
            'username'    => $username,
            'dn'          => $userDn,
            'displayname' => isset($entries[0]['displayname'][0]) ? $entries[0]['displayname'][0] : $username,
            'mail'        => isset($entries[0]['mail'][0]) ? $entries[0]['mail'][0] : '',
            'groups'      => array(),
        );

        if (isset($entries[0]['memberof'])) {
            for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                $info['groups'][] = $entries[0]['memberof'][$i];
            }
        }

        ldap_close($ldap);

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
            'SELECT role_dn, role FROM utilisateurs_roles WHERE username = ?',
            array($username)
        );
        $roles = array();
        foreach ($rows as $row) {
            $roles[$row['role_dn']] = $row['role'];
        }
        return $roles;
    }

    /**
     * Retourne le rôle effectif de l'utilisateur sur un DN donné
     * (recherche dans les ancêtres si pas de rôle direct)
     */
    public static function getRoleForDn($userRoles, $dn) {
        // Administrateur global ?
        if (isset($userRoles['*'])) return 'admin';

        // Rôle direct
        if (isset($userRoles[$dn])) return $userRoles[$dn];

        // Remonte la hiérarchie DN
        $parts = ldap_explode_dn($dn, 0);
        if ($parts && $parts['count'] > 1) {
            array_shift($parts);
            unset($parts['count']);
            $parentDn = implode(',', $parts);
            return self::getRoleForDn($userRoles, $parentDn);
        }

        return null;
    }

    /**
     * Vérifie si l'utilisateur a au minimum un rôle sur un DN
     */
    public static function hasRole($userRoles, $dn, $minRole) {
        $hierarchy = array('lecteur'=>1, 'modificateur'=>2, 'responsable'=>3, 'admin'=>4);
        $role = self::getRoleForDn($userRoles, $dn);
        if (!$role) return false;
        $min = isset($hierarchy[$minRole]) ? $hierarchy[$minRole] : 99;
        $cur = isset($hierarchy[$role]) ? $hierarchy[$role] : 0;
        return $cur >= $min;
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
     * Retourne l'utilisateur connecté ou null (sans redirection)
     */
    public static function getUser() {
        self::startSession();
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
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
     * Liste les utilisateurs LDAP sous un DN
     */
    public static function getUsersUnderDn($baseDn) {
        $ldap = @ldap_connect(LDAP_HOST, LDAP_PORT);
        if (!$ldap) return array();

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap, LDAP_USER_DN, LDAP_USER_DN_PASS)) {
            ldap_close($ldap);
            return array();
        }

        $filter = '(|(objectClass=person)(objectClass=inetOrgPerson)(objectClass=user))';
        $search = @ldap_search($ldap, $baseDn, $filter, array('dn','cn','uid','sAMAccountName','displayName','mail','ou'));
        if (!$search) {
            ldap_close($ldap);
            return array();
        }

        $entries = ldap_get_entries($ldap, $search);
        ldap_close($ldap);

        $users = array();
        for ($i = 0; $i < $entries['count']; $i++) {
            $e = $entries[$i];
            $uid = '';
            if (!empty($e['uid'][0]))           $uid = $e['uid'][0];
            elseif (!empty($e['samaccountname'][0])) $uid = $e['samaccountname'][0];
            if (empty($uid)) continue;
            $users[] = array(
                'username'    => $uid,
                'dn'          => $e['dn'],
                'displayname' => isset($e['displayname'][0]) ? $e['displayname'][0] : $uid,
                'mail'        => isset($e['mail'][0]) ? $e['mail'][0] : '',
            );
        }
        return $users;
    }

    /**
     * Récupère l'arborescence LDAP (OU / groupe)
     */
    public static function getLdapTree($baseDn = null) {
        if ($baseDn === null) $baseDn = LDAP_BASE_DN;

        $ldap = @ldap_connect(LDAP_HOST, LDAP_PORT);
        if (!$ldap) return array();

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap, LDAP_USER_DN, LDAP_USER_DN_PASS)) {
            ldap_close($ldap);
            return array();
        }

        $filter = '(|(objectClass=organizationalUnit)(objectClass=group)(objectClass=groupOfNames))';
        $search = @ldap_list($ldap, $baseDn, $filter, array('dn','ou','cn','objectClass'));
        if (!$search) {
            ldap_close($ldap);
            return array();
        }

        $entries = ldap_get_entries($ldap, $search);
        ldap_close($ldap);

        $tree = array();
        for ($i = 0; $i < $entries['count']; $i++) {
            $e = $entries[$i];
            $name = '';
            if (!empty($e['ou'][0]))  $name = $e['ou'][0];
            elseif (!empty($e['cn'][0])) $name = $e['cn'][0];
            $tree[] = array(
                'dn'   => $e['dn'],
                'name' => $name,
            );
        }
        return $tree;
    }
}