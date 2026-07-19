<?php
require __DIR__ . '/config/config.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
);

$stmt = $pdo->query('SHOW COLUMNS FROM pdc_domaines');
foreach ($stmt as $row) {
    echo $row['Field'] . PHP_EOL;
}
