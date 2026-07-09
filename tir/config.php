<?php
/**
 * TIR118 - Configuration
 * Remplissez les valeurs ci-dessous selon votre environnement.
 */

// ---------------------------------------------------------------------------
// Base de données MySQL
// ---------------------------------------------------------------------------
define('DB_HOST',    'localhost');
define('DB_NAME',    'tir_db');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_CHARSET', 'utf8');

// ---------------------------------------------------------------------------
// LDAP
// ---------------------------------------------------------------------------
define('LDAP_HOST',        'ldap://ldap.dev.pi');
define('LDAP_PORT',        389);
define('LDAP_BIND_DN',     'cn=admin,dc=a,dc=c,dc=d,dc=fr');   // DN du compte applicatif
define('LDAP_BIND_PW',     'admin');                        // Mot de passe du compte applicatif
define('LDAP_BASE_DN',     'dc=a,dc=c,dc=d,dc=fr');           // Base de recherche
define('LDAP_USER_FILTER', '(|(uid=%s)(sAMAccountName=%s))');                        // Filtre de recherche (%s = login)

// OU dont les membres ont le rôle administrateur
define('LDAP_ADMIN_OU', 'ou=ccoa,ou=ec2sa,ou=ba118,ou=users,dc=a,dc=c,dc=d,dc=fr');

// ---------------------------------------------------------------------------
// Mail SMTP
// ---------------------------------------------------------------------------
define('MAIL_HOST',      'smtp.exemple.fr');
define('MAIL_PORT',      587);          // 587 = STARTTLS, 465 = SSL
define('MAIL_USER',      'noreply@exemple.fr');
define('MAIL_PASS',      'ChangeMe');
define('MAIL_FROM',      'noreply@exemple.fr');
define('MAIL_FROM_NAME', 'TIR118');

// ---------------------------------------------------------------------------
// Application
// ---------------------------------------------------------------------------
define('APP_NAME', 'TIR118');
define('APP_URL',  'http://localhost/tir');   // Sans slash final

// Durée de session en secondes (2 heures)
define('SESSION_LIFETIME', 7200);

// Mettre à true uniquement en développement
define('APP_DEBUG', true);

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
