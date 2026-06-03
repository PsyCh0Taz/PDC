<?php
/**
 * Hiérarchie de services - Treeview Bootstrap
 * 
 * Structure de données : tableau associatif représentant les services.
 * En production, remplacez $services par une requête SQL (voir commentaire plus bas).
 */

// ─── Données de démonstration ─────────────────────────────────────────────────
// Chaque service a : id, parent_id (null = racine), name, icon (optionnel)
$services = [
    ['id' => 1,  'parent_id' => null, 'name' => 'Direction Générale',       'icon' => 'bi-building'],
    ['id' => 2,  'parent_id' => 1,    'name' => 'Direction Technique',       'icon' => 'bi-gear'],
    ['id' => 3,  'parent_id' => 1,    'name' => 'Direction Commerciale',     'icon' => 'bi-briefcase'],
    ['id' => 4,  'parent_id' => 1,    'name' => 'Direction Administrative',  'icon' => 'bi-bank'],
    ['id' => 5,  'parent_id' => 2,    'name' => 'Développement',             'icon' => 'bi-code-slash'],
    ['id' => 6,  'parent_id' => 2,    'name' => 'Infrastructure',            'icon' => 'bi-server'],
    ['id' => 7,  'parent_id' => 2,    'name' => 'Sécurité',                  'icon' => 'bi-shield-check'],
    ['id' => 8,  'parent_id' => 5,    'name' => 'Front-end',                 'icon' => 'bi-laptop'],
    ['id' => 9,  'parent_id' => 5,    'name' => 'Back-end',                  'icon' => 'bi-database'],
    ['id' => 10, 'parent_id' => 5,    'name' => 'Mobile',                    'icon' => 'bi-phone'],
    ['id' => 11, 'parent_id' => 6,    'name' => 'Réseau',                    'icon' => 'bi-diagram-3'],
    ['id' => 12, 'parent_id' => 6,    'name' => 'Cloud',                     'icon' => 'bi-cloud'],
    ['id' => 13, 'parent_id' => 3,    'name' => 'Marketing',                 'icon' => 'bi-megaphone'],
    ['id' => 14, 'parent_id' => 3,    'name' => 'Ventes',                    'icon' => 'bi-cart'],
    ['id' => 15, 'parent_id' => 4,    'name' => 'Ressources Humaines',       'icon' => 'bi-people'],
    ['id' => 16, 'parent_id' => 4,    'name' => 'Comptabilité',              'icon' => 'bi-calculator'],
    ['id' => 17, 'parent_id' => 15,   'name' => 'Recrutement',               'icon' => 'bi-person-plus'],
    ['id' => 18, 'parent_id' => 15,   'name' => 'Formation',                 'icon' => 'bi-mortarboard'],
];

/*
 * ─── Version base de données ──────────────────────────────────────────────────
 * Remplacez le tableau ci-dessus par cette requête PDO :
 *
 * $pdo = new PDO('mysql:host=localhost;dbname=mabase', 'user', 'pass');
 * $stmt = $pdo->query('SELECT id, parent_id, name, icon FROM services ORDER BY parent_id, name');
 * $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
 */


// ─── Construction de l'arbre ──────────────────────────────────────────────────
/**
 * Transforme un tableau plat en arbre hiérarchique.
 * @param array $items  Liste plate de nœuds avec 'id' et 'parent_id'
 * @param int|null $parentId  Identifiant du nœud parent courant
 * @return array  Arbre hiérarchique
 */
function buildTree($items, $parentId = null)
{
    $branch = [];
    foreach ($items as $item) {
        if ($item['parent_id'] === $parentId) {
            $children = buildTree($items, (int)$item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }
    }
    return $branch;
}

$tree = buildTree($services);


// ─── Rendu HTML récursif du treeview ─────────────────────────────────────────
/**
 * Génère le HTML Bootstrap d'un nœud et de ses enfants.
 * @param array $nodes  Nœuds à afficher
 * @param int   $depth  Profondeur actuelle (pour l'ID unique collapse)
 * @param string $prefix  Préfixe pour générer des IDs uniques
 */
function renderTree( $nodes,  $depth = 0,  $prefix = 'root')
{
    echo '<ul class="tree-list' . ($depth === 0 ? ' tree-root' : '') . '">';
    foreach ($nodes as $index => $node) {
        $hasChildren = !empty($node['children']);
        $nodeId      = 'node-' . $prefix . '-' . $index;
        $icon        = ( $node['icon'] ? $node['icon'] : 'bi-circle' );
        $collapseId  = 'collapse-' . $nodeId;

        echo '<li class="tree-item' . ($hasChildren ? ' has-children' : '') . '">';

        if ($hasChildren) {
            // Nœud parent : bouton toggle + icône dossier
            echo '<div class="tree-node d-flex align-items-center gap-2" ';
            echo '     data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" ';
            echo '     aria-expanded="' . ($depth === 0 ? 'true' : 'false') . '" ';
            echo '     aria-controls="' . $collapseId . '">';
            echo '  <span class="toggle-icon"><i class="bi bi-chevron-right"></i></span>';
            echo '  <i class="bi ' . htmlspecialchars($icon) . ' node-icon"></i>';
            echo '  <span class="node-label">' . htmlspecialchars($node['name']) . '</span>';
            echo '  <span class="badge rounded-pill">' . count($node['children']) . '</span>';
            echo '</div>';

            // Sous-liste (collapse Bootstrap)
            echo '<div class="collapse' . ($depth === 0 ? ' show' : '') . '" id="' . $collapseId . '">';
            renderTree($node['children'], $depth + 1, $nodeId);
            echo '</div>';
        } else {
            // Nœud feuille
            echo '<div class="tree-node tree-leaf d-flex align-items-center gap-2">';
            echo '  <span class="toggle-icon invisible"><i class="bi bi-chevron-right"></i></span>';
            echo '  <i class="bi ' . htmlspecialchars($icon) . ' node-icon leaf-icon"></i>';
            echo '  <span class="node-label">' . htmlspecialchars($node['name']) . '</span>';
            echo '</div>';
        }

        echo '</li>';
    }
    echo '</ul>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hiérarchie des Services</title>

    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --tree-indent:      1.6rem;
            --tree-bg:          #ffffff;
            --tree-border:      #e2e8f0;
            --node-hover-bg:    #f0f7ff;
            --node-active-bg:   #dbeafe;
            --node-color:       #1e293b;
            --icon-color:       #3b82f6;
            --leaf-icon-color:  #64748b;
            --badge-bg:         #e0f2fe;
            --badge-color:      #0369a1;
            --toggle-color:     #94a3b8;
            --line-color:       #cbd5e1;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--node-color);
        }

        .card-tree {
            background: var(--tree-bg);
            border: 1px solid var(--tree-border);
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
        }

        /* ── Structure liste ── */
        .tree-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .tree-root {
            padding: 0.25rem 0;
        }
        .tree-list .tree-list {
            padding-left: var(--tree-indent);
            border-left: 2px solid var(--line-color);
            margin-left: 0.85rem;
        }

        .tree-item {
            position: relative;
        }

        /* ── Nœud ── */
        .tree-node {
            padding: 0.45rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .tree-node:hover {
            background: var(--node-hover-bg);
        }
        .tree-node[aria-expanded="true"] {
            background: var(--node-active-bg);
            font-weight: 600;
        }
        .tree-leaf {
            cursor: default;
        }
        .tree-leaf:hover {
            background: #f8fafc;
        }

        /* ── Icônes ── */
        .toggle-icon {
            width: 1rem;
            font-size: .75rem;
            color: var(--toggle-color);
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .tree-node[aria-expanded="true"] .toggle-icon {
            transform: rotate(90deg);
        }
        .node-icon {
            font-size: 1rem;
            color: var(--icon-color);
            flex-shrink: 0;
        }
        .leaf-icon {
            color: var(--leaf-icon-color);
        }

        /* ── Badge enfants ── */
        .badge {
            font-size: .65rem;
            font-weight: 600;
            background: var(--badge-bg) !important;
            color: var(--badge-color) !important;
            margin-left: auto;
        }

        /* ── Barre de recherche ── */
        #searchInput {
            border-radius: 0.5rem;
            border: 1px solid var(--tree-border);
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            width: 100%;
            outline: none;
            transition: border-color .2s;
        }
        #searchInput:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .search-wrapper { position: relative; }
        .search-wrapper .bi { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* ── Highlight recherche ── */
        .highlight { background: #fef08a; border-radius: 2px; padding: 0 2px; }

        /* ── Boutons ── */
        .btn-tree {
            font-size: .8rem;
            padding: .3rem .75rem;
            border-radius: .5rem;
        }

        /* ── Nœud masqué par la recherche ── */
        .tree-item.hidden { display: none; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width: 720px">

    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-diagram-2 text-primary me-2"></i>Hiérarchie des Services
        </h1>
        <p class="text-muted small mb-0">Arborescence générée depuis PHP</p>
    </div>

    <div class="card-tree p-4">

        <!-- Barre d'outils -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <div class="search-wrapper flex-grow-1">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un service…" autocomplete="off">
            </div>
            <button class="btn btn-outline-secondary btn-tree" id="btnExpandAll">
                <i class="bi bi-arrows-expand me-1"></i>Tout déplier
            </button>
            <button class="btn btn-outline-secondary btn-tree" id="btnCollapseAll">
                <i class="bi bi-arrows-collapse me-1"></i>Tout replier
            </button>
        </div>

        <!-- Treeview généré par PHP -->
        <div id="treeContainer">
            <?php renderTree($tree); ?>
        </div>

    </div>

    <p class="text-muted text-center mt-3" style="font-size:.75rem">
        <?= count($services) ?> services &bull; <?= count($tree) ?> racine(s)
    </p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Expand / Collapse tout ────────────────────────────────────────────────────
document.getElementById('btnExpandAll').addEventListener('click', () => {
    document.querySelectorAll('#treeContainer .collapse').forEach(el => {
        bootstrap.Collapse.getOrCreateInstance(el).show();
    });
});
document.getElementById('btnCollapseAll').addEventListener('click', () => {
    document.querySelectorAll('#treeContainer .collapse').forEach(el => {
        bootstrap.Collapse.getOrCreateInstance(el).hide();
    });
});

// ── Recherche avec highlight ──────────────────────────────────────────────────
const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('input', function () {
    const query = this.value.trim().toLowerCase();

    // Réinitialisation
    document.querySelectorAll('.node-label').forEach(el => {
        el.innerHTML = el.textContent; // supprime anciens highlights
    });
    document.querySelectorAll('.tree-item').forEach(el => el.classList.remove('hidden'));

    if (!query) return;

    // Marque les nœuds qui ne correspondent pas
    document.querySelectorAll('.tree-item').forEach(item => {
        const label = item.querySelector(':scope > .tree-node .node-label');
        if (!label) return;
        const text = label.textContent.toLowerCase();
        if (!text.includes(query)) {
            // Masquer seulement si aucun enfant visible
            const visibleChild = item.querySelector('.tree-item:not(.hidden)');
            if (!visibleChild) item.classList.add('hidden');
        } else {
            // Highlight
            const re = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            label.innerHTML = label.textContent.replace(re, '<mark class="highlight">$1</mark>');
            // S'assurer que tous les parents sont visibles
            let parent = item.parentElement;
            while (parent && parent.id !== 'treeContainer') {
                if (parent.classList.contains('tree-item')) parent.classList.remove('hidden');
                if (parent.classList.contains('collapse')) {
                    bootstrap.Collapse.getOrCreateInstance(parent).show();
                }
                parent = parent.parentElement;
            }
        }
    });
});
</script>
</body>
</html>