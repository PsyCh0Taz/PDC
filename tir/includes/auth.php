<?php
/**
 * TIR118 - Authentification LDAP et gestion de session
 */

// Compatibilité PHP 5.4 : ldap_escape() n'existe qu'en PHP 5.6+
if (!function_exists('ldap_escape')) {
    define('LDAP_ESCAPE_FILTER', 0x01);
    define('LDAP_ESCAPE_DN',     0x02);
    function ldap_escape($value, $ignore = '', $flags = 0) {
        if ($flags & LDAP_ESCAPE_FILTER) {
            $chars = array('\\', '*', '(', ')', "\x00");
        } elseif ($flags & LDAP_ESCAPE_DN) {
            $chars = array('\\', ',', '=', '+', '<', '>', ';', '"', '#');
        } else {
            $chars = array('\\', '*', '(', ')', "\x00", ',', '=', '+', '<', '>', ';', '"', '#');
        }
        foreach ($chars as $char) {
            if (strpos($ignore, $char) === false) {
                $value = str_replace($char, '\\' . sprintf('%02X', ord($char)), $value);
            }
        }
        return $value;
    }
}

/**
 * Authentifie un utilisateur via LDAP et crée/met à jour l'entrée en base.
 * @return array ['success' => bool, 'error' => string|null]
 */
function ldap_login($login, $password) {
    if (empty($login) || empty($password)) {
        return array('success' => false, 'error' => 'Identifiant et mot de passe requis.');
    }

    $conn = @ldap_connect(LDAP_HOST, LDAP_PORT);
    if (!$conn) {
        return array('success' => false, 'error' => 'Connexion au serveur LDAP impossible.');
    }

    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

    // Bind applicatif
    $bind = @ldap_bind($conn, LDAP_BIND_DN, LDAP_BIND_PW);
    if (!$bind) {
        ldap_close($conn);
        return array('success' => false, 'error' => 'Erreur de configuration LDAP (bind applicatif).');
    }

    // Recherche de l'utilisateur
    $filter = sprintf(LDAP_USER_FILTER, ldap_escape($login, '', LDAP_ESCAPE_FILTER),ldap_escape($login, '', LDAP_ESCAPE_FILTER));
    $result = @ldap_search($conn, LDAP_BASE_DN, $filter, array('dn', 'uid', 'cn', 'givenName', 'sn', 'mail'));

    if (!$result || ldap_count_entries($conn, $result) === 0) {
        ldap_close($conn);
        return array('success' => false, 'error' => 'Utilisateur introuvable dans l\'annuaire.');
    }

    $entry   = ldap_first_entry($conn, $result);
    $user_dn = ldap_get_dn($conn, $entry);
    $attrs   = ldap_get_attributes($conn, $entry);

    // Vérification du mot de passe (bind utilisateur)
    $user_bind = @ldap_bind($conn, $user_dn, $password);
    if (!$user_bind) {
        ldap_close($conn);
        return array('success' => false, 'error' => 'Mot de passe incorrect.');
    }

    $nom    = isset($attrs['sn'][0])        ? $attrs['sn'][0]        : '';
    $prenom = isset($attrs['givenName'][0]) ? $attrs['givenName'][0] : '';
    $mail   = isset($attrs['mail'][0])      ? $attrs['mail'][0]      : '';
    $uid    = isset($attrs['uid'][0])       ? $attrs['uid'][0]       : $login;

    // Le DN contient-il l'OU admin ?
    $is_admin = (stripos($user_dn, LDAP_ADMIN_OU) !== false) ? 1 : 0;

    ldap_close($conn);

    // Synchronisation en base
    $pdo  = get_pdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE ldap_uid = ?');
    $stmt->execute(array($uid));
    $user = $stmt->fetch();

    if ($user) {
        $pdo->prepare(
            'UPDATE users SET nom=?, prenom=?, mail=?, ou=?, is_admin=?, last_login=NOW() WHERE ldap_uid=?'
        )->execute(array($nom, $prenom, $mail, $user_dn, $is_admin, $uid));
        $user_id = (int)$user['id'];
    } else {
        $pdo->prepare(
            'INSERT INTO users (ldap_uid, nom, prenom, mail, ou, is_admin, last_login) VALUES (?,?,?,?,?,?,NOW())'
        )->execute(array($uid, $nom, $prenom, $mail, $user_dn, $is_admin));
        $user_id = (int)$pdo->lastInsertId();
    }

    // Remplissage de la session
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user_id;
    $_SESSION['uid']       = $uid;
    $_SESSION['nom']       = $nom;
    $_SESSION['prenom']    = $prenom;
    $_SESSION['mail']      = $mail;
    $_SESSION['is_admin']  = $is_admin;
    $_SESSION['logged_at'] = time();

    return array('success' => true);
}

/** Redirige vers login si l'utilisateur n'est pas authentifié ou si la session a expiré. */
function require_auth() {
    $ok = isset($_SESSION['user_id'])
       && isset($_SESSION['logged_at'])
       && (time() - $_SESSION['logged_at']) < SESSION_LIFETIME;

    if (!$ok) {
        session_unset();
        session_destroy();
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header('Location: ' . APP_URL . '/login.php?redirect=' . $redirect);
        exit;
    }
}

/** Redirige vers l'accueil si l'utilisateur n'est pas administrateur. */
function require_admin() {
    require_auth();
    if (empty($_SESSION['is_admin'])) {
        header('Location: ' . APP_URL . '/index.php?err=access');
        exit;
    }
}

function is_admin() {
    return !empty($_SESSION['is_admin']);
}

function current_user_id() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function current_user_mail() {
    return isset($_SESSION['mail']) ? $_SESSION['mail'] : '';
}
