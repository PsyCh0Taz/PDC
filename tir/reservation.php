<?php
require_once __DIR__ . '/includes/init.php';
require_auth();

$page_title = 'Réservation';
$pdo        = get_pdo();
$error      = null;
$success    = false;

$tir_id = isset($_GET['tir_id']) ? (int)$_GET['tir_id'] : 0;

// Charger le tir
$stmt = $pdo->prepare(
    'SELECT t.*, ct.titre AS cat_titre, ct.couleur, ct.categorie_arme_id, ct.date_raison AS cat_date_raison,
            ct.id AS categorie_tir_id
     FROM tirs t
     JOIN categories_tir ct ON t.categorie_tir_id = ct.id
     WHERE t.id = ? AND t.published = 1'
);
$stmt->execute(array($tir_id));
$tir = $stmt->fetch();

if (!$tir) {
    flash_set('error', 'Séance introuvable ou non disponible.');
    redirect(APP_URL . '/calendar.php');
}

// Places restantes
$nb_inscrits   = nb_inscrits($tir_id);
$nb_attente_v  = nb_attente($tir_id);
$restantes     = places_restantes($tir_id, $tir['nb_places']);
$liste_attente = ($restantes <= 0);

// Raisons associées à la catégorie
$raisons = $pdo->prepare(
    'SELECT r.* FROM raisons r
     JOIN categories_tir_raisons ctr ON r.id = ctr.raison_id
     WHERE ctr.categorie_tir_id = ?
     ORDER BY r.libelle'
);
$raisons->execute(array($tir['categorie_tir_id']));
$raisons = $raisons->fetchAll();

// Armes de la catégorie d'arme associée
$armes = array();
if ($tir['categorie_arme_id']) {
    $stmt_armes = $pdo->prepare(
        'SELECT a.* FROM armes a
         JOIN categories_armes_armes caa ON a.id = caa.arme_id
         WHERE caa.categorie_id = ?
         ORDER BY a.libelle'
    );
    $stmt_armes->execute(array($tir['categorie_arme_id']));
    $armes = $stmt_armes->fetchAll();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nom         = trim(isset($_POST['nom'])         ? $_POST['nom']         : '');
    $prenom      = trim(isset($_POST['prenom'])      ? $_POST['prenom']      : '');
    $mail        = trim(isset($_POST['mail'])        ? $_POST['mail']        : '');
    $raison_id   = isset($_POST['raison_id'])        ? (int)$_POST['raison_id']   : null;
    $arme_id     = isset($_POST['arme_id'])          ? (int)$_POST['arme_id']     : null;
    $raison_date = isset($_POST['raison_date'])      ? trim($_POST['raison_date']) : null;

    // Validations
    if (empty($nom) || empty($prenom)) {
        $error = 'Nom et prénom sont obligatoires.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse e-mail invalide.';
    } elseif (empty($raisons) === false && !$raison_id) {
        $error = 'Veuillez sélectionner une raison.';
    } elseif (!empty($armes) && !$arme_id) {
        $error = 'Veuillez sélectionner une arme.';
    } else {
        // Vérifier si déjà inscrit (même mail + même tir)
        $check = $pdo->prepare(
            "SELECT id FROM inscriptions WHERE tir_id = ? AND mail = ?"
        );
        $check->execute(array($tir_id, $mail));
        if ($check->fetch()) {
            $error = 'Cette adresse e-mail est déjà inscrite à cette séance.';
        } else {
            // Re-vérifier places (entre validation et enregistrement)
            $nb_inscrits_now = nb_inscrits($tir_id);
            $type_ins = ($nb_inscrits_now >= (int)$tir['nb_places']) ? 'attente' : 'inscrit';

            $hash = generate_hash(32);

            $pdo->prepare(
                'INSERT INTO inscriptions
                 (tir_id, user_id, nom, prenom, mail, raison_id, arme_id, raison_date, type, hash)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute(array(
                $tir_id,
                current_user_id() ?: null,
                $nom,
                $prenom,
                $mail,
                $raison_id ?: null,
                $arme_id   ?: null,
                $raison_date ?: null,
                $type_ins,
                $hash,
            ));

            // Charger l'inscription pour le mail
            $ins_stmt = $pdo->prepare('SELECT * FROM inscriptions WHERE hash = ?');
            $ins_stmt->execute(array($hash));
            $inscription = $ins_stmt->fetch();

            $cat = array('titre' => $tir['cat_titre']);
            mail_confirmation_inscription($inscription, $tir, $cat);

            $success   = true;
            $type_ins_display = $type_ins;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-7">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/calendar.php">Calendrier</a></li>
            <li class="breadcrumb-item active">Réservation</li>
        </ol>
    </nav>

    <!-- Info séance -->
    <div class="card mb-4 border-left-colored" style="border-left: 5px solid <?php echo h($tir['couleur']); ?>">
        <div class="card-body">
            <h5 class="card-title"><?php echo h($tir['cat_titre']); ?></h5>
            <dl class="row mb-0 small">
                <dt class="col-sm-4">Début</dt>
                <dd class="col-sm-8"><?php echo fmt_datetime($tir['date_debut']); ?></dd>
                <dt class="col-sm-4">Fin</dt>
                <dd class="col-sm-8"><?php echo fmt_datetime($tir['date_fin']); ?></dd>
                <dt class="col-sm-4">Places</dt>
                <dd class="col-sm-8">
                    <?php echo $nb_inscrits; ?>/<?php echo (int)$tir['nb_places']; ?>
                    inscrits
                    <?php if ($nb_attente_v > 0): ?>
                    — <span class="text-warning"><?php echo $nb_attente_v; ?> en attente</span>
                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-1"></i>
        <?php if ($type_ins_display === 'attente'): ?>
            Vous avez été placé(e) en <strong>liste d'attente</strong>.
            Un e-mail de confirmation vous a été envoyé.
        <?php else: ?>
            <strong>Inscription confirmée !</strong>
            Un e-mail de confirmation avec un lien de désinscription vous a été envoyé.
        <?php endif; ?>
    </div>
    <a href="<?php echo APP_URL; ?>/calendar.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Retour au calendrier
    </a>
    <?php else: ?>

    <?php if ($liste_attente): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong>Séance complète.</strong> Vous serez inscrit(e) en liste d'attente.
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-user-plus mr-1"></i>
            <?php echo $liste_attente ? 'S\'inscrire en liste d\'attente' : 'Réserver une place'; ?>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required
                               value="<?php echo isset($_POST['prenom']) ? h($_POST['prenom']) : h($_SESSION['prenom']); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required
                               value="<?php echo isset($_POST['nom']) ? h($_POST['nom']) : h($_SESSION['nom']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="mail">E-mail *</label>
                    <input type="email" id="mail" name="mail" class="form-control" required
                           value="<?php echo isset($_POST['mail']) ? h($_POST['mail']) : h($_SESSION['mail']); ?>">
                </div>

                <?php if ($raisons): ?>
                <div class="form-group">
                    <label for="raison_id">Raison *</label>
                    <select id="raison_id" name="raison_id" class="form-control" required>
                        <option value="">— Sélectionnez —</option>
                        <?php foreach ($raisons as $r): ?>
                        <option value="<?php echo (int)$r['id']; ?>"
                            <?php echo (isset($_POST['raison_id']) && (int)$_POST['raison_id'] === (int)$r['id']) ? 'selected' : ''; ?>>
                            <?php echo h($r['libelle']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="raison_date">Date de la raison</label>
                    <input type="date" id="raison_date" name="raison_date" class="form-control"
                           value="<?php echo isset($_POST['raison_date']) ? h($_POST['raison_date']) : h($tir['cat_date_raison']); ?>">
                </div>
                <?php endif; ?>

                <?php if ($armes): ?>
                <div class="form-group">
                    <label for="arme_id">Arme *</label>
                    <select id="arme_id" name="arme_id" class="form-control" required>
                        <option value="">— Sélectionnez une arme —</option>
                        <?php foreach ($armes as $a): ?>
                        <option value="<?php echo (int)$a['id']; ?>"
                            <?php echo (isset($_POST['arme_id']) && (int)$_POST['arme_id'] === (int)$a['id']) ? 'selected' : ''; ?>>
                            <?php echo h($a['libelle']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-check mr-1"></i>
                    <?php echo $liste_attente ? 'M\'inscrire en liste d\'attente' : 'Confirmer la réservation'; ?>
                </button>
                <a href="<?php echo APP_URL; ?>/calendar.php" class="btn btn-link">Annuler</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
