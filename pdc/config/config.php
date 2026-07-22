<?php
// ============================================================
// PDC — Configuration générale
// ============================================================

// Base de données
define('DB_HOST',     'localhost');
define('DB_NAME',     'pdc_user');
define('DB_USER',     'pdc_user');
define('DB_PASS',     'pdc_user');
define('DB_CHARSET',     'utf8mb4');

// Mode Offline (pour développement/test)
define('OFFLINE_MODE',     true);

// Annuaire externe utilisé pour rechercher les utilisateurs à autoriser
define('ANNUAIRE_SEARCH_URL', 'http://127.0.0.1/pdc/annuaire.json');

// LDAP
define('LDAP_HOST',     '192.168.1.2');
define('LDAP_PORT',        389);
define('LDAP_BASE_DN',     'dc=a,dc=c,dc=d,dc=fr');
define('LDAP_USER_DN',     'cn=admin,dc=a,dc=c,dc=d,dc=fr');
define('LDAP_USER_DN_PASS',     'admin');

// Application
define('APP_NAME',    'Plan de Charge');
define('APP_URL',     'http://localhost/pdc');
define('SESSION_NAME','pdc_session');

// Durée de session (secondes)
define('SESSION_LIFETIME',        7200);

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Dev
ini_set("display_errors",1);
error_reporting(E_ALL);
