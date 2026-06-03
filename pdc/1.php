<?php
/**
 * Affichage de la hiérarchie LDAP en treeview Bootstrap
 * -------------------------------------------------------
 * Configuration : adaptez les constantes ci-dessous à votre annuaire.
 */

// ─── Configuration LDAP ───────────────────────────────────────────────────────

define('LDAP_VERSION',  3);

define('LDAP_HOST',        '192.168.1.2');
define('LDAP_PORT',        389);
define('LDAP_BASE_DN',     'dc=a,dc=c,dc=d,dc=fr');
define('LDAP_BIND_DN',     'cn=admin,' . LDAP_BASE_DN);
define('LDAP_BIND_PW','admin');

// Attributs LDAP utilisés
define('LDAP_ATTR_NAME',   'ou');          // nom affiché du nœud
define('LDAP_ATTR_DESC',   'description'); // info-bulle optionnelle
define('LDAP_OBJECT_CLASS','organizationalUnit');

// ─── Connexion & lecture LDAP ─────────────────────────────────────────────────

/**
 * Retourne un tableau associatif représentant l'arbre :
 *   [ 'dn' => ..., 'name' => ..., 'desc' => ..., 'children' => [...] ]
 */
function fetchLdapTree( $baseDn, $ldapConn) {
    $filter  = '(objectClass=' . LDAP_OBJECT_CLASS . ')';
    $attrs   = [LDAP_ATTR_NAME, LDAP_ATTR_DESC];

    $result  = @ldap_search($ldapConn, $baseDn, $filter, $attrs, 0, 0, 0, LDAP_DEREF_NEVER);
    if (!$result) return [];

    $entries = ldap_get_entries($ldapConn, $result);
    $nodes   = [];

    for ($i = 0; $i < $entries['count']; $i++) {
        $dn   = $entries[$i]['dn'];
        $name = ( $entries[$i][LDAP_ATTR_NAME][0]  ? $entries[$i][LDAP_ATTR_NAME][0] : $dn );
        @$desc = ( $entries[$i][LDAP_ATTR_DESC][0]   ? $entries[$i][LDAP_ATTR_DESC][0] : '' );
        $nodes[$dn] = [
            'dn'       => $dn,
            'name'     => $name,
            'desc'     => $desc,
            'parent'   => getParentDn($dn),
            'children' => [],
        ];
    }

    // Reconstruction de l'arborescence
    $roots = [];
    foreach ($nodes as $dn => &$node) {
        $parentDn = $node['parent'];
        if (isset($nodes[$parentDn])) {
            $nodes[$parentDn]['children'][] = &$node;
        } else {
            $roots[] = &$node;
        }
    }
    unset($node);

    // Si plusieurs racines, on retourne un nœud virtuel "racine"
    if (count($roots) === 1) return $roots[0];
    return [
        'dn'       => $baseDn,
        'name'     => 'Services',
        'desc'     => '',
        'children' => $roots,
    ];
}

/** Retourne le DN parent en retirant le premier RDN */
function getParentDn( $dn) {
    $parts = ldap_explode_dn($dn, 0);
    if ($parts === false || $parts['count'] <= 1) return '';
    unset($parts['count'], $parts[0]);
    return implode(',', $parts);
}

// ─── Rendu HTML récursif ──────────────────────────────────────────────────────

function renderTree($node, $depth = 0) {
    $hasChildren = !empty($node['children']);
    $id          = 'node-' . md5($node['dn']);
    $name        = htmlspecialchars($node['name']);
    $desc        = htmlspecialchars($node['desc']);
    $tooltip     = $desc ? " title=\"{$desc}\" data-bs-toggle=\"tooltip\"" : '';

    $icon = $hasChildren
        ? '<span class="tree-icon folder-icon">&#9660;</span>'
        : '<span class="tree-icon leaf-icon">&#8227;</span>';

    $badge = $hasChildren
        ? '<span class="badge rounded-pill bg-secondary ms-2">' . count($node['children']) . '</span>'
        : '';

    $html  = '<li class="tree-item">';
    if ($hasChildren) {
        $html .= '<details open>';
        $html .= '<summary class="tree-node depth-' . $depth . '"' . $tooltip . '>';
        $html .= $icon . ' <span class="node-label">' . $name . '</span>' . $badge;
        $html .= '</summary>';
        $html .= '<ul class="tree-children">';
        foreach ($node['children'] as $child) {
            $html .= renderTree($child, $depth + 1);
        }
        $html .= '</ul>';
        $html .= '</details>';
    } else {
        $html .= '<div class="tree-node leaf depth-' . $depth . '"' . $tooltip . '>';
        $html .= $icon . ' <span class="node-label">' . $name . '</span>';
        $html .= '</div>';
    }
    $html .= '</li>';
    return $html;
}

// ─── Connexion & données ──────────────────────────────────────────────────────

$error   = null;
$treeHtml = '';

$conn = @ldap_connect(LDAP_HOST, LDAP_PORT);
if (!$conn) {
    $error = 'Impossible de se connecter au serveur LDAP.';
} else {
    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, LDAP_VERSION);
    ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

    $bound = (LDAP_BIND_DN)
        ? @ldap_bind($conn, LDAP_BIND_DN, LDAP_BIND_PW)
        : @ldap_bind($conn);

    if (!$bound) {
        $error = 'Échec de l\'authentification LDAP : ' . ldap_error($conn);
    } else {
        $tree     = fetchLdapTree(LDAP_BASE_DN, $conn);
        $treeHtml = '<ul class="tree-root">' . renderTree($tree) . '</ul>';
        ldap_unbind($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hiérarchie LDAP — Services</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:        #0d0f14;
      --panel:     #13161e;
      --border:    #1e2433;
      --accent:    #4af0b4;
      --accent2:   #2d8fff;
      --text:      #c8d0e0;
      --text-dim:  #5a6480;
      --leaf:      #7a85a0;
      --radius:    8px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'IBM Plex Sans', sans-serif;
      min-height: 100vh;
      padding: 2rem 1rem;
    }

    /* ── Header ── */
    .app-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border);
    }
    .app-header .logo {
      font-family: 'IBM Plex Mono', monospace;
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--accent);
      letter-spacing: -1px;
    }
    .app-header .subtitle {
      font-size: .8rem;
      color: var(--text-dim);
      font-family: 'IBM Plex Mono', monospace;
    }
    .ldap-badge {
      font-family: 'IBM Plex Mono', monospace;
      font-size: .7rem;
      background: rgba(74,240,180,.12);
      color: var(--accent);
      border: 1px solid rgba(74,240,180,.3);
      padding: .2rem .6rem;
      border-radius: 4px;
      margin-left: auto;
    }

    /* ── Panel ── */
    .tree-panel {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
    }
    .panel-title {
      font-family: 'IBM Plex Mono', monospace;
      font-size: .75rem;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 1.25rem;
    }

    /* ── Tree ── */
    .tree-root,
    .tree-children {
      list-style: none;
      padding: 0;
    }
    .tree-children {
      padding-left: 1.4rem;
      border-left: 1px dashed var(--border);
      margin-left: .6rem;
    }
    .tree-item {
      margin: .15rem 0;
    }

    /* summary reset */
    details > summary { list-style: none; }
    details > summary::-webkit-details-marker { display: none; }

    .tree-node {
      display: flex;
      align-items: center;
      gap: .5rem;
      padding: .3rem .6rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background .15s, color .15s;
      font-size: .9rem;
      user-select: none;
    }
    .tree-node:hover {
      background: rgba(255,255,255,.04);
      color: #fff;
    }
    .tree-node.leaf {
      cursor: default;
      color: var(--leaf);
    }
    .tree-node.leaf:hover {
      background: rgba(255,255,255,.03);
      color: var(--text);
    }

    .node-label { flex: 1; }

    /* Icons */
    .tree-icon {
      font-size: .75rem;
      transition: transform .2s;
    }
    .folder-icon { color: var(--accent); }
    .leaf-icon   { color: var(--text-dim); }

    details[open] > summary .folder-icon { transform: rotate(0deg); }
    details:not([open]) > summary .folder-icon { transform: rotate(-90deg); }

    /* depth tints */
    .depth-0 { font-weight: 600; font-size: .95rem; }
    .depth-1 { }
    .depth-2 { font-size: .85rem; }
    .depth-3, .depth-4 { font-size: .82rem; color: var(--leaf); }

    /* Badge */
    .badge {
      font-family: 'IBM Plex Mono', monospace;
      font-size: .65rem;
      background: rgba(45,143,255,.2) !important;
      color: var(--accent2) !important;
      border: 1px solid rgba(45,143,255,.3);
    }

    /* ── Error ── */
    .alert-ldap {
      background: rgba(255,80,80,.1);
      border: 1px solid rgba(255,80,80,.3);
      color: #ff8080;
      border-radius: var(--radius);
      padding: 1rem 1.25rem;
      font-family: 'IBM Plex Mono', monospace;
      font-size: .85rem;
    }

    /* ── Controls ── */
    .tree-controls {
      display: flex;
      gap: .5rem;
      margin-bottom: 1.25rem;
    }
    .btn-tree {
      font-family: 'IBM Plex Mono', monospace;
      font-size: .72rem;
      padding: .3rem .8rem;
      border-radius: 5px;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text-dim);
      cursor: pointer;
      transition: border-color .15s, color .15s;
    }
    .btn-tree:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    /* ── Search ── */
    .tree-search {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 6px;
      color: var(--text);
      font-family: 'IBM Plex Mono', monospace;
      font-size: .8rem;
      padding: .35rem .8rem;
      width: 100%;
      margin-bottom: 1.25rem;
      outline: none;
      transition: border-color .15s;
    }
    .tree-search:focus { border-color: var(--accent); }
    .tree-search::placeholder { color: var(--text-dim); }

    .highlight { background: rgba(74,240,180,.25); border-radius: 3px; padding: 0 2px; }
    .tree-item.hidden { display: none; }
  </style>
</head>
<body>
<div class="container" style="max-width:720px">

  <!-- Header -->
  <div class="app-header">
    <div>
      <div class="logo">&#9636; LDAP Tree</div>
      <div class="subtitle"><?= htmlspecialchars(LDAP_HOST . ' / ' . LDAP_BASE_DN) ?></div>
    </div>
    <span class="ldap-badge">v<?= LDAP_VERSION ?></span>
  </div>

  <?php if ($error): ?>
    <div class="alert-ldap">&#9888; <?= htmlspecialchars($error) ?></div>

  <?php else: ?>
    <div class="tree-panel">
      <div class="panel-title">Arborescence des services</div>

      <!-- Search -->
      <input type="text" class="tree-search" id="treeSearch" placeholder="Filtrer les nœuds…">

      <!-- Controls -->
      <div class="tree-controls">
        <button class="btn-tree" id="expandAll">&#9660; Tout déplier</button>
        <button class="btn-tree" id="collapseAll">&#9658; Tout replier</button>
      </div>

      <!-- Tree -->
      <div id="treeContainer">
        <?= $treeHtml ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Tooltips Bootstrap
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { placement: 'right' });
  });

  // Expand / Collapse all
  document.getElementById('expandAll')?.addEventListener('click', () => {
    document.querySelectorAll('#treeContainer details').forEach(d => d.open = true);
  });
  document.getElementById('collapseAll')?.addEventListener('click', () => {
    document.querySelectorAll('#treeContainer details').forEach(d => d.open = false);
  });

  // Filtre / recherche
  const searchInput = document.getElementById('treeSearch');
  searchInput?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();

    // Supprimer les anciens highlights
    document.querySelectorAll('.highlight').forEach(el => {
      el.replaceWith(document.createTextNode(el.textContent));
    });
    // Normaliser les text nodes fusionnés
    document.querySelectorAll('.node-label').forEach(el => el.normalize());

    if (!q) {
      document.querySelectorAll('#treeContainer .tree-item').forEach(li => li.classList.remove('hidden'));
      document.querySelectorAll('#treeContainer details').forEach(d => d.open = true);
      return;
    }

    const allItems = [...document.querySelectorAll('#treeContainer .tree-item')];

    allItems.forEach(li => {
      const label = li.querySelector('.node-label');
      if (!label) return;
      const text  = label.textContent.toLowerCase();
      const match = text.includes(q);
      li.classList.toggle('hidden', !match);

      if (match) {
        // Highlight
        const raw = label.textContent;
        const idx = raw.toLowerCase().indexOf(q);
        label.innerHTML =
          escHtml(raw.slice(0, idx)) +
          '<span class="highlight">' + escHtml(raw.slice(idx, idx + q.length)) + '</span>' +
          escHtml(raw.slice(idx + q.length));

        // Remonter les ancêtres
        let parent = li.parentElement;
        while (parent) {
          if (parent.classList.contains('tree-item')) parent.classList.remove('hidden');
          if (parent.tagName === 'DETAILS') parent.open = true;
          parent = parent.parentElement;
        }
      }
    });
  });

  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
</script>
</body>
</html>