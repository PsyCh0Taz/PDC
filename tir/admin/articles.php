<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Articles';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ── SUPPRESSION ────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute(array($id));
    flash_set('success', 'Article supprimé.');
    redirect(APP_URL . '/admin/articles.php');
}

// ── TOGGLE ACTIF ───────────────────────────────────────────────────────────────
if ($action === 'toggle_actif' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('UPDATE articles SET actif = 1 - actif WHERE id = ?')->execute(array($id));
    flash_set('success', 'Statut mis à jour.');
    redirect(APP_URL . '/admin/articles.php');
}

// ── SAUVEGARDE ─────────────────────────────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $titre   = trim(isset($_POST['titre'])   ? $_POST['titre']   : '');
    $contenu = isset($_POST['contenu'])      ? $_POST['contenu'] : '';
    $ordre   = isset($_POST['ordre'])        ? (int)$_POST['ordre'] : 0;
    $actif   = isset($_POST['actif'])        ? 1 : 0;
    $edit_id = isset($_POST['edit_id'])      ? (int)$_POST['edit_id'] : 0;

    if (empty($titre)) {
        $error  = 'Le titre est obligatoire.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        if ($edit_id > 0) {
            $pdo->prepare(
                'UPDATE articles SET titre=?, contenu=?, ordre=?, actif=? WHERE id=?'
            )->execute(array($titre, $contenu, $ordre, $actif, $edit_id));
            flash_set('success', 'Article modifié.');
        } else {
            $pdo->prepare(
                'INSERT INTO articles (titre, contenu, ordre, actif) VALUES (?,?,?,?)'
            )->execute(array($titre, $contenu, $ordre, $actif));
            flash_set('success', 'Article ajouté.');
        }
        redirect(APP_URL . '/admin/articles.php');
    }
}

// ── DONNÉES FORMULAIRE ─────────────────────────────────────────────────────────
$edit_row = null;
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Article introuvable.');
        redirect(APP_URL . '/admin/articles.php');
    }
}

// ── LISTE ──────────────────────────────────────────────────────────────────────
$articles = $pdo->query('SELECT * FROM articles ORDER BY ordre ASC, id ASC')->fetchAll();

// TinyMCE CDN à charger uniquement sur la page d'édition
$extra_head    = '';
$extra_scripts = '';
if ($action === 'add' || $action === 'edit') {
    $extra_head = '<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>';
    $extra_scripts = '
<script>
tinymce.init({
    selector: "#contenu",
    language: "fr_FR",
    plugins: "lists link image code table wordcount",
    toolbar: "undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | '
            . 'bullist numlist | link image | table | code",
    height: 400,
    menubar: false,
    content_style: "body { font-family: sans-serif; font-size: 14px; }"
});
</script>';
}

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-newspaper mr-2 text-danger"></i>Articles</h4>
    <a href="?action=add" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter</a>
</div>

<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr><th>Ordre</th><th>Titre</th><th>Actif</th><th>Modifié</th><th style="width:130px">Actions</th></tr>
        </thead>
        <tbody>
        <?php if ($articles): ?>
        <?php foreach ($articles as $a): ?>
        <tr>
            <td><?php echo (int)$a['ordre']; ?></td>
            <td><?php echo h($a['titre']); ?></td>
            <td>
                <form method="post" action="?action=toggle_actif&id=<?php echo (int)$a['id']; ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs <?php echo $a['actif'] ? 'btn-success' : 'btn-secondary'; ?>">
                        <i class="fas fa-<?php echo $a['actif'] ? 'check' : 'times'; ?>"></i>
                    </button>
                </form>
            </td>
            <td><?php echo fmt_date($a['updated_at'], 'd/m/Y H:i'); ?></td>
            <td>
                <a href="?action=edit&id=<?php echo (int)$a['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$a['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cet article ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="5" class="text-muted">Aucun article.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card">
    <div class="card-header bg-danger text-white">
        <?php echo $action === 'edit' ? 'Modifier l\'article' : 'Nouvel article'; ?>
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="?action=save">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Titre *</label>
                    <input type="text" name="titre" class="form-control" required
                           value="<?php echo $edit_row ? h($edit_row['titre']) : ''; ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>Ordre</label>
                    <input type="number" name="ordre" class="form-control"
                           value="<?php echo $edit_row ? (int)$edit_row['ordre'] : 0; ?>">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="actif" name="actif" value="1"
                               <?php echo (!$edit_row || $edit_row['actif']) ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="actif">Actif</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Contenu</label>
                <textarea id="contenu" name="contenu" class="form-control" rows="10"><?php
                    echo $edit_row ? htmlspecialchars($edit_row['contenu'], ENT_QUOTES, 'UTF-8') : '';
                ?></textarea>
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo APP_URL; ?>/admin/articles.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3 small">
    <i class="fas fa-info-circle mr-1"></i>
    TinyMCE utilise <code>no-api-key</code> en mode démonstration.
    Remplacez par votre clé API sur <a href="https://www.tiny.cloud" target="_blank">tiny.cloud</a>
    pour supprimer l'avertissement.
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
