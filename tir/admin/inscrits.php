<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Inscrits à la séance';
$pdo        = get_pdo();
$error      = null;

$tir_id = isset($_GET['tir_id']) ? (int)$_GET['tir_id'] : 0;
if (!$tir_id) {
    redirect(APP_URL . '/admin/tirs.php');
}

// Charger la séance
$stmt = $pdo->prepare(
    'SELECT t.*, ct.titre AS cat_titre, ct.couleur, ct.categorie_arme_id
     FROM tirs t JOIN categories_tir ct ON t.categorie_tir_id = ct.id
     WHERE t.id = ?'
);
$stmt->execute(array($tir_id));
$tir = $stmt->fetch();
if (!$tir) {
    flash_set('error', 'Séance introuvable.');
    redirect(APP_URL . '/admin/tirs.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$ins_id = isset($_GET['ins_id']) ? (int)$_GET['ins_id'] : 0;

// ── SUPPRIMER UN INSCRIT ───────────────────────────────────────────────────────
if ($action === 'delete' && $ins_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $ins_stmt = $pdo->prepare('SELECT * FROM inscriptions WHERE id = ? AND tir_id = ?');
    $ins_stmt->execute(array($ins_id, $tir_id));
    $inscription = $ins_stmt->fetch();

    if ($inscription) {
        $pdo->prepare('DELETE FROM inscriptions WHERE id = ?')->execute(array($ins_id));

        $send_mail = isset($_POST['send_mail']);
        if ($send_mail) {
            mail_confirmation_desinscription($inscription, $tir, array('titre' => $tir['cat_titre']));
        }

        if ($inscription['type'] === 'inscrit') {
            promouvoir_liste_attente($tir_id);
        }
        flash_set('success', 'Inscription supprimée.' . ($send_mail ? ' E-mail envoyé.' : ''));
    }
    redirect(APP_URL . '/admin/inscrits.php?tir_id=' . $tir_id);
}

// ── METTRE À JOUR LE STATUT ────────────────────────────────────────────────────
if ($action === 'statut' && $ins_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $statut = isset($_POST['statut']) ? $_POST['statut'] : 'inscrit';
    $allowed_statuts = array('inscrit', 'present', 'absent', 'no_safe');
    if (!in_array($statut, $allowed_statuts)) {
        $statut = 'inscrit';
    }
    $pdo->prepare('UPDATE inscriptions SET statut = ? WHERE id = ? AND tir_id = ?')
        ->execute(array($statut, $ins_id, $tir_id));
    flash_set('success', 'Statut mis à jour.');
    redirect(APP_URL . '/admin/inscrits.php?tir_id=' . $tir_id);
}

// ── AJOUTER UN INSCRIT MANUELLEMENT ────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nom         = trim(isset($_POST['nom'])         ? $_POST['nom']         : '');
    $prenom      = trim(isset($_POST['prenom'])      ? $_POST['prenom']      : '');
    $mail        = trim(isset($_POST['mail'])        ? $_POST['mail']        : '');
    $raison_id   = isset($_POST['raison_id'])        ? (int)$_POST['raison_id']   : null;
    $arme_id     = isset($_POST['arme_id'])          ? (int)$_POST['arme_id']     : null;
    $type_ins    = isset($_POST['type'])             ? $_POST['type']             : 'inscrit';
    $send_mail   = isset($_POST['send_mail']);

    if (empty($nom) || empty($prenom) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error  = 'Nom, prénom et e-mail valide sont requis.';
    } else {
        $check = $pdo->prepare('SELECT id FROM inscriptions WHERE tir_id = ? AND mail = ?');
        $check->execute(array($tir_id, $mail));
        if ($check->fetch()) {
            $error = 'Cet e-mail est déjà inscrit à cette séance.';
        } else {
            $allowed_types = array('inscrit', 'attente');
            if (!in_array($type_ins, $allowed_types)) {
                $type_ins = 'inscrit';
            }
            $hash = generate_hash(32);
            $pdo->prepare(
                'INSERT INTO inscriptions (tir_id, nom, prenom, mail, raison_id, arme_id, type, hash)
                 VALUES (?,?,?,?,?,?,?,?)'
            )->execute(array(
                $tir_id, $nom, $prenom, $mail,
                $raison_id ?: null,
                $arme_id   ?: null,
                $type_ins, $hash,
            ));

            if ($send_mail) {
                $ins_stmt = $pdo->prepare('SELECT * FROM inscriptions WHERE hash = ?');
                $ins_stmt->execute(array($hash));
                $inscription = $ins_stmt->fetch();
                mail_confirmation_inscription($inscription, $tir, array('titre' => $tir['cat_titre']));
            }
            flash_set('success', 'Inscrit ajouté.' . ($send_mail ? ' E-mail envoyé.' : ''));
            redirect(APP_URL . '/admin/inscrits.php?tir_id=' . $tir_id);
        }
    }
}

// ── DONNÉES ────────────────────────────────────────────────────────────────────
$inscrits = $pdo->prepare(
    "SELECT i.*, r.libelle AS raison_libelle, a.libelle AS arme_libelle
     FROM inscriptions i
     LEFT JOIN raisons r ON i.raison_id = r.id
     LEFT JOIN armes a   ON i.arme_id   = a.id
     WHERE i.tir_id = ?
     ORDER BY i.type ASC, i.created_at ASC"
);
$inscrits->execute(array($tir_id));
$inscrits = $inscrits->fetchAll();

// Raisons et armes pour le formulaire d'ajout
$raisons = $pdo->prepare(
    'SELECT r.* FROM raisons r
     JOIN categories_tir_raisons ctr ON r.id = ctr.raison_id
     WHERE ctr.categorie_tir_id = ? ORDER BY r.libelle'
);
$raisons->execute(array($tir['categorie_tir_id']));
$raisons = $raisons->fetchAll();

$armes = array();
if ($tir['categorie_arme_id']) {
    $stmt_armes = $pdo->prepare(
        'SELECT a.* FROM armes a
         JOIN categories_armes_armes caa ON a.id = caa.arme_id
         WHERE caa.categorie_id = ? ORDER BY a.libelle'
    );
    $stmt_armes->execute(array($tir['categorie_arme_id']));
    $armes = $stmt_armes->fetchAll();
}

$nb_ins_total = nb_inscrits($tir_id);

include __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/admin/tirs.php">Séances</a></li>
        <li class="breadcrumb-item active">Inscrits</li>
    </ol>
</nav>

<!-- Info séance -->
<div class="card mb-4" style="border-left: 5px solid <?php echo h($tir['couleur']); ?>">
    <div class="card-body py-2">
        <strong><?php echo h($tir['cat_titre']); ?></strong>
        — <?php echo fmt_datetime($tir['date_debut']); ?> → <?php echo fmt_datetime($tir['date_fin']); ?>
        <span class="badge badge-<?php echo $nb_ins_total >= (int)$tir['nb_places'] ? 'danger' : 'success'; ?> ml-2">
            <?php echo $nb_ins_total; ?>/<?php echo (int)$tir['nb_places']; ?> places
        </span>
    </div>
</div>

<!-- Tableau des inscrits -->
<div class="table-responsive mb-4">
    <table class="table table-hover table-sm" id="tableInscrits">
        <thead class="thead-dark">
            <tr>
                <th>Nom</th><th>Prénom</th><th>Mail</th>
                <th>Arme</th><th>Raison</th>
                <th>Type</th><th>Statut</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($inscrits): ?>
        <?php foreach ($inscrits as $ins): ?>
        <tr class="<?php echo $ins['type'] === 'attente' ? 'table-warning' : ''; ?>">
            <td><?php echo h($ins['nom']); ?></td>
            <td><?php echo h($ins['prenom']); ?></td>
            <td><a href="mailto:<?php echo h($ins['mail']); ?>"><?php echo h($ins['mail']); ?></a></td>
            <td><?php echo $ins['arme_libelle'] ? h($ins['arme_libelle']) : '—'; ?></td>
            <td><?php echo $ins['raison_libelle'] ? h($ins['raison_libelle']) : '—'; ?></td>
            <td>
                <?php if ($ins['type'] === 'attente'): ?>
                <span class="badge badge-warning">Attente</span>
                <?php else: ?>
                <span class="badge badge-success">Inscrit</span>
                <?php endif; ?>
            </td>
            <td>
                <form method="post"
                      action="?action=statut&tir_id=<?php echo $tir_id; ?>&ins_id=<?php echo (int)$ins['id']; ?>"
                      class="form-inline">
                    <?php echo csrf_field(); ?>
                    <select name="statut" class="form-control form-control-sm mr-1" style="width:auto"
                            onchange="this.form.submit()">
                        <?php foreach (array('inscrit'=>'Inscrit','present'=>'Présent','absent'=>'Absent','no_safe'=>'No Safe') as $val => $lbl): ?>
                        <option value="<?php echo $val; ?>" <?php echo $ins['statut'] === $val ? 'selected' : ''; ?>>
                            <?php echo $lbl; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <td>
                <button type="button" class="btn btn-xs btn-outline-danger"
                        data-toggle="modal" data-target="#modalDelete"
                        data-ins-id="<?php echo (int)$ins['id']; ?>"
                        data-ins-nom="<?php echo h($ins['prenom'] . ' ' . $ins['nom']); ?>">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="8" class="text-muted">Aucun inscrit.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Formulaire ajout manuel -->
<div class="card">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-user-plus mr-1"></i>Ajouter un inscrit manuellement
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="?action=add&tir_id=<?php echo $tir_id; ?>">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" class="form-control form-control-sm" required
                           value="<?php echo isset($_POST['prenom']) ? h($_POST['prenom']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Nom *</label>
                    <input type="text" name="nom" class="form-control form-control-sm" required
                           value="<?php echo isset($_POST['nom']) ? h($_POST['nom']) : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>E-mail *</label>
                    <input type="email" name="mail" class="form-control form-control-sm" required
                           value="<?php echo isset($_POST['mail']) ? h($_POST['mail']) : ''; ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="inscrit">Inscrit</option>
                        <option value="attente">Attente</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <?php if ($raisons): ?>
                <div class="form-group col-md-4">
                    <label>Raison</label>
                    <select name="raison_id" class="form-control form-control-sm">
                        <option value="">—</option>
                        <?php foreach ($raisons as $r): ?>
                        <option value="<?php echo (int)$r['id']; ?>"><?php echo h($r['libelle']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if ($armes): ?>
                <div class="form-group col-md-4">
                    <label>Arme</label>
                    <select name="arme_id" class="form-control form-control-sm">
                        <option value="">—</option>
                        <?php foreach ($armes as $a): ?>
                        <option value="<?php echo (int)$a['id']; ?>"><?php echo h($a['libelle']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group col-md-4 d-flex align-items-end">
                    <div class="custom-control custom-checkbox mb-2 mr-3">
                        <input type="checkbox" class="custom-control-input" id="chkMail" name="send_mail" value="1" checked>
                        <label class="custom-control-label" for="chkMail">Envoyer un mail</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-danger mb-2">
                        <i class="fas fa-plus mr-1"></i>Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal confirmation suppression -->
<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Supprimer l'inscription de <strong id="modalNom"></strong> ?</p>
                <form method="post" id="formDelete" action="">
                    <?php echo csrf_field(); ?>
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="chkMailDel" name="send_mail" value="1" checked>
                        <label class="custom-control-label" for="chkMailDel">Envoyer un mail de désinscription</label>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extra_scripts = '
<script>
$("#modalDelete").on("show.bs.modal", function(e) {
    var btn    = $(e.relatedTarget);
    var insId  = btn.data("ins-id");
    var insNom = btn.data("ins-nom");
    $("#modalNom").text(insNom);
    $("#formDelete").attr("action", "?action=delete&tir_id=' . $tir_id . '&ins_id=" + insId);
});
</script>
';
?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
