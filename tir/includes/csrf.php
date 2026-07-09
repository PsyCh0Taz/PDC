<?php
/**
 * TIR118 - Protection CSRF
 */

/** Génère ou retourne le token CSRF de la session. */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        // openssl_random_pseudo_bytes disponible depuis PHP 4.3 (extension openssl)
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Retourne un champ HTML hidden contenant le token CSRF. */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Vérifie le token CSRF sur une requête POST.
 * Interrompt l'exécution avec HTTP 403 si le token est invalide.
 */
function csrf_verify() {
    $submitted = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $expected  = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

    if (!_tir_hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('<p style="font-family:sans-serif">Requête invalide (token CSRF). '
          . '<a href="javascript:history.back()">Retour</a></p>');
    }
}

/**
 * Comparaison à durée constante (hash_equals n'existe qu'en PHP 5.6+).
 */
function _tir_hash_equals($a, $b) {
    if (strlen($a) !== strlen($b)) {
        return false;
    }
    $diff = 0;
    for ($i = 0, $len = strlen($a); $i < $len; $i++) {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
}
