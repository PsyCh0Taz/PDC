<?php
require_once __DIR__ . '/includes/init.php';
require_auth();

$page_title = 'Accueil';

$pdo      = get_pdo();
$articles = $pdo->query(
    "SELECT * FROM articles WHERE actif = 1 ORDER BY ordre ASC, id ASC"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if ($articles): ?>
<!-- Carousel d'articles -->
<div id="articleCarousel" class="carousel slide mb-4" data-ride="carousel" data-interval="6000">
    <ol class="carousel-indicators">
        <?php foreach ($articles as $i => $article): ?>
        <li data-target="#articleCarousel" data-slide-to="<?php echo $i; ?>"
            <?php echo $i === 0 ? 'class="active"' : ''; ?>></li>
        <?php endforeach; ?>
    </ol>

    <div class="carousel-inner">
        <?php foreach ($articles as $i => $article): ?>
        <div class="carousel-item<?php echo $i === 0 ? ' active' : ''; ?>">
            <div class="carousel-slide-content container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-body px-4 py-4">
                                <h3 class="card-title"><?php echo h($article['titre']); ?></h3>
                                <div class="card-text article-content">
                                    <?php
                                    // Le contenu est du HTML produit par TinyMCE, on l'affiche tel quel
                                    // (l'admin est de confiance ; à adapter si nécessaire)
                                    echo $article['contenu'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($articles) > 1): ?>
    <a class="carousel-control-prev" href="#articleCarousel" data-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#articleCarousel" data-slide="next">
        <span class="carousel-control-next-icon"></span>
    </a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="jumbotron">
    <h1 class="display-4"><i class="fas fa-crosshairs text-danger mr-2"></i><?php echo h(APP_NAME); ?></h1>
    <p class="lead">Bienvenue sur l'application de gestion des séances de tir.</p>
    <a class="btn btn-danger btn-lg" href="<?php echo APP_URL; ?>/calendar.php">
        <i class="fas fa-calendar-alt mr-1"></i>Voir le calendrier
    </a>
</div>
<?php endif; ?>

<!-- Raccourci vers le calendrier -->
<div class="text-center mt-3 mb-5">
    <a href="<?php echo APP_URL; ?>/calendar.php" class="btn btn-outline-danger btn-lg">
        <i class="fas fa-calendar-alt mr-1"></i>Accéder au calendrier des séances
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
