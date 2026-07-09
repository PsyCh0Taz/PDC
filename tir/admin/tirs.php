<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Séances de tir';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ── SUPPRESSION ────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('DELETE FROM tirs WHERE id = ?')->execute(array($id));
    flash_set('success', 'Séance supprimée.');
    redirect(APP_URL . '/admin/tirs.php');
}

// ── TOGGLE PUBLICATION ─────────────────────────────────────────────────────────
if ($action === 'toggle_publish' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('UPDATE tirs SET published = 1 - published WHERE id = ?')->execute(array($id));
    flash_set('success', 'Statut de publication mis à jour.');
    redirect(APP_URL . '/admin/tirs.php');
}

// ── VALIDATION ─────────────────────────────────────────────────────────────────
if ($action === 'toggle_valide' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('UPDATE tirs SET valide = 1 - valide WHERE id = ?')->execute(array($id));
    flash_set('success', 'Statut de validation mis à jour.');
    redirect(APP_URL . '/admin/tirs.php');
}

// ── SAUVEGARDE ─────────────────────────────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $cat_id       = isset($_POST['categorie_tir_id']) ? (int)$_POST['categorie_tir_id']     : 0;
    $date_debut   = isset($_POST['date_debut'])       ? trim($_POST['date_debut'])           : '';
    $date_fin     = isset($_POST['date_fin'])         ? trim($_POST['date_fin'])             : '';
    $nb_places    = isset($_POST['nb_places'])        ? (int)$_POST['nb_places']            : 10;
    $published    = isset($_POST['published'])        ? 1                                    : 0;
    $edit_id      = isset($_POST['edit_id'])          ? (int)$_POST['edit_id']              : 0;

    if (!$cat_id || empty($date_debut) || empty($date_fin)) {
        $error  = 'Catégorie, date de début et date de fin sont obligatoires.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } elseif ($nb_places < 1) {
        $error  = 'Le nombre de places doit être supérieur à 0.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        $data = array($cat_id, $date_debut, $date_fin, $nb_places, $published);
        if ($edit_id > 0) {
            $data[] = $edit_id;
            $pdo->prepare(
                'UPDATE tirs SET categorie_tir_id=?, date_debut=?, date_fin=?, nb_places=?, published=? WHERE id=?'
            )->execute($data);
            flash_set('success', 'Séance modifiée.');
        } else {
            $pdo->prepare(
                'INSERT INTO tirs (categorie_tir_id, date_debut, date_fin, nb_places, published) VALUES (?,?,?,?,?)'
            )->execute($data);
            flash_set('success', 'Séance ajoutée.');
        }
        redirect(APP_URL . '/admin/tirs.php');
    }
}

// ── DONNÉES FORMULAIRE ─────────────────────────────────────────────────────────
$edit_row = null;
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM tirs WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Séance introuvable.');
        redirect(APP_URL . '/admin/tirs.php');
    }
}

$categories_tir = $pdo->query('SELECT id, titre, couleur FROM categories_tir ORDER BY titre')->fetchAll();

// ── LISTE ──────────────────────────────────────────────────────────────────────
$tirs = $pdo->query(
    "SELECT t.*, ct.titre AS cat_titre, ct.couleur,
            (SELECT COUNT(*) FROM inscriptions i WHERE i.tir_id = t.id AND i.type = 'inscrit') AS nb_ins,
            (SELECT COUNT(*) FROM inscriptions i WHERE i.tir_id = t.id AND i.type = 'attente') AS nb_att
     FROM tirs t
     JOIN categories_tir ct ON t.categorie_tir_id = ct.id
     ORDER BY t.date_debut DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-bullseye mr-2 text-danger"></i>Séances de tir</h4>
    <a href="?action=add" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter</a>
</div>

<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Catégorie</th><th>Début</th><th>Fin</th>
                <th>Places</th><th>Attente</th><th>Publié</th><th>Validé</th>
                <th style="width:160px">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($tirs): ?>
        <?php foreach ($tirs as $t): ?>
        <tr>
            <td>
                <span class="badge" style="background-color:<?php echo h($t['couleur']); ?>; color:#fff">
                    <?php echo h($t['cat_titre']); ?>
                </span>
            </td>
            <td><?php echo fmt_datetime($t['date_debut']); ?></td>
            <td><?php echo fmt_datetime($t['date_fin']); ?></td>
            <td><?php echo (int)$t['nb_ins']; ?>/<?php echo (int)$t['nb_places']; ?></td>
            <td>
                <?php if ((int)$t['nb_att'] > 0): ?>
                <span class="badge badge-warning"><?php echo (int)$t['nb_att']; ?></span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <form method="post" action="?action=toggle_publish&id=<?php echo (int)$t['id']; ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs <?php echo $t['published'] ? 'btn-success' : 'btn-secondary'; ?>"
                            title="<?php echo $t['published'] ? 'Dépublier' : 'Publier'; ?>">
                        <i class="fas fa-<?php echo $t['published'] ? 'eye' : 'eye-slash'; ?>"></i>
                    </button>
                </form>
            </td>
            <td>
                <form method="post" action="?action=toggle_valide&id=<?php echo (int)$t['id']; ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs <?php echo $t['valide'] ? 'btn-success' : 'btn-outline-secondary'; ?>"
                            title="<?php echo $t['valide'] ? 'Dévalider' : 'Valider'; ?>">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            </td>
            <td>
                <a href="<?php echo APP_URL; ?>/admin/inscrits.php?tir_id=<?php echo (int)$t['id']; ?>"
                   class="btn btn-xs btn-outline-info" title="Inscrits">
                    <i class="fas fa-users"></i>
                </a>
                <a href="?action=edit&id=<?php echo (int)$t['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$t['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cette séance et toutes ses inscriptions ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="8" class="text-muted">Aucune séance.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card">
    <div class="card-header bg-danger text-white">
        <?php echo $action === 'edit' ? 'Modifier la séance' : 'Nouvelle séance'; ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="?action=save">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-group">
                <label>Catégorie de tir *</label>
                <select name="categorie_tir_id" class="form-control" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($categories_tir as $ct): ?>
                    <option value="<?php echo (int)$ct['id']; ?>"
                        <?php echo ($edit_row && (int)$edit_row['categorie_tir_id'] === (int)$ct['id']) ? 'selected' : ''; ?>>
                        <?php echo h($ct['titre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Date/heure de début *</label>
                    <input type="datetime-local" name="date_debut" class="form-control" required
                           value="<?php echo $edit_row ? date('Y-m-d\TH:i', strtotime($edit_row['date_debut'])) : ''; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Date/heure de fin *</label>
                    <input type="datetime-local" name="date_fin" class="form-control" required
                           value="<?php echo $edit_row ? date('Y-m-d\TH:i', strtotime($edit_row['date_fin'])) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Nombre de places *</label>
                    <input type="number" name="nb_places" class="form-control" min="1" required
                           value="<?php echo $edit_row ? (int)$edit_row['nb_places'] : 10; ?>">
                </div>
                <div class="form-group col-md-4 d-flex align-items-end">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="published" name="published" value="1"
                               <?php echo ($edit_row && $edit_row['published']) ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="published">Publier immédiatement</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo APP_URL; ?>/admin/tirs.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
