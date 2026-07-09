<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Raisons de tir';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('DELETE FROM raisons WHERE id = ?')->execute(array($id));
    flash_set('success', 'Raison supprimée.');
    redirect(APP_URL . '/admin/raisons.php');
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $libelle = trim(isset($_POST['libelle']) ? $_POST['libelle'] : '');
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if (empty($libelle)) {
        $error  = 'Le libellé est obligatoire.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        if ($edit_id > 0) {
            $pdo->prepare('UPDATE raisons SET libelle=? WHERE id=?')->execute(array($libelle, $edit_id));
            flash_set('success', 'Raison modifiée.');
        } else {
            $pdo->prepare('INSERT INTO raisons (libelle) VALUES (?)')->execute(array($libelle));
            flash_set('success', 'Raison ajoutée.');
        }
        redirect(APP_URL . '/admin/raisons.php');
    }
}

$edit_row = null;
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM raisons WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Raison introuvable.');
        redirect(APP_URL . '/admin/raisons.php');
    }
}

$raisons = $pdo->query('SELECT * FROM raisons ORDER BY libelle ASC')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-list mr-2 text-danger"></i>Raisons de tir</h4>
    <a href="?action=add" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter</a>
</div>

<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr><th>Libellé</th><th style="width:120px">Actions</th></tr>
        </thead>
        <tbody>
        <?php if ($raisons): ?>
        <?php foreach ($raisons as $r): ?>
        <tr>
            <td><?php echo h($r['libelle']); ?></td>
            <td>
                <a href="?action=edit&id=<?php echo (int)$r['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$r['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cette raison ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="2" class="text-muted">Aucune raison.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card">
    <div class="card-header bg-danger text-white">
        <?php echo $action === 'edit' ? 'Modifier la raison' : 'Nouvelle raison'; ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="?action=save">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-group">
                <label>Libellé *</label>
                <input type="text" name="libelle" class="form-control" required
                       value="<?php echo $edit_row ? h($edit_row['libelle']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo APP_URL; ?>/admin/raisons.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
