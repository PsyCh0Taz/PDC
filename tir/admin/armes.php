<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Gestion des armes';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ── SUPPRESSION ──────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // Supprimer l'image associée si elle existe
    $row = $pdo->prepare('SELECT image FROM armes WHERE id = ?');
    $row->execute(array($id));
    $row = $row->fetch();
    if ($row && $row['image'] && file_exists(dirname(__DIR__) . '/' . $row['image'])) {
        @unlink(dirname(__DIR__) . '/' . $row['image']);
    }
    $pdo->prepare('DELETE FROM armes WHERE id = ?')->execute(array($id));
    flash_set('success', 'Arme supprimée.');
    redirect(APP_URL . '/admin/armes.php');
}

// ── SAUVEGARDE (ajout / modification) ────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $libelle  = trim(isset($_POST['libelle']) ? $_POST['libelle'] : '');
    $edit_id  = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if (empty($libelle)) {
        $error  = 'Le libellé est obligatoire.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            $up = upload_image($_FILES['image'], 'armes');
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
                    // Supprimer l'ancienne image
                    $old = $pdo->prepare('SELECT image FROM armes WHERE id = ?');
                    $old->execute(array($edit_id));
                    $old = $old->fetchColumn();
                    if ($old && file_exists(dirname(__DIR__) . '/' . $old)) {
                        @unlink(dirname(__DIR__) . '/' . $old);
                    }
                    $pdo->prepare('UPDATE armes SET libelle=?, image=? WHERE id=?')
                        ->execute(array($libelle, $image_path, $edit_id));
                } else {
                    $pdo->prepare('UPDATE armes SET libelle=? WHERE id=?')
                        ->execute(array($libelle, $edit_id));
                }
                flash_set('success', 'Arme modifiée.');
            } else {
                $pdo->prepare('INSERT INTO armes (libelle, image) VALUES (?,?)')
                    ->execute(array($libelle, $image_path));
                flash_set('success', 'Arme ajoutée.');
            }
            redirect(APP_URL . '/admin/armes.php');
        }
    }
}

// ── DONNÉES POUR FORMULAIRE (ajout / édition) ─────────────────────────────────
$edit_row = null;
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM armes WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Arme introuvable.');
        redirect(APP_URL . '/admin/armes.php');
    }
}

// ── LISTE ─────────────────────────────────────────────────────────────────────
$armes = $pdo->query('SELECT * FROM armes ORDER BY libelle ASC')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-gun mr-2 text-danger"></i>Armes</h4>
    <a href="?action=add" class="btn btn-danger btn-sm">
        <i class="fas fa-plus mr-1"></i>Ajouter
    </a>
</div>

<!-- Tableau -->
<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr><th style="width:70px">Image</th><th>Libellé</th><th style="width:120px">Actions</th></tr>
        </thead>
        <tbody>
        <?php if ($armes): ?>
        <?php foreach ($armes as $a): ?>
        <tr>
            <td><?php echo img_tag($a['image'], h($a['libelle']), 'img-thumbnail'); ?></td>
            <td><?php echo h($a['libelle']); ?></td>
            <td>
                <a href="?action=edit&id=<?php echo (int)$a['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$a['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cette arme ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="3" class="text-muted">Aucune arme.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Formulaire ajout / édition -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-<?php echo $action === 'edit' ? 'pencil-alt' : 'plus'; ?> mr-1"></i>
        <?php echo $action === 'edit' ? 'Modifier l\'arme' : 'Nouvelle arme'; ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post" action="?action=save" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-group">
                <label>Libellé *</label>
                <input type="text" name="libelle" class="form-control" required
                       value="<?php echo $edit_row ? h($edit_row['libelle']) : (isset($_POST['libelle']) ? h($_POST['libelle']) : ''); ?>">
            </div>
            <div class="form-group">
                <label>Image</label>
                <?php if ($edit_row && $edit_row['image']): ?>
                <div class="mb-2"><?php echo img_tag($edit_row['image'], '', 'img-thumbnail'); ?></div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                <small class="form-text text-muted">JPG, PNG ou GIF — max 2 Mo. Laisser vide pour conserver l'image existante.</small>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save mr-1"></i>Enregistrer
            </button>
            <a href="<?php echo APP_URL; ?>/admin/armes.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
