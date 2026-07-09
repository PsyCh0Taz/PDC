<?php
require_once __DIR__ . '/includes/init.php';
require_auth();

$page_title = 'Désinscription';
$pdo        = get_pdo();
$error      = null;
$success    = false;
$inscription = null;

// Hash passé dans l'URL ou saisi manuellement
$hash = isset($_GET['hash']) ? trim($_GET['hash']) : '';

// Traitement du formulaire de désinscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $hash = isset($_POST['hash']) ? trim($_POST['hash']) : '';
}

// Chercher l'inscription si on a un hash
if ($hash !== '') {
    // Valider le format du hash (64 caractères hexadécimaux)
    if (!preg_match('/^[0-9a-f]{64}$/', $hash)) {
        $error = 'Code de désinscription invalide.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT i.*, t.date_debut, t.date_fin, t.nb_places, t.categorie_tir_id,
                    ct.titre AS cat_titre, ct.couleur
             FROM inscriptions i
             JOIN tirs t  ON i.tir_id = t.id
             JOIN categories_tir ct ON t.categorie_tir_id = ct.id
             WHERE i.hash = ?"
        );
        $stmt->execute(array($hash));
        $inscription = $stmt->fetch();

        if (!$inscription) {
            $error = 'Code de désinscription introuvable ou déjà utilisé.';
        }
    }
}

// Confirmation de la désinscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $inscription && !$error) {
    // Supprimer l'inscription
    $pdo->prepare('DELETE FROM inscriptions WHERE hash = ?')->execute(array($hash));

    // Envoyer mail de confirmation
    $tir = array(
        'date_debut' => $inscription['date_debut'],
        'date_fin'   => $inscription['date_fin'],
    );
    $cat = array('titre' => $inscription['cat_titre']);
    mail_confirmation_desinscription($inscription, $tir, $cat);

    // Promouvoir le premier en liste d'attente si la place était confirmée
    if ($inscription['type'] === 'inscrit') {
        promouvoir_liste_attente((int)$inscription['tir_id']);
    }

    $success = true;
    $inscription = null;
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-6">

    <h4 class="mb-4"><i class="fas fa-user-minus text-danger mr-2"></i>Désinscription</h4>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-1"></i>
        Désinscription confirmée. Un e-mail de confirmation vous a été envoyé.
    </div>
    <a href="<?php echo APP_URL; ?>/calendar.php" class="btn btn-outline-danger">
        <i class="fas fa-calendar-alt mr-1"></i>Retour au calendrier
    </a>

    <?php elseif ($inscription): ?>
    <!-- Confirmation avant désinscription -->
    <div class="card mb-4" style="border-left: 5px solid <?php echo h($inscription['couleur']); ?>">
        <div class="card-body">
            <h5><?php echo h($inscription['cat_titre']); ?></h5>
            <dl class="row small mb-0">
                <dt class="col-sm-4">Tireur</dt>
                <dd class="col-sm-8"><?php echo h(trim($inscription['prenom'] . ' ' . $inscription['nom'])); ?></dd>
                <dt class="col-sm-4">Date</dt>
                <dd class="col-sm-8"><?php echo fmt_datetime($inscription['date_debut']); ?></dd>
                <dt class="col-sm-4">Statut</dt>
                <dd class="col-sm-8">
                    <?php echo $inscription['type'] === 'attente' ? '<span class="badge badge-warning">Liste d\'attente</span>' : '<span class="badge badge-success">Inscrit(e)</span>'; ?>
                </dd>
            </dl>
        </div>
    </div>

    <form method="post" action="">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="hash" value="<?php echo h($hash); ?>">
        <p>Confirmez-vous votre désinscription de cette séance ?</p>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-check mr-1"></i>Confirmer la désinscription
        </button>
        <a href="<?php echo APP_URL; ?>/calendar.php" class="btn btn-link">Annuler</a>
    </form>

    <?php else: ?>
    <!-- Formulaire de saisie du hash -->
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <p class="text-muted">Saisissez le code de désinscription reçu par e-mail lors de votre inscription.</p>
    <form method="post" action="">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="hash">Code de désinscription</label>
            <input type="text" id="hash" name="hash" class="form-control font-monospace"
                   placeholder="Code reçu par e-mail…" required
                   value="<?php echo h($hash); ?>">
        </div>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-search mr-1"></i>Rechercher
        </button>
    </form>
    <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
