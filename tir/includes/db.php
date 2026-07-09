<?php
/**
 * TIR118 - Connexion PDO MySQL
 */
function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ));
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die('<pre>Erreur DB : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>');
            }
            die('Erreur de connexion à la base de données. Vérifiez la configuration.');
        }
    }
    return $pdo;
}
