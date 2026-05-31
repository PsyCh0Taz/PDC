<?php
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = Auth::requireLogin();

// Récupérer les paramètres
$niveau    = isset($_GET['niveau']) ? $_GET['niveau'] : 'entreprise';
$id        = isset($_GET['id']) ? (int)$_GET['id'] : null;
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-01-01');
$dateFin   = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-12-31');

// Charger les paramètres PDF
$pdfTitre = Organisation::getParam('pdf_titre');
$pdfLogo  = Organisation::getParam('pdf_logo');

// Journaliser l'export
Journal::logModification(
    $currentUser['username'],
    Journal::getIp(),
    'EXPORT',
    'pdf',
    null,
    'Export PDF : niveau=' . $niveau . ', id=' . $id
);

// Pour simplifier, on génère un HTML qui sera converti en PDF
// En production, utiliser une bibliothèque comme TCPDF, mPDF ou DomPDF

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - <?php echo htmlspecialchars($pdfTitre, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            color: #333;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2c5aa0;
        }
        .pdf-header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .pdf-header h1 {
            font-size: 24pt;
            color: #2c5aa0;
            margin: 10px 0;
        }
        .pdf-header p {
            font-size: 11pt;
            color: #666;
        }
        .pdf-section {
            page-break-before: always;
            margin-bottom: 40px;
        }
        .pdf-section:first-child {
            page-break-before: auto;
        }
        .pdf-section-title {
            font-size: 20pt;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }
        .pdf-domaine {
            margin-bottom: 30px;
        }
        .pdf-domaine-titre {
            font-size: 16pt;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 15px;
        }
        .pdf-projet {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
        }
        .pdf-projet-titre {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .pdf-frise {
            position: relative;
            height: 60px;
            margin: 15px 0;
        }
        .pdf-frise-arrow {
            position: absolute;
            top: 25px;
            height: 10px;
            border-radius: 5px;
        }
        .pdf-frise-arrow:after {
            content: '';
            position: absolute;
            right: -10px;
            top: -5px;
            width: 0;
            height: 0;
            border-left: 10px solid;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
        }
        .pdf-jalon {
            position: absolute;
            top: 15px;
            text-align: center;
        }
        .pdf-jalon-triangle {
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 12px solid;
            margin: 0 auto;
        }
        .pdf-jalon-libelle {
            font-size: 9pt;
            margin-top: 5px;
            max-width: 100px;
            word-wrap: break-word;
        }
        .couleur-vert   { background-color: #27ae60; border-color: #27ae60; }
        .couleur-jaune  { background-color: #f1c40f; border-color: #f1c40f; }
        .couleur-orange { background-color: #e67e22; border-color: #e67e22; }
        .couleur-rouge  { background-color: #e74c3c; border-color: #e74c3c; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 20px; background: #f0f0f0; margin-bottom: 20px;">
    <p><strong>Aperçu de l'export PDF</strong></p>
    <p>Utilisez la fonction d'impression de votre navigateur (Ctrl+P / Cmd+P) pour enregistrer en PDF.</p>
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 14pt;">Imprimer / Enregistrer en PDF</button>
</div>

<?php

// Charger les données selon le niveau
switch ($niveau) {
    case 'entreprise':
        $entreprises = Organisation::getEntreprises();
        foreach ($entreprises as $entreprise) {
            renderEntreprise($entreprise, $dateDebut, $dateFin, $pdfTitre, $pdfLogo);
        }
        break;

    case 'departement':
        if ($id) {
            $entreprise = Organisation::getEntrepriseById($id);
            renderEntreprise($entreprise, $dateDebut, $dateFin, $pdfTitre, $pdfLogo);
        }
        break;

    case 'service':
        if ($id) {
            $departement = Organisation::getDepartementById($id);
            renderDepartement($departement, $dateDebut, $dateFin, $pdfTitre, $pdfLogo);
        }
        break;

    case 'domaine':
        if ($id) {
            $service = Organisation::getServiceById($id);
            renderService($service, $dateDebut, $dateFin, $pdfTitre, $pdfLogo);
        }
        break;
}

function renderEntreprise($entreprise, $dateDebut, $dateFin, $pdfTitre, $pdfLogo) {
    echo '<div class="pdf-section">';
    renderHeader($pdfTitre, $pdfLogo, $entreprise['nom'], $dateDebut, $dateFin);
    
    $departements = Organisation::getDepartements($entreprise['id']);
    foreach ($departements as $dep) {
        renderDepartement($dep, $dateDebut, $dateFin, $pdfTitre, $pdfLogo, false);
    }
    echo '</div>';
}

function renderDepartement($departement, $dateDebut, $dateFin, $pdfTitre, $pdfLogo, $withHeader = true) {
    if ($withHeader) {
        echo '<div class="pdf-section">';
        renderHeader($pdfTitre, $pdfLogo, $departement['nom'], $dateDebut, $dateFin);
    } else {
        echo '<div style="page-break-before: always;">';
        echo '<h2 class="pdf-section-title">' . htmlspecialchars($departement['nom'], ENT_QUOTES, 'UTF-8') . '</h2>';
    }
    
    $services = Organisation::getServices($departement['id']);
    foreach ($services as $srv) {
        renderService($srv, $dateDebut, $dateFin, $pdfTitre, $pdfLogo, false);
    }
    echo '</div>';
}

function renderService($service, $dateDebut, $dateFin, $pdfTitre, $pdfLogo, $withHeader = true) {
    if ($withHeader) {
        echo '<div class="pdf-section">';
        renderHeader($pdfTitre, $pdfLogo, $service['nom'], $dateDebut, $dateFin);
    } else {
        echo '<div style="page-break-before: always;">';
        echo '<h2 class="pdf-section-title">' . htmlspecialchars($service['nom'], ENT_QUOTES, 'UTF-8') . '</h2>';
    }
    
    $domaines = Organisation::getDomainesByService($service['id']);
    foreach ($domaines as $domaine) {
        renderDomaine($domaine, $dateDebut, $dateFin);
    }
    echo '</div>';
}

function renderDomaine($domaine, $dateDebut, $dateFin) {
    echo '<div class="pdf-domaine">';
    echo '<h3 class="pdf-domaine-titre">' . htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8') . '</h3>';
    
    $projets = Projet::getByDomaine($domaine['id']);
    foreach ($projets as $projet) {
        renderProjet($projet, $dateDebut, $dateFin);
    }
    echo '</div>';
}

function renderProjet($projet, $periodeDebut, $periodeFin) {
    echo '<div class="pdf-projet">';
    echo '<div class="pdf-projet-titre">' . htmlspecialchars($projet['titre'], ENT_QUOTES, 'UTF-8') . '</div>';
    
    $gradients = Projet::getGradients($projet['id']);
    $jalons = Projet::getJalons($projet['id']);
    
    // Calculer la frise
    $dateDebut = new DateTime($projet['date_debut']);
    $dateFin = new DateTime($projet['date_fin']);
    $pDebut = new DateTime($periodeDebut);
    $pFin = new DateTime($periodeFin);
    
    $visibleDebut = $dateDebut > $pDebut ? $dateDebut : $pDebut;
    $visibleFin = $dateFin < $pFin ? $dateFin : $pFin;
    
    if ($visibleDebut > $pFin || $visibleFin < $pDebut) {
        echo '<p><em>Hors période</em></p>';
    } else {
        $totalDays = $pFin->diff($pDebut)->days;
        $startOffset = $pDebut->diff($visibleDebut)->days / $totalDays * 100;
        $width = $visibleDebut->diff($visibleFin)->days / $totalDays * 100;
        
        // Couleur de la frise (simplifiée)
        $couleur = 'vert';
        if (!empty($gradients)) {
            $couleur = $gradients[0]['couleur'];
        }
        
        echo '<div class="pdf-frise">';
        echo '<div class="pdf-frise-arrow couleur-' . $couleur . '" style="left: ' . $startOffset . '%; width: ' . $width . '%;"></div>';
        
        // Jalons
        foreach ($jalons as $jalon) {
            $jalonDate = new DateTime($jalon['date_jalon']);
            if ($jalonDate < $pDebut || $jalonDate > $pFin) continue;
            
            $jalonOffset = $pDebut->diff($jalonDate)->days / $totalDays * 100;
            
            echo '<div class="pdf-jalon" style="left: ' . $jalonOffset . '%;">';
            echo '<div class="pdf-jalon-triangle couleur-' . $jalon['couleur'] . '"></div>';
            echo '<div class="pdf-jalon-libelle">' . htmlspecialchars($jalon['libelle'], ENT_QUOTES, 'UTF-8') . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    echo '</div>';
}

function renderHeader($titre, $logo, $sectionName, $dateDebut, $dateFin) {
    echo '<div class="pdf-header">';
    if (!empty($logo)) {
        echo '<img src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" alt="Logo">';
    }
    echo '<h1>' . htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p><strong>' . htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    echo '<p>Période : du ' . htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8') . ' au ' . htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><small>Généré le ' . date('d/m/Y à H:i') . '</small></p>';
    echo '</div>';
}

?>

</body>
</html>
