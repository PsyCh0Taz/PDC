<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Administration';
$pdo        = get_pdo();

$nb_tirs        = $pdo->query("SELECT COUNT(*) FROM tirs")->fetchColumn();
$nb_inscrits    = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE type = 'inscrit'")->fetchColumn();
$nb_attente     = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE type = 'attente'")->fetchColumn();
$nb_users       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Prochains tirs
$prochains = $pdo->query(
    "SELECT t.id, t.date_debut, t.nb_places, ct.titre, ct.couleur,
            (SELECT COUNT(*) FROM inscriptions i WHERE i.tir_id = t.id AND i.type = 'inscrit') AS nb_ins
     FROM tirs t
     JOIN categories_tir ct ON t.categorie_tir_id = ct.id
     WHERE t.date_debut >= NOW() AND t.published = 1
     ORDER BY t.date_debut ASC LIMIT 5"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="fas fa-tachometer-alt mr-2 text-danger"></i>Tableau de bord</h4>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body text-center">
                <div class="display-4"><?php echo (int)$nb_tirs; ?></div>
                <div>Séances</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <div class="display-4"><?php echo (int)$nb_inscrits; ?></div>
                <div>Inscrits</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <div class="display-4"><?php echo (int)$nb_attente; ?></div>
                <div>En attente</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <div class="display-4"><?php echo (int)$nb_users; ?></div>
                <div>Utilisateurs</div>
            </div>
        </div>
    </div>
</div>

<h5>Prochaines séances publiées</h5>
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Catégorie</th>
                <th>Date</th>
                <th>Places</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($prochains): ?>
        <?php foreach ($prochains as $t): ?>
        <tr>
            <td>
                <span class="badge" style="background-color:<?php echo h($t['couleur']); ?>; color:#fff">
                    <?php echo h($t['titre']); ?>
                </span>
            </td>
            <td><?php echo fmt_datetime($t['date_debut']); ?></td>
            <td><?php echo (int)$t['nb_ins']; ?>/<?php echo (int)$t['nb_places']; ?></td>
            <td>
                <a href="<?php echo APP_URL; ?>/admin/inscrits.php?tir_id=<?php echo (int)$t['id']; ?>"
                   class="btn btn-xs btn-outline-danger">
                    <i class="fas fa-users"></i> Inscrits
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="4" class="text-muted">Aucune séance à venir.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
