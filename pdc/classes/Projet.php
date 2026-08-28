<?php
// ============================================================
// PDC — Classe Projet
// ============================================================

class Projet {

    private static $commentColumn = null;
    private static $jalonCommentColumn = null;

    // ----------------------------------------------------------
    // Lecture
    // ----------------------------------------------------------

    public static function getById($id) {
        $db = Database::getInstance();
        $row = $db->fetchOne('SELECT * FROM pdc_projets WHERE id = ?', array((int)$id));
        return self::normalizeProjetRow($row);
    }

    public static function getByDomaine($domaineId) {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT * FROM pdc_projets WHERE domaine_id = ? ORDER BY ordre ASC, id ASC',
            array((int)$domaineId)
        );

        foreach ($rows as &$row) {
            $row = self::normalizeProjetRow($row);
        }

        return $rows;
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
        $rows = $db->fetchAll(
            'SELECT * FROM pdc_projet_jalons WHERE projet_id = ? ORDER BY date_jalon ASC, id ASC',
            array((int)$projetId)
        );

        foreach ($rows as &$row) {
            $row = self::normalizeJalonRow($row);
        }

        return $rows;
    }

    // ----------------------------------------------------------
    // Création
    // ----------------------------------------------------------

    public static function create($domaineId, $titre, $dateDebut, $dateFin, $commentaire = '') {
        $db = Database::getInstance();

        // Ordre : à la fin
        $last = $db->fetchOne(
            'SELECT MAX(ordre) AS m FROM pdc_projets WHERE domaine_id = ?',
            array((int)$domaineId)
        );
        $ordre = $last ? (int)$last['m'] + 1 : 0;

        $commentColumn = self::getCommentColumn();
        if ($commentColumn !== null) {
            return $db->insert(
                'INSERT INTO pdc_projets (domaine_id, titre, date_debut, date_fin, ' . $commentColumn . ', ordre) VALUES (?, ?, ?, ?, ?, ?)',
                array((int)$domaineId, self::sanitizeStr($titre), $dateDebut, $dateFin, self::sanitizeCommentaire($commentaire), $ordre)
            );
        }

        return $db->insert(
            'INSERT INTO pdc_projets (domaine_id, titre, date_debut, date_fin, ordre) VALUES (?, ?, ?, ?, ?)',
            array((int)$domaineId, self::sanitizeStr($titre), $dateDebut, $dateFin, $ordre)
        );
    }

    // ----------------------------------------------------------
    // Mise à jour
    // ----------------------------------------------------------

    public static function update($id, $titre, $dateDebut, $dateFin, $commentaire = '') {
        $db = Database::getInstance();

        $commentColumn = self::getCommentColumn();
        if ($commentColumn !== null) {
            return $db->execute(
                'UPDATE pdc_projets SET titre = ?, date_debut = ?, date_fin = ?, ' . $commentColumn . ' = ? WHERE id = ?',
                array(self::sanitizeStr($titre), $dateDebut, $dateFin, self::sanitizeCommentaire($commentaire), (int)$id)
            );
        }

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
        $jalonCommentColumn = self::getJalonCommentColumn();
        $db->execute('DELETE FROM pdc_projet_jalons WHERE projet_id = ?', array((int)$projetId));

        // Première passe : créer tous les jalons et maintenir un mapping ancien ID => nouveau ID
        $idMapping = array(); // ancien ID => nouveau ID

        foreach ($jalons as $idx => $j) {
            if (empty($j['date'])) continue;

            $commentaire = isset($j['commentaire']) ? $j['commentaire'] : '';
            
            if ($jalonCommentColumn !== null) {
                $newId = $db->insert(
                    'INSERT INTO pdc_projet_jalons (projet_id, date_jalon, couleur, libelle, ' . $jalonCommentColumn . ', jalon_reference_id) VALUES (?, ?, ?, ?, ?, ?)',
                    array(
                        (int)$projetId,
                        $j['date'],
                        self::sanitizeCouleur($j['couleur']),
                        self::sanitizeStr($j['libelle']),
                        self::sanitizeCommentaire($commentaire),
                        null
                    )
                );
            } else {
                $newId = $db->insert(
                    'INSERT INTO pdc_projet_jalons (projet_id, date_jalon, couleur, libelle, jalon_reference_id) VALUES (?, ?, ?, ?, ?)',
                    array(
                        (int)$projetId,
                        $j['date'],
                        self::sanitizeCouleur($j['couleur']),
                        self::sanitizeStr($j['libelle']),
                        null
                    )
                );
            }
            
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

    private static function sanitizeCommentaire($s) {
        $html = trim((string)$s);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $allowedTags = '<p><br><strong><b><em><i><u><s><strike><sub><sup><ul><ol><li><a><blockquote><h1><h2><h3><h4><h5><h6><span><div>';
        $html = strip_tags($html, $allowedTags);

        // Retirer les attributs inline dangereux.
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/\s(href)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', ' $1="#"', $html);
        $html = preg_replace('/\s(href)\s*=\s*javascript:[^\s>]*/i', ' $1="#"', $html);

        // Assainir les styles inline en conservant uniquement les propriétés de formatage texte.
        $html = preg_replace_callback('/\sstyle\s*=\s*("([^"]*)"|\'([^\']*)\')/i', function($m) {
            $rawStyle = isset($m[2]) && $m[2] !== '' ? $m[2] : (isset($m[3]) ? $m[3] : '');
            $safeDeclarations = array();

            $allowedProperties = array(
                'text-align',
                'color',
                'background-color',
                'font-weight',
                'font-style',
                'text-decoration'
            );

            $parts = explode(';', $rawStyle);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '' || strpos($part, ':') === false) {
                    continue;
                }

                list($prop, $value) = array_map('trim', explode(':', $part, 2));
                $prop = strtolower($prop);
                $valueLower = strtolower($value);

                if (!in_array($prop, $allowedProperties, true)) {
                    continue;
                }

                if (preg_match('/expression\s*\(|javascript:|vbscript:|url\s*\(|@import|behavior\s*:/i', $valueLower)) {
                    continue;
                }

                if ($prop === 'text-align' && !preg_match('/^(left|right|center|justify)$/i', $value)) {
                    continue;
                }

                if ($prop === 'font-weight' && !preg_match('/^(normal|bold|[1-9]00)$/i', $value)) {
                    continue;
                }

                if ($prop === 'font-style' && !preg_match('/^(normal|italic|oblique)$/i', $value)) {
                    continue;
                }

                if ($prop === 'text-decoration' && !preg_match('/^(none|underline|line-through)$/i', $value)) {
                    continue;
                }

                if (($prop === 'color' || $prop === 'background-color') && !preg_match('/^(#[0-9a-f]{3,8}|rgb\([0-9\s,\.]+\)|rgba\([0-9\s,\.]+\)|hsl\([0-9\s,%\.]+\)|hsla\([0-9\s,%\.]+\)|[a-z]+)$/i', $value)) {
                    continue;
                }

                $safeDeclarations[] = $prop . ': ' . $value;
            }

            if (empty($safeDeclarations)) {
                return '';
            }

            return ' style="' . implode('; ', $safeDeclarations) . '"';
        }, $html);

        // Garder uniquement href/target/rel sur les liens.
        $html = preg_replace_callback('/<a\b[^>]*>/i', function($m) {
            $tag = $m[0];
            $attrs = array();

            if (preg_match('/\shref\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', $tag, $href)) {
                $hrefValue = trim($href[1], "\"'");
                if (!preg_match('/^\s*javascript:/i', $hrefValue)) {
                    $attrs[] = 'href="' . htmlspecialchars($hrefValue, ENT_QUOTES, 'UTF-8') . '"';
                }
            }

            if (preg_match('/\starget\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', $tag, $target)) {
                $targetValue = trim($target[1], "\"'");
                if (in_array($targetValue, array('_blank', '_self'), true)) {
                    $attrs[] = 'target="' . $targetValue . '"';
                }
            }

            if (preg_match('/\srel\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', $tag, $rel)) {
                $relValue = trim($rel[1], "\"'");
                if ($relValue !== '') {
                    $attrs[] = 'rel="' . htmlspecialchars($relValue, ENT_QUOTES, 'UTF-8') . '"';
                }
            }

            if (in_array('target="_blank"', $attrs, true) && !preg_grep('/^rel="/i', $attrs)) {
                $attrs[] = 'rel="noopener noreferrer"';
            }

            return '<a' . (empty($attrs) ? '' : ' ' . implode(' ', $attrs)) . '>';
        }, $html);

        return $html;
    }

    private static function sanitizeCouleur($c) {
        $allowed = array('vert','jaune','orange','rouge');
        return in_array($c, $allowed) ? $c : 'vert';
    }

    private static function getCommentColumn() {
        if (self::$commentColumn !== null) {
            return self::$commentColumn;
        }

        $db = Database::getInstance();
        $candidates = array('commentaire', 'commentaires', 'comment', 'pdc_commentaire');

        foreach ($candidates as $candidate) {
            $row = $db->fetchOne("SHOW COLUMNS FROM pdc_projets LIKE ?", array($candidate));
            if (!empty($row)) {
                self::$commentColumn = $candidate;
                return self::$commentColumn;
            }
        }

        self::$commentColumn = null;
        return self::$commentColumn;
    }

    private static function getJalonCommentColumn() {
        if (self::$jalonCommentColumn !== null) {
            return self::$jalonCommentColumn;
        }

        $db = Database::getInstance();
        $candidates = array('commentaire', 'commentaires', 'comment', 'pdc_commentaire');

        foreach ($candidates as $candidate) {
            $row = $db->fetchOne("SHOW COLUMNS FROM pdc_projet_jalons LIKE ?", array($candidate));
            if (!empty($row)) {
                self::$jalonCommentColumn = $candidate;
                return self::$jalonCommentColumn;
            }
        }

        self::$jalonCommentColumn = null;
        return self::$jalonCommentColumn;
    }

    private static function normalizeProjetRow($row) {
        if (!$row) {
            return $row;
        }

        if (!isset($row['commentaire'])) {
            $commentColumn = self::getCommentColumn();
            if ($commentColumn !== null && isset($row[$commentColumn])) {
                $row['commentaire'] = $row[$commentColumn];
            } else {
                $row['commentaire'] = '';
            }
        }

        return $row;
    }

    private static function normalizeJalonRow($row) {
        if (!$row) {
            return $row;
        }

        if (!isset($row['commentaire'])) {
            $commentColumn = self::getJalonCommentColumn();
            if ($commentColumn !== null && isset($row[$commentColumn])) {
                $row['commentaire'] = $row[$commentColumn];
            } else {
                $row['commentaire'] = '';
            }
        }

        return $row;
    }
}
