<?php
/**
 * TIR118 - Fonctions utilitaires
 */

/** Échappe une chaîne pour l'affichage HTML (XSS). */
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/** Redirige vers une URL et stoppe l'exécution. */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/** Stocke un message flash dans la session. */
function flash_set($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

/** Récupère et supprime le message flash de la session. */
function flash_get() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Affiche un éventuel message flash Bootstrap. */
function flash_render() {
    $flash = flash_get();
    if (!$flash) {
        return;
    }
    $map = array('success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info');
    $cls = isset($map[$flash['type']]) ? $map[$flash['type']] : 'info';
    echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
       . h($flash['message'])
       . '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'
       . '</div>';
}

/** Génère un hash aléatoire (hex). */
function generate_hash($bytes = 32) {
    return bin2hex(openssl_random_pseudo_bytes($bytes));
}

/** Nombre d'inscrits (hors liste d'attente) pour un tir. */
function nb_inscrits($tir_id) {
    $stmt = get_pdo()->prepare(
        "SELECT COUNT(*) FROM inscriptions WHERE tir_id = ? AND type = 'inscrit'"
    );
    $stmt->execute(array((int)$tir_id));
    return (int)$stmt->fetchColumn();
}

/** Nombre de personnes en liste d'attente pour un tir. */
function nb_attente($tir_id) {
    $stmt = get_pdo()->prepare(
        "SELECT COUNT(*) FROM inscriptions WHERE tir_id = ? AND type = 'attente'"
    );
    $stmt->execute(array((int)$tir_id));
    return (int)$stmt->fetchColumn();
}

/** Nombre de places restantes pour un tir. */
function places_restantes($tir_id, $nb_places) {
    return (int)$nb_places - nb_inscrits($tir_id);
}

/**
 * Promeut la première personne en attente vers "inscrit" si une place est libre.
 * Envoie un mail de notification.
 */
function promouvoir_liste_attente($tir_id) {
    $pdo = get_pdo();

    // Récupérer les infos du tir
    $stmt = $pdo->prepare(
        'SELECT t.*, ct.titre FROM tirs t
         JOIN categories_tir ct ON t.categorie_tir_id = ct.id
         WHERE t.id = ?'
    );
    $stmt->execute(array((int)$tir_id));
    $tir = $stmt->fetch();
    if (!$tir) {
        return;
    }

    if (places_restantes($tir_id, $tir['nb_places']) <= 0) {
        return;
    }

    // Premier en attente
    $stmt = $pdo->prepare(
        "SELECT * FROM inscriptions WHERE tir_id = ? AND type = 'attente' ORDER BY created_at ASC LIMIT 1"
    );
    $stmt->execute(array((int)$tir_id));
    $inscrit = $stmt->fetch();
    if (!$inscrit) {
        return;
    }

    $pdo->prepare("UPDATE inscriptions SET type = 'inscrit' WHERE id = ?")
        ->execute(array((int)$inscrit['id']));

    $cat_stmt = $pdo->prepare('SELECT * FROM categories_tir WHERE id = ?');
    $cat_stmt->execute(array($tir['categorie_tir_id']));
    $categorie = $cat_stmt->fetch();

    mail_promotion_attente($inscrit, $tir, $categorie ?: array('titre' => 'Séance'));
}

/**
 * Télécharge et sauvegarde une image uploadée.
 * @param array  $file  Entrée de $_FILES
 * @param string $type  Sous-répertoire (ex: 'armes', 'categories')
 * @return array ['success'=>bool, 'path'=>string|null, 'error'=>string|null]
 */
function upload_image($file, $type) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $messages = array(
            UPLOAD_ERR_INI_SIZE   => 'Le fichier dépasse la limite du serveur.',
            UPLOAD_ERR_FORM_SIZE  => 'Le fichier dépasse la limite du formulaire.',
            UPLOAD_ERR_PARTIAL    => 'Téléchargement partiel.',
            UPLOAD_ERR_NO_FILE    => 'Aucun fichier sélectionné.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque.',
        );
        $code = isset($file['error']) ? $file['error'] : -1;
        $msg  = isset($messages[$code]) ? $messages[$code] : 'Erreur inconnue.';
        return array('success' => false, 'path' => null, 'error' => $msg);
    }

    // Validation via getimagesize (ignore le Content-Type déclaré par le client)
    $img_info = @getimagesize($file['tmp_name']);
    if (!$img_info) {
        return array('success' => false, 'path' => null, 'error' => 'Le fichier n\'est pas une image valide.');
    }

    $allowed = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
    if (!in_array($img_info[2], $allowed)) {
        return array('success' => false, 'path' => null, 'error' => 'Format non autorisé (jpg, png, gif uniquement).');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return array('success' => false, 'path' => null, 'error' => 'Image trop volumineuse (max 2 Mo).');
    }

    $exts = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif');
    $ext  = $exts[$img_info[2]];

    $type_clean = preg_replace('/[^a-z0-9_]/', '', strtolower($type));
    $filename   = bin2hex(openssl_random_pseudo_bytes(8)) . '.' . $ext;
    $dir        = dirname(__DIR__) . '/assets/img/uploads/' . $type_clean . '/';

    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return array('success' => false, 'path' => null, 'error' => 'Impossible de créer le répertoire de destination.');
    }

    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return array('success' => false, 'path' => null, 'error' => 'Erreur lors de l\'enregistrement de l\'image.');
    }

    return array(
        'success' => true,
        'path'    => 'assets/img/uploads/' . $type_clean . '/' . $filename,
        'error'   => null,
    );
}

/** Affiche une image ou un placeholder. */
function img_tag($path, $alt = '', $class = '') {
    if ($path && file_exists(dirname(__DIR__) . '/' . $path)) {
        $src = APP_URL . '/' . h($path);
    } else {
        $src = 'https://via.placeholder.com/80x60?text=' . urlencode($alt ?: 'Image');
    }
    return '<img src="' . $src . '" alt="' . h($alt) . '" class="' . h($class) . '">';
}

/** Retourne la date/heure formatée en français. */
function fmt_date($datetime, $format = 'd/m/Y') {
    return $datetime ? date($format, strtotime($datetime)) : '';
}

function fmt_datetime($datetime) {
    return fmt_date($datetime, 'd/m/Y à H:i');
}
