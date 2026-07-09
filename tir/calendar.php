<?php
require_once __DIR__ . '/includes/init.php';
require_auth();

$page_title = 'Calendrier';

$pdo = get_pdo();

// Récupérer toutes les catégories pour le filtre
$categories = $pdo->query('SELECT id, titre, couleur FROM categories_tir ORDER BY titre ASC')->fetchAll();

// Prochaine date disponible par catégorie (pour le panneau bas)
$next_dates = array();
foreach ($categories as $cat) {
    $stmt = $pdo->prepare(
        "SELECT t.id, t.date_debut, t.nb_places,
                (SELECT COUNT(*) FROM inscriptions i WHERE i.tir_id = t.id AND i.type = 'inscrit') AS nb_inscrits
         FROM tirs t
         WHERE t.categorie_tir_id = ? AND t.published = 1 AND t.date_debut >= NOW()
         ORDER BY t.date_debut ASC
         LIMIT 10"
    );
    $stmt->execute(array($cat['id']));
    $tirs = $stmt->fetchAll();
    foreach ($tirs as $tir) {
        if ((int)$tir['nb_places'] - (int)$tir['nb_inscrits'] > 0) {
            $next_dates[$cat['id']] = $tir;
            break;
        }
    }
}

$extra_head = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css">
';

$extra_scripts = '
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/fr.js"></script>
<script>
$(document).ready(function() {
    var categorieId = "";

    $("#categorieFilter").on("change", function() {
        categorieId = $(this).val();
        $("#calendar").fullCalendar("refetchEvents");
    });

    $("#calendar").fullCalendar({
        locale: "fr",
        header: {
            left:   "prev,next today",
            center: "title",
            right:  "month,agendaWeek"
        },
        events: function(start, end, timezone, callback) {
            $.getJSON("' . APP_URL . '/api/events.php", {
                start:        start.format(),
                end:          end.format(),
                categorie_id: categorieId
            }, function(data) {
                callback(data);
            });
        },
        eventClick: function(event) {
            if (event.url) {
                window.location.href = event.url;
                return false;
            }
        },
        eventRender: function(event, element) {
            element.attr("title",
                event.title + "\n" +
                "Places : " + event.places_restantes + "/" + event.nb_places
            );
            if (event.places_restantes <= 0) {
                element.addClass("event-complet");
            }
        },
        height: "auto",
        editable: false,
        eventLimit: true
    });
});
</script>
';

include __DIR__ . '/includes/header.php';
?>

<div class="row mb-3 align-items-center">
    <div class="col-md-4">
        <h4><i class="fas fa-calendar-alt mr-2 text-danger"></i>Calendrier des tirs</h4>
    </div>
    <div class="col-md-4">
        <select id="categorieFilter" class="form-control">
            <option value="">— Toutes les catégories —</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['id']; ?>"><?php echo h($cat['titre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div id="calendar"></div>

<!-- Panneau bas : prochaine date disponible par catégorie -->
<?php if ($categories): ?>
<div class="card mt-4">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-info-circle mr-1"></i>Prochaine place disponible par catégorie
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($categories as $cat): ?>
            <div class="col-md-4 mb-2">
                <?php if (isset($next_dates[$cat['id']])): ?>
                    <?php $nd = $next_dates[$cat['id']]; ?>
                    <a href="<?php echo APP_URL; ?>/calendar.php#<?php echo date('Y-m', strtotime($nd['date_debut'])); ?>"
                       class="btn btn-sm btn-block text-white"
                       style="background-color: <?php echo h($cat['couleur']); ?>; border-color: <?php echo h($cat['couleur']); ?>">
                        <strong><?php echo h($cat['titre']); ?></strong><br>
                        <small><?php echo fmt_datetime($nd['date_debut']); ?></small>
                    </a>
                <?php else: ?>
                    <div class="btn btn-sm btn-block btn-secondary disabled">
                        <?php echo h($cat['titre']); ?><br>
                        <small>Aucune place disponible</small>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
