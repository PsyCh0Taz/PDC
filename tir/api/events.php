<?php
/**
 * TIR118 - API JSON : événements pour FullCalendar
 */
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();

header('Content-Type: application/json; charset=UTF-8');

$pdo   = get_pdo();
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-t');
$cat   = isset($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : 0;

// Validation basique des dates
$start = preg_replace('/[^0-9\-T:+Z]/', '', $start);
$end   = preg_replace('/[^0-9\-T:+Z]/', '', $end);

$where   = 't.published = 1 AND t.date_debut BETWEEN ? AND ?';
$params  = array($start, $end);

if ($cat > 0) {
    $where  .= ' AND t.categorie_tir_id = ?';
    $params[] = $cat;
}

$sql = "SELECT t.id, t.date_debut, t.date_fin, t.nb_places,
               ct.titre, ct.couleur,
               (SELECT COUNT(*) FROM inscriptions i
                WHERE i.tir_id = t.id AND i.type = 'inscrit') AS nb_inscrits
        FROM tirs t
        JOIN categories_tir ct ON t.categorie_tir_id = ct.id
        WHERE $where
        ORDER BY t.date_debut ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tirs = $stmt->fetchAll();

$events = array();
foreach ($tirs as $tir) {
    $restantes = (int)$tir['nb_places'] - (int)$tir['nb_inscrits'];
    $titre     = $tir['titre'] . ' (' . $restantes . '/' . $tir['nb_places'] . ')';
    $complet   = $restantes <= 0;

    $events[] = array(
        'id'               => (int)$tir['id'],
        'title'            => $titre,
        'start'            => $tir['date_debut'],
        'end'              => $tir['date_fin'],
        'color'            => $complet ? '#6c757d' : $tir['couleur'],
        'url'              => APP_URL . '/reservation.php?tir_id=' . $tir['id'],
        'nb_places'        => (int)$tir['nb_places'],
        'places_restantes' => $restantes,
    );
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
