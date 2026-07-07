<?php
// ============================================================
// PDC — Classe Projet
// ============================================================

class Projet {

    // ----------------------------------------------------------
    // Lecture
    // ----------------------------------------------------------

    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetchOne('SELECT * FROM pdc_projets WHERE id = ?', array((int)$id));
    }

    public static function getByDomaine($domaineId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM pdc_projets WHERE domaine_id = ? ORDER BY ordre ASC, id ASC',
            array((int)$domaineId)
        );
    }

    public static function getGradients($projetId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM pdc_projet_gradients WHERE projet_id = ? ORDER BY date_gradient ASC',
            array((int)$projetId)
        );
    }

    public static function getJalons($projetId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT * FROM pdc_projet_jalons WHERE projet_id = ? ORDER BY date_jalon ASC',
            array((int)$projetId)
        );
    }

    // ----------------------------------------------------------
    // Création
    // ----------------------------------------------------------

    public static function create($domaineId, $titre, $dateDebut, $dateFin) {
        $db = Database::getInstance();

        // Ordre : à la fin
        $last = $db->fetchOne(
            'SELECT MAX(ordre) AS m FROM pdc_projets WHERE domaine_id = ?',
            array((int)$domaineId)
        );
        $ordre = $last ? (int)$last['m'] + 1 : 0;

        return $db->insert(
            'INSERT INTO pdc_projets (domaine_id, titre, date_debut, date_fin, ordre) VALUES (?, ?, ?, ?, ?)',
            array((int)$domaineId, self::sanitizeStr($titre), $dateDebut, $dateFin, $ordre)
        );
    }

    // ----------------------------------------------------------
    // Mise à jour
    // ----------------------------------------------------------

    public static function update($id, $titre, $dateDebut, $dateFin) {
        $db = Database::getInstance();
        return $db->execute(
            'UPDATE pdc_projets SET titre = ?, date_debut = ?, date_fin = ? WHERE id = ?',
            array(self::sanitizeStr($titre), $dateDebut, $dateFin, (int)$id)
        );
    }

    public static function delete($id) {
        $db = Database::getInstance();
        return $db->execute('DELETE FROM pdc_projets WHERE id = ?', array((int)$id));
    }

    // ----------------------------------------------------------
    // Gradients
    // ----------------------------------------------------------

    public static function saveGradients($projetId, $gradients) {
        $db = Database::getInstance();
        $db->execute('DELETE FROM pdc_projet_gradients WHERE projet_id = ?', array((int)$projetId));
        foreach ($gradients as $g) {
            if (empty($g['date']) || empty($g['couleur'])) continue;
            $libelle = isset($g['libelle']) ? $g['libelle'] : '';
            $db->insert(
                'INSERT INTO pdc_projet_gradients (projet_id, date_gradient, couleur, libelle) VALUES (?, ?, ?, ?)',
                array((int)$projetId, $g['date'], self::sanitizeCouleur($g['couleur']), $libelle)
            );
        }
    }

    // ----------------------------------------------------------
    // Jalons
    // ----------------------------------------------------------

    public static function saveJalons($projetId, $jalons) {
        $db = Database::getInstance();
        $db->execute('DELETE FROM pdc_projet_jalons WHERE projet_id = ?', array((int)$projetId));

        // Première passe : créer tous les jalons et maintenir un mapping ancien ID => nouveau ID
        $idMapping = array(); // ancien ID => nouveau ID

        foreach ($jalons as $idx => $j) {
            if (empty($j['date'])) continue;
            
            $newId = $db->insert(
                'INSERT INTO pdc_projet_jalons (projet_id, date_jalon, couleur, libelle, jalon_reference_id) VALUES (?, ?, ?, ?, ?)',
                array(
                    (int)$projetId,
                    $j['date'],
                    self::sanitizeCouleur($j['couleur']),
                    self::sanitizeStr($j['libelle']),
                    null // On mettra à jour les références après
                )
            );
            
            // Stocker le mapping si le jalon avait un ancien ID
            if (isset($j['id']) && !empty($j['id'])) {
                $idMapping[(int)$j['id']] = $newId;
            }
            
            // Pour les jalons nouveaux, on utilise l'indice comme clé temporaire
            if (!isset($j['id']) || empty($j['id'])) {
                $idMapping['_idx_' . $idx] = $newId;
            }
        }

        // Deuxième passe : mettre à jour les références avec les nouveaux IDs
        foreach ($jalons as $idx => $j) {
            if (empty($j['date'])) continue;
            
            // Récupérer le nouveau ID du jalon courant
            if (isset($j['id']) && !empty($j['id'])) {
                $currentNewId = $idMapping[(int)$j['id']];
            } else {
                $currentNewId = $idMapping['_idx_' . $idx];
            }
            
            // Si ce jalon a une référence, la remapper
            if (isset($j['jalon_reference_id']) && !empty($j['jalon_reference_id'])) {
                $refId = (int)$j['jalon_reference_id'];
                
                // Chercher le nouveau ID de la référence
                if (isset($idMapping[$refId])) {
                    $newRefId = $idMapping[$refId];
                    $db->execute(
                        'UPDATE pdc_projet_jalons SET jalon_reference_id = ? WHERE id = ?',
                        array($newRefId, $currentNewId)
                    );
                }
            }
        }
    }

    // ----------------------------------------------------------
    // Drag & drop — ordre
    // ----------------------------------------------------------

    public static function updateOrdres($ordres) {
        // $ordres = array( projetId => newOrdre, ... )
        $db = Database::getInstance();
        foreach ($ordres as $projetId => $ordre) {
            $db->execute(
                'UPDATE pdc_projets SET ordre = ? WHERE id = ?',
                array((int)$ordre, (int)$projetId)
            );
        }
    }

    public static function moveToDomaine($projetId, $domaineId) {
        $db = Database::getInstance();
        // Ordre à la fin du domaine cible
        $last = $db->fetchOne(
            'SELECT MAX(ordre) AS m FROM pdc_projets WHERE domaine_id = ?',
            array((int)$domaineId)
        );
        $ordre = $last ? (int)$last['m'] + 1 : 0;
        return $db->execute(
            'UPDATE pdc_projets SET domaine_id = ?, ordre = ? WHERE id = ?',
            array((int)$domaineId, $ordre, (int)$projetId)
        );
    }

    // ----------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------

    private static function sanitizeStr($s) {
        return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
    }

    private static function sanitizeCouleur($c) {
        $allowed = array('vert','jaune','orange','rouge');
        return in_array($c, $allowed) ? $c : 'vert';
    }
}