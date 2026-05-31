<?php
// ============================================================
// PDC — Classe Helper (Utilitaires)
// ============================================================

class Helper {

    /**
     * Formate une date au format dd/mm/yyyy
     * Accepte les formats : yyyy-mm-dd, timestamp Unix, objet DateTime
     */
    public static function formatDate($date) {
        if (empty($date)) {
            return '';
        }

        // Si c'est une chaîne au format yyyy-mm-dd
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            $parts = explode('-', substr($date, 0, 10));
            return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
        }

        // Si c'est un timestamp Unix
        if (is_numeric($date)) {
            return date('d/m/Y', (int)$date);
        }

        // Si c'est un objet DateTime
        if ($date instanceof DateTime) {
            return $date->format('d/m/Y');
        }

        return '';
    }

    /**
     * Convertit une date au format dd/mm/yyyy en yyyy-mm-dd (pour stockage/URL)
     */
    public static function parseDateInput($date) {
        if (empty($date)) {
            return '';
        }

        // Si format dd/mm/yyyy
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        // Si déjà au format yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            return substr($date, 0, 10);
        }

        return '';
    }

    /**
     * Escape HTML pour l'affichage sécurisé
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
?>
