<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$page_title = 'Catégories de tir';
$pdo        = get_pdo();
$error      = null;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ── SUPPRESSION ────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('DELETE FROM categories_tir WHERE id = ?')->execute(array($id));
    flash_set('success', 'Catégorie de tir supprimée.');
    redirect(APP_URL . '/admin/categories_tir.php');
}

// ── GESTION DES RAISONS ASSOCIÉES ─────────────────────────────────────────────
if ($action === 'add_raison' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $raison_id = isset($_POST['raison_id']) ? (int)$_POST['raison_id'] : 0;
    if ($raison_id > 0) {
        try {
            $pdo->prepare('INSERT INTO categories_tir_raisons (categorie_tir_id, raison_id) VALUES (?,?)')
                ->execute(array($id, $raison_id));
        } catch (PDOException $e) {
            // déjà présent
        }
    }
    redirect(APP_URL . '/admin/categories_tir.php?action=edit&id=' . $id);
}

if ($action === 'remove_raison' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $raison_id = isset($_POST['raison_id']) ? (int)$_POST['raison_id'] : 0;
    $pdo->prepare('DELETE FROM categories_tir_raisons WHERE categorie_tir_id = ? AND raison_id = ?')
        ->execute(array($id, $raison_id));
    redirect(APP_URL . '/admin/categories_tir.php?action=edit&id=' . $id);
}

// ── SAUVEGARDE ─────────────────────────────────────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $titre          = trim(isset($_POST['titre'])          ? $_POST['titre']          : '');
    $icone          = trim(isset($_POST['icone'])          ? $_POST['icone']          : '');
    $couleur        = trim(isset($_POST['couleur'])        ? $_POST['couleur']        : '#3498db');
    $cat_arme_id    = isset($_POST['categorie_arme_id'])   ? (int)$_POST['categorie_arme_id']   : null;
    $date_raison    = isset($_POST['date_raison'])         ? trim($_POST['date_raison'])         : null;
    $edit_id        = isset($_POST['edit_id'])             ? (int)$_POST['edit_id']             : 0;

    if (empty($titre)) {
        $error  = 'Le titre est obligatoire.';
        $action = $edit_id ? 'edit' : 'add';
        $id     = $edit_id;
    } else {
        // Valider couleur hex
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $couleur)) {
            $couleur = '#3498db';
        }

        $data = array(
            $titre,
            $icone ?: null,
            $couleur,
            $cat_arme_id ?: null,
            $date_raison ?: null,
        );

        if ($edit_id > 0) {
            $data[] = $edit_id;
            $pdo->prepare(
                'UPDATE categories_tir SET titre=?, icone=?, couleur=?, categorie_arme_id=?, date_raison=? WHERE id=?'
            )->execute($data);
            flash_set('success', 'Catégorie modifiée.');
        } else {
            $pdo->prepare(
                'INSERT INTO categories_tir (titre, icone, couleur, categorie_arme_id, date_raison) VALUES (?,?,?,?,?)'
            )->execute($data);
            flash_set('success', 'Catégorie ajoutée.');
        }
        redirect(APP_URL . '/admin/categories_tir.php');
    }
}

// ── DONNÉES FORMULAIRE ─────────────────────────────────────────────────────────
$edit_row       = null;
$cat_raisons    = array();
$all_raisons    = array();
$raison_ids     = array();

if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM categories_tir WHERE id = ?');
    $s->execute(array($id));
    $edit_row = $s->fetch();
    if (!$edit_row) {
        flash_set('error', 'Catégorie introuvable.');
        redirect(APP_URL . '/admin/categories_tir.php');
    }

    $s2 = $pdo->prepare(
        'SELECT r.* FROM raisons r
         JOIN categories_tir_raisons ctr ON r.id = ctr.raison_id
         WHERE ctr.categorie_tir_id = ? ORDER BY r.libelle'
    );
    $s2->execute(array($id));
    $cat_raisons = $s2->fetchAll();
    foreach ($cat_raisons as $cr) {
        $raison_ids[] = (int)$cr['id'];
    }
    $all_raisons = $pdo->query('SELECT * FROM raisons ORDER BY libelle')->fetchAll();
}

$cats_armes = $pdo->query('SELECT id, titre FROM categories_armes ORDER BY titre')->fetchAll();

// ── LISTE ──────────────────────────────────────────────────────────────────────
$cats = $pdo->query(
    'SELECT ct.*, ca.titre AS armes_titre,
            (SELECT COUNT(*) FROM categories_tir_raisons WHERE categorie_tir_id = ct.id) AS nb_raisons
     FROM categories_tir ct
     LEFT JOIN categories_armes ca ON ct.categorie_arme_id = ca.id
     ORDER BY ct.titre ASC'
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-layer-group mr-2 text-danger"></i>Catégories de tir</h4>
    <a href="?action=add" class="btn btn-danger btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter</a>
</div>

<div class="table-responsive mb-4">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Titre</th><th>Couleur</th><th>Cat. armes</th>
                <th>Raisons</th><th style="width:120px">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($cats): ?>
        <?php foreach ($cats as $c): ?>
        <tr>
            <td>
                <span class="badge" style="background-color:<?php echo h($c['couleur']); ?>; color:#fff">
                    <?php if ($c['icone']): ?>
                    <i class="<?php echo h($c['icone']); ?> mr-1"></i>
                    <?php endif; ?>
                    <?php echo h($c['titre']); ?>
                </span>
            </td>
            <td><input type="color" value="<?php echo h($c['couleur']); ?>" disabled style="width:30px;height:24px;border:none;padding:0"></td>
            <td><?php echo $c['armes_titre'] ? h($c['armes_titre']) : '<span class="text-muted">—</span>'; ?></td>
            <td><?php echo (int)$c['nb_raisons']; ?></td>
            <td>
                <a href="?action=edit&id=<?php echo (int)$c['id']; ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <form method="post" action="?action=delete&id=<?php echo (int)$c['id']; ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="5" class="text-muted">Aucune catégorie.</td></tr>
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
        <form method="post" action="?action=save">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="edit_id" value="<?php echo $edit_row ? (int)$edit_row['id'] : 0; ?>">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Titre *</label>
                    <input type="text" name="titre" class="form-control" required
                           value="<?php echo $edit_row ? h($edit_row['titre']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Couleur</label>
                    <input type="color" name="couleur" class="form-control"
                           value="<?php echo $edit_row ? h($edit_row['couleur']) : '#3498db'; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Icône FontAwesome</label>
                    <input type="text" name="icone" class="form-control" placeholder="fas fa-bullseye"
                           value="<?php echo $edit_row ? h($edit_row['icone']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Catégorie d'armes</label>
                    <select name="categorie_arme_id" class="form-control">
                        <option value="">— Aucune —</option>
                        <?php foreach ($cats_armes as $ca): ?>
                        <option value="<?php echo (int)$ca['id']; ?>"
                            <?php echo ($edit_row && (int)$edit_row['categorie_arme_id'] === (int)$ca['id']) ? 'selected' : ''; ?>>
                            <?php echo h($ca['titre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Date de raison par défaut</label>
                    <input type="date" name="date_raison" class="form-control"
                           value="<?php echo $edit_row ? h($edit_row['date_raison']) : ''; ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo APP_URL; ?>/admin/categories_tir.php" class="btn btn-link">Annuler</a>
        </form>
    </div>
</div>

<?php if ($action === 'edit' && $edit_row): ?>
<div class="card">
    <div class="card-header"><strong>Raisons associées</strong></div>
    <div class="card-body">
        <?php if ($cat_raisons): ?>
        <table class="table table-sm table-bordered mb-3">
            <thead class="thead-light"><tr><th>Raison</th><th style="width:80px">Retirer</th></tr></thead>
            <tbody>
            <?php foreach ($cat_raisons as $cr): ?>
            <tr>
                <td><?php echo h($cr['libelle']); ?></td>
                <td>
                    <form method="post" action="?action=remove_raison&id=<?php echo (int)$id; ?>"
                          onsubmit="return confirm('Retirer cette raison ?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="raison_id" value="<?php echo (int)$cr['id']; ?>">
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted small">Aucune raison associée.</p>
        <?php endif; ?>

        <form method="post" action="?action=add_raison&id=<?php echo (int)$id; ?>" class="form-inline">
            <?php echo csrf_field(); ?>
            <select name="raison_id" class="form-control form-control-sm mr-2" required>
                <option value="">— Ajouter une raison —</option>
                <?php foreach ($all_raisons as $r): ?>
                <?php if (!in_array((int)$r['id'], $raison_ids)): ?>
                <option value="<?php echo (int)$r['id']; ?>"><?php echo h($r['libelle']); ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus mr-1"></i>Ajouter</button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
