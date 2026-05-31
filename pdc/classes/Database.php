<?php
// ============================================================
// PDC — Classe Database (singleton PDO)
// ============================================================

class Database {

    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Exécute une requête préparée et retourne le statement
     */
    public function query($sql, $params = array()) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retourne toutes les lignes
     */
    public function fetchAll($sql, $params = array()) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Retourne une seule ligne
     */
    public function fetchOne($sql, $params = array()) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Insert et retourne le dernier ID
     */
    public function insert($sql, $params = array()) {
        $this->query($sql, $params);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update/Delete, retourne le nombre de lignes affectées
     */
    public function execute($sql, $params = array()) {
        return $this->query($sql, $params)->rowCount();
    }
}