<?php
/**
 * TIR118 - Initialisation
 * À inclure en premier dans chaque page PHP.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/', '', false, true); // HttpOnly
    session_start();
}

// Depuis includes/ → remonter d'un niveau pour trouver config.php
$_base = dirname(__DIR__);
require_once $_base . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/functions.php';
