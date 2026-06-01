<?php
// ============================================================
// PDC — Classe LDAP (récupération de l'organisation)
// ============================================================

class LDAP {

    /**
     * Se connecte au serveur LDAP
     * Retourne une ressource LDAP ou lance une exception
     */
    private static function connect() {
        $ldap = @ldap_connect(LDAP_HOST, LDAP_PORT);
        if (!$ldap) {
            throw new Exception('Impossible de se connecter au serveur LDAP.');
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        // Bind compte de service
        $bound = @ldap_bind($ldap, LDAP_USER_DN, LDAP_USER_DN_PASS);
        if (!$bound) {
            ldap_close($ldap);
            throw new Exception('Impossible de s\'authentifier sur le serveur LDAP.');
        }

        return $ldap;
    }

    /**
     * Récupère les entreprises du LDAP (filter: objectClass=organizationalUnit)
     * Cherche dans les OU de premier niveau
     */
    public static function getEntreprisesFromLDAP() {
        $ldap = self::connect();
        
        // Chercher les OU de premier niveau sous le base DN
        $filter = '(objectClass=organizationalUnit)';
        $search = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('ou', 'description'), false, 500);
        
        if (!$search) {
            ldap_close($ldap);
            return array();
        }

        $entries = ldap_get_entries($ldap, $search);
        ldap_close($ldap);

        $entreprises = array();
        for ($i = 0; $i < $entries['count']; $i++) {
            if ($entries[$i]['count'] > 0) {
                $entreprises[] = array(
                    'ou' => $entries[$i]['ou'][0],
                    'dn' => $entries[$i]['dn'],
                    'description' => isset($entries[$i]['description'][0]) ? $entries[$i]['description'][0] : ''
                );
            }
        }

        return $entreprises;
    }

    /**
     * Récupère les départements d'une entreprise du LDAP
     */
    public static function getDepartementsFromLDAP($entrepriseDN) {
        $ldap = self::connect();
        
        $filter = '(objectClass=organizationalUnit)';
        $search = @ldap_search($ldap, $entrepriseDN, $filter, array('ou', 'description'), false, 500);
        
        if (!$search) {
            ldap_close($ldap);
            return array();
        }

        $entries = ldap_get_entries($ldap, $search);
        ldap_close($ldap);

        $departements = array();
        for ($i = 0; $i < $entries['count']; $i++) {
            if ($entries[$i]['count'] > 0 && $entries[$i]['dn'] !== $entrepriseDN) {
                $departements[] = array(
                    'ou' => $entries[$i]['ou'][0],
                    'dn' => $entries[$i]['dn'],
                    'description' => isset($entries[$i]['description'][0]) ? $entries[$i]['description'][0] : ''
                );
            }
        }

        return $departements;
    }

    /**
     * Récupère les services d'un département du LDAP
     */
    public static function getServicesFromLDAP($departementDN) {
        $ldap = self::connect();
        
        $filter = '(objectClass=organizationalUnit)';
        $search = @ldap_search($ldap, $departementDN, $filter, array('ou', 'description'), false, 500);
        
        if (!$search) {
            ldap_close($ldap);
            return array();
        }

        $entries = ldap_get_entries($ldap, $search);
        ldap_close($ldap);

        $services = array();
        for ($i = 0; $i < $entries['count']; $i++) {
            if ($entries[$i]['count'] > 0 && $entries[$i]['dn'] !== $departementDN) {
                $services[] = array(
                    'ou' => $entries[$i]['ou'][0],
                    'dn' => $entries[$i]['dn'],
                    'description' => isset($entries[$i]['description'][0]) ? $entries[$i]['description'][0] : ''
                );
            }
        }

        return $services;
    }

    /**
     * Récupère la structure complète de l'organisation du LDAP
     * Retourne un tableau imbriqué : entreprises > départements > services
     */
    public static function getFullOrganization() {
        $entreprises = self::getEntreprisesFromLDAP();
        $result = array();

        foreach ($entreprises as $entreprise) {
            $departements = self::getDepartementsFromLDAP($entreprise['dn']);
            $entreprise['departements'] = array();

            foreach ($departements as $departement) {
                $services = self::getServicesFromLDAP($departement['dn']);
                $departement['services'] = $services;
                $entreprise['departements'][] = $departement;
            }

            $result[] = $entreprise;
        }

        return $result;
    }
}
