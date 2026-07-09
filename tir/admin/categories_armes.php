<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Catégories d\'armes';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ── SUPPRESSION ───────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $row = $pdo->prepare('SELECT image FROM categories_armes WHERE id = ?');
    $row->execute(array($id));
    $row = $row->fetch();
    if ($row && $row['image'] && file_exists(dirname(__DIR__) . '/' . $row['image'])) {
        @unlink(dirname(__DIR__) . '/' . $row['image']);
    }
    $pdo->prepare('DELETE FROM categories_armes WHERE id = ?')->execute(array($id));
    flash_set('success', 'Catégorie supprimée.');
    redirect(APP_URL . '/admin/categories_armes.php');
}

// ── RETIRER UNE ARME D'UNE CATÉGORIE ─────────────────────────────────────────
if ($action === 'remove_arme' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $arme_id = isset($_POST['arme_id']) ? (int)$_POST['arme_id'] : 0;
    $pdo->prepare('DELETE FROM categories_armes_armes WHERE categorie_id = ? AND arme_id = ?')
        ->execute(array($id, $arme_id));
    flash_set('success', 'Arme retirée de la catégorie.');
    redirect(APP_URL . '/admin/categories_armes.php?action=edit&id=' . $id);
}

// ── AJOUTER UNE ARME À UNE CATÉGORIE ─────────────────────────────────────────
if ($action === 'add_arme' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $arme_id = isset($_POST['arme_id']) ? (int)$_POST['arme_id'] : 0;
    if ($arme_id > 0) {
        // Ignore si déjà présent (clé primaire)
        try {
            $pdo->prepare('INSERT INTO categories_armes_armes (categorie_id, arme_id) VALUES (?,?)')
                ->execute(array($id, $arme_id));
            flash_set('success', 'Arme ajoutée à la catégorie.');
        } catch (PDOException $e) {
            flash_set('warning', 'Cette arme est déjà dans la catégorie.');
        }
    }
    redirect(APP_URL . '/admin/categories_armes.php?action=edit&id=' . $id);
}

// ── SAUVEGARDE ────────────────────────────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $titre   = trim(isset($_POST['titre']) ? $_POST['titre'] : '');
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if (empty($titre)) {
        $error  = 'Le titre est obligatoire.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            $up = upload_image($_FILES['image'], 'categories');
            if (!$up['success']) {
                $error  = $up['error'];
                $action = $edit_id ? 'edit' : 'add';
                $id     = $edit_id;
            } else {
                $image_path = $up['path'];
            }
        }

        if (!$error) {
            if ($edit_id > 0) {
                if ($image_path) {
                    $old = $pdo->prepare('SELECT image FROM categories_armes WHERE id = ?');
                    $old->execute(array($edit_id));
                    $old = $old->fetchColumn();
                    if ($old && file_exists(dirname(__DIR__) . '/' . $old)) {
                        @unlink(dirname(__DIR__) . '/' . $old);
                    }
                    $pdo->prepare('UPDATE categories_armes SET titre=?, image=? WHERE id=?')
                        ->execute(array($titre, $image_path, $edit_id));
                } else {
                    $pdo->prepare('UPDATE categories_armes SET titre=? WHERE id=?')
                        ->execute(array($titre, $edit_id));
                }
                flash_set('success', 'Catégorie modifiée.');
            } else {
                $pdo->prepare('INSERT INTO categories_armes (titre, image) VALUES (?,?)')
                    ->execute(array($titre, $image_path));
                flash_set('success', 'Catégorie ajoutée.');
            }
            redirect(APP_URL . '/admin/categories_armes.php');
        }
    }
}

// ── DONNÉES POUR FORMULAIRE ────────────────────────────────────────────────────
$edit_row = null;
$cat_armes = array();
$all_armes = array();
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM categories_armes WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Catégorie introuvable.');
        redirect(APP_URL . '/admin/categories_armes.php');
    }
    $s2 = $pdo->prepare(
        'SELECT a.* FROM armes a JOIN categories_armes_armes caa ON a.id = caa.arme_id
         WHERE caa.categorie_id = ? ORDER BY a.libelle'
    );
    $s2->execute(array($id));
    $cat_armes = $s2->fetchAll();

    $cat_ids = array();
    foreach ($cat_armes as $ca) {
        $cat_ids[] = (int)$ca['id'];
    }
    $all_armes = $pdo->query('SELECT * FROM armes ORDER BY libelle')->fetchAll();
}

// ── LISTE ──────────────────────────────────────────────────────────────────────
$cats = $pdo->query('SELECT ca.*, (SELECT COUNT(*) FROM categories_armes_armes WHERE categorie_id = ca.id) AS nb_armes FROM categories_armes ca ORDER BY titre ASC')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-tags mr-2 text-danger"></i>Catégories d'armes</h4>
    <a href="?action=add" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter</a>
</div>

<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr><th style="width:70px">Image</th><th>Titre</th><th>Nb armes</th><th style="width:120px">Actions</th></tr>
        </thead>
        <tbody>
        <?php if ($cats): ?>
        <?php foreach ($cats as $c): ?>
        <tr>
            <td><?php echo img_tag($c['image'], h($c['titre']), 'img-thumbnail'); ?></td>
            <td><?php echo h($c['titre']); ?></td>
            <td><?php echo (int)$c['nb_armes']; ?></td>
            <td>
                <a href="?action=edit&id=<?php echo (int)$c['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$c['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="4" class="text-muted">Aucune catégorie.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <?php echo $action === 'edit' ? 'Modifier la catégorie' : 'Nouvelle catégorie'; ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="?action=save" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="titre" class="form-control" required
                       value="<?php echo $edit_row ? h($edit_row['titre']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Image</label>
                <?php if ($edit_row && $edit_row['image']): ?>
                <div class="mb-2"><?php echo img_tag($edit_row['image'], '', 'img-thumbnail'); ?></div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                <small class="form-text text-muted">Laisser vide pour conserver l'image existante.</small>
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo APP_URL; ?>/admin/categories_armes.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>

<?php if ($action === 'edit' && $edit_row): ?>
<!-- Gestion des armes de la catégorie -->
<div class="card">
    <div class="card-header">
        <strong>Armes dans cette catégorie</strong>
    </div>
    <div class="card-body">
        <?php if ($cat_armes): ?>
        <table class="table table-sm table-bordered mb-3">
            <thead class="thead-light"><tr><th>Arme</th><th style="width:80px">Retirer</th></tr></thead>
            <tbody>
            <?php foreach ($cat_armes as $ca): ?>
            <tr>
                <td><?php echo h($ca['libelle']); ?></td>
                <td>
                    <form method="post" action="?action=remove_arme&id=<?php echo (int)$id; ?>"
                          onsubmit="return confirm('Retirer cette arme ?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="arme_id" value="<?php echo (int)$ca['id']; ?>">
                        <button type="submit" class="btn btn-xs btn-outline-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted small">Aucune arme dans cette catégorie.</p>
        <?php endif; ?>

        <!-- Ajouter une arme -->
        <form method="post" action="?action=add_arme&id=<?php echo (int)$id; ?>" class="form-inline">
            <?php echo csrf_field(); ?>
            <select name="arme_id" class="form-control form-control-sm mr-2" required>
                <option value="">— Choisir une arme —</option>
                <?php foreach ($all_armes as $a): ?>
                <?php if (!in_array((int)$a['id'], $cat_ids)): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo h($a['libelle']); ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i>Ajouter
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
