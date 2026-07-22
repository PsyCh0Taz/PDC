<?php
// ============================================================
// PDC — Classe LDAP (récupération de l'organisation)
// ============================================================

class LDAP {

	/**
	 * Echappe une valeur pour un filtre LDAP (compat PHP 5.4).
	 */
	private static function escapeFilterValue($value) {
		$value = (string)$value;
		return str_replace(
			array('\\', '*', '(', ')', chr(0)),
			array('\\5c', '\\2a', '\\28', '\\29', '\\00'),
			$value
		);
	}

	/**
	 * Ouvre une connexion LDAP et configure les options de base.
	 */
	private static function openConnection() {
		$ldap = @ldap_connect(LDAP_HOST, LDAP_PORT);
		if (!$ldap) {
			throw new Exception('Impossible de se connecter au serveur LDAP.');
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		return $ldap;
	}

	/**
	 * Bind LDAP avec compte technique, ou anonyme si LDAP_USER_DN est vide.
	 */
	private static function bindAccount($ldap) {
		$serviceDn = trim((string)LDAP_USER_DN);
		if ($serviceDn === '') {
			return @ldap_bind($ldap);
		}
		return @ldap_bind($ldap, $serviceDn, LDAP_USER_DN_PASS);
	}

	/**
	 * Retourne une connexion LDAP déjà bindée (technique/anonyme).
	 */
	private static function connectAsAccount() {
		$ldap = self::openConnection();
		if (!self::bindAccount($ldap)) {
			$err = @ldap_error($ldap);
			@ldap_unbind($ldap);
			throw new Exception('Impossible de s\'authentifier sur le serveur LDAP' . ($err ? ' (' . $err . ')' : '') . '.');
		}
		return $ldap;
	}

	/**
	 * Authentifie un utilisateur LDAP et retourne ses informations,
	 * ou false si login/mot de passe invalide.
	 */
	public static function authenticateUser($username, $password) {
		$ldap = self::openConnection();

		if (!self::bindAccount($ldap)) {
			$err = @ldap_error($ldap);
			@ldap_unbind($ldap);
			throw new Exception('Impossible de s\'authentifier sur le serveur LDAP' . ($err ? ' (' . $err . ')' : '') . '.');
		}

		$safeUser = self::escapeFilterValue($username);
		$filter = '(|(uid=' . $safeUser . ')(sAMAccountName=' . $safeUser . '))';
		$search = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('dn', 'cn', 'mail', 'displayName', 'ou'));

		if (!$search) {
			@ldap_unbind($ldap);
			return false;
		}

		$entries = @ldap_get_entries($ldap, $search);
		if (!is_array($entries) || empty($entries['count'])) {
			@ldap_unbind($ldap);
			return false;
		}

		$userDn = $entries[0]['dn'];
		$userBound = @ldap_bind($ldap, $userDn, $password);
		if (!$userBound) {
			@ldap_unbind($ldap);
			return false;
		}

		$result = array(
			'username' => $username,
			'dn' => $userDn,
			'displayname' => isset($entries[0]['displayname'][0]) ? $entries[0]['displayname'][0] : $username,
			'mail' => isset($entries[0]['mail'][0]) ? $entries[0]['mail'][0] : '',
		);

		@ldap_unbind($ldap);
		return $result;
	}

	/**
	 * Vérifie les identifiants d'un utilisateur à partir d'un DN déjà connu.
	 * Le DN doit provenir d'une source de confiance, telle que la base locale.
	 */
	public static function bindUserDn($dn, $password) {
		$dn = trim((string)$dn);
		if ($dn === '' || $password === '') {
			return false;
		}

		$ldap = self::openConnection();
		$authenticated = @ldap_bind($ldap, $dn, $password);
		@ldap_unbind($ldap);

		return (bool)$authenticated;
	}

	/**
	 * Retrouve un utilisateur LDAP par son identifiant exact.
	 */
	public static function findUserByUsername($username) {
		$username = trim((string)$username);
		if ($username === '') return false;

		$ldap = self::connectAsAccount();
		$safe = self::escapeFilterValue($username);
		$filter = '(&(objectClass=person)(|(uid=' . $safe . ')(cn=' . $safe . ')(sAMAccountName=' . $safe . ')))';
		$search = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('dn', 'uid', 'sAMAccountName', 'displayName', 'mail'), 0, 2);
		if (!$search) {
			@ldap_unbind($ldap);
			return false;
		}

		$entries = @ldap_get_entries($ldap, $search);
		@ldap_unbind($ldap);
		if (!is_array($entries) || (int)$entries['count'] !== 1 || empty($entries[0]['dn'])) {
			return false;
		}

		$entry = $entries[0];
		return array(
			'username' => $username,
			'dn' => $entry['dn'],
			'displayname' => !empty($entry['displayname'][0]) ? $entry['displayname'][0] : $username,
			'email' => !empty($entry['mail'][0]) ? $entry['mail'][0] : '',
		);
	}

	/**
	 * Recherche des utilisateurs LDAP par login, nom ou e-mail.
	 */
	public static function searchUsers($query, $limit = 20) {
		$query = trim((string)$query);
		if ($query === '') {
			return array();
		}

		try {
			$ldap = self::connectAsAccount();
		} catch (Exception $e) {
			return array();
		}

		$safe = self::escapeFilterValue($query);
		$filter = '(&(objectClass=person)(|(uid=*' . $safe . '*)(sAMAccountName=*' . $safe . '*)(cn=*' . $safe . '*)(displayName=*' . $safe . '*)(mail=*' . $safe . '*)))';
		$search = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('dn', 'cn', 'uid', 'sAMAccountName', 'displayName', 'mail'), 0, (int)$limit);
		if (!$search) {
			@ldap_unbind($ldap);
			return array();
		}

		$entries = @ldap_get_entries($ldap, $search);
		@ldap_unbind($ldap);

		$users = array();
		if (!is_array($entries) || empty($entries['count'])) {
			return $users;
		}

		for ($i = 0; $i < $entries['count']; $i++) {
			$e = $entries[$i];
			$uid = '';
			if (!empty($e['uid'][0])) {
				$uid = $e['uid'][0];
			} elseif (!empty($e['samaccountname'][0])) {
				$uid = $e['samaccountname'][0];
			}

			if ($uid === '' || empty($e['dn'])) {
				continue;
			}

			$users[] = array(
				'username' => $uid,
				'dn' => $e['dn'],
				'displayname' => !empty($e['displayname'][0]) ? $e['displayname'][0] : (!empty($e['cn'][0]) ? $e['cn'][0] : $uid),
				'email' => !empty($e['mail'][0]) ? $e['mail'][0] : '',
			);
		}

		return $users;
	}

	/**
	 * Recherche libre dans la sous-arborescence d'un DN et retourne tous les attributs.
	 */
	public static function searchFromDn($baseDn, $limit = 100) {
		$baseDn = trim((string)$baseDn);
		if ($baseDn === '') {
			throw new Exception('Le DN de recherche est obligatoire.');
		}
		if (!extension_loaded('ldap')) {
			throw new Exception('Extension LDAP non disponible sur PHP.');
		}

		$ldap = self::connectAsAccount();
		$filter = '(objectClass=*)';
		$search = @ldap_search($ldap, $baseDn, $filter, array(), false, (int)$limit);
		if (!$search) {
			$ldapError = @ldap_error($ldap);
			@ldap_unbind($ldap);
			throw new Exception('Recherche LDAP echouee' . ($ldapError ? ' (' . $ldapError . ')' : '') . '.');
		}

		$entries = @ldap_get_entries($ldap, $search);
		@ldap_unbind($ldap);
		if (!is_array($entries)) {
			throw new Exception('Impossible de lire les resultats LDAP.');
		}

		$count = isset($entries['count']) ? (int)$entries['count'] : 0;
		$rows = array();
		for ($i = 0; $i < $count; $i++) {
			if (!isset($entries[$i])) {
				continue;
			}

			$row = array('dn' => isset($entries[$i]['dn']) ? (string)$entries[$i]['dn'] : '');
			foreach ($entries[$i] as $attribute => $values) {
				if ($attribute === 'count' || $attribute === 'dn' || is_numeric($attribute) || !is_array($values)) {
					continue;
				}
				$attributeValues = array();
				$valueCount = isset($values['count']) ? (int)$values['count'] : 0;
				for ($valueIndex = 0; $valueIndex < $valueCount; $valueIndex++) {
					$attributeValues[] = (string)$values[$valueIndex];
				}
				$row[$attribute] = $attributeValues;
			}
			$rows[] = $row;
		}

		return array(
			'success' => true,
			'message' => $count . ' entree(s) trouvee(s) a partir du DN indique.',
			'base_dn' => $baseDn,
			'filter' => $filter,
			'count' => $count,
			'limit' => (int)$limit,
			'results' => $rows,
		);
	}

	/**
	 * Retourne les OU enfants directes d'un DN.
	 */
	private static function getChildOus($baseDn, $excludeDn = '') {
		$ldap = self::connectAsAccount();

		$filter = '(objectClass=organizationalUnit)';
		$search = @ldap_search($ldap, $baseDn, $filter, array('ou', 'description'), false, 500);
		if (!$search) {
			@ldap_unbind($ldap);
			return array();
		}

		$entries = @ldap_get_entries($ldap, $search);
		@ldap_unbind($ldap);

		$result = array();
		if (!is_array($entries) || empty($entries['count'])) {
			return $result;
		}

		for ($i = 0; $i < $entries['count']; $i++) {
			if (!isset($entries[$i]) || !isset($entries[$i]['count']) || $entries[$i]['count'] <= 0) {
				continue;
			}
			$dn = isset($entries[$i]['dn']) ? $entries[$i]['dn'] : '';
			if ($excludeDn !== '' && $dn === $excludeDn) {
				continue;
			}
			$result[] = array(
				'ou' => isset($entries[$i]['ou'][0]) ? $entries[$i]['ou'][0] : '',
				'dn' => $dn,
				'description' => isset($entries[$i]['description'][0]) ? $entries[$i]['description'][0] : '',
			);
		}

		return $result;
	}

	/**
	 * Diagnostic détaillé de connexion LDAP pour la page setup.
	 */
	public static function runConnectionDiagnostic($filter = '(objectClass=organizationalUnit)', $limit = 500) {
		$steps = array();
		$ldap = null;

		try {
			$steps[] = array('label' => 'Verification extension LDAP', 'status' => 'ok', 'detail' => extension_loaded('ldap') ? 'Extension LDAP chargee.' : '');
			if (!extension_loaded('ldap')) {
				throw new Exception('Extension LDAP non disponible sur PHP.');
			}

			$ldap = self::openConnection();
			$steps[] = array('label' => 'Connexion au serveur LDAP', 'status' => 'ok', 'detail' => 'Connexion ouverte sur ' . LDAP_HOST . ':' . LDAP_PORT . '.');

			$steps[] = array('label' => 'Configuration des options LDAP', 'status' => 'ok', 'detail' => 'Protocol v3 et referrals desactives.');

			$serviceDn = trim((string)LDAP_USER_DN);
			$bindLabel = ($serviceDn === '') ? 'Bind anonyme' : 'Bind avec le compte de service';
			if (!self::bindAccount($ldap)) {
				$ldapError = @ldap_error($ldap);
				throw new Exception('Bind LDAP refuse' . ($ldapError ? ' (' . $ldapError . ')' : '') . '.');
			}
			$steps[] = array('label' => $bindLabel, 'status' => 'ok', 'detail' => ($serviceDn === '') ? 'Bind anonyme valide.' : 'Bind du compte de service valide.');

			$search = @ldap_search($ldap, LDAP_BASE_DN, $filter, array('ou', 'description'), false, (int)$limit);
			if (!$search) {
				$ldapError = @ldap_error($ldap);
				throw new Exception('Recherche LDAP echouee' . ($ldapError ? ' (' . $ldapError . ')' : '') . '.');
			}
			$steps[] = array('label' => 'Recherche des OU', 'status' => 'ok', 'detail' => 'Requete LDAP executee.');

			$entries = @ldap_get_entries($ldap, $search);
			if (!is_array($entries)) {
				throw new Exception('Impossible de lire les resultats LDAP.');
			}

			$count = isset($entries['count']) ? (int)$entries['count'] : 0;
			$rows = array();
			for ($i = 0; $i < $count; $i++) {
				if (!isset($entries[$i])) {
					continue;
				}
				$rows[] = array(
					'ou' => isset($entries[$i]['ou'][0]) ? (string)$entries[$i]['ou'][0] : '',
					'dn' => isset($entries[$i]['dn']) ? (string)$entries[$i]['dn'] : '',
					'description' => isset($entries[$i]['description'][0]) ? (string)$entries[$i]['description'][0] : '',
				);
			}

			$steps[] = array('label' => 'Lecture des resultats', 'status' => 'ok', 'detail' => $count . ' entree(s) LDAP retournee(s).');

			// Récupérer les informations du compte de service (ldap_user_dn)
			$serviceDnFields = array();
			$serviceDn = trim((string)LDAP_USER_DN);
			if ($serviceDn !== '') {
				$search = @ldap_read($ldap, $serviceDn, '(objectClass=*)', array(), false);
				if ($search) {
					$entries = @ldap_get_entries($ldap, $search);
					if (is_array($entries) && isset($entries[0])) {
						// Récupérer tous les attributs sauf 'count' et les index numériques
						foreach ($entries[0] as $key => $value) {
							if ($key !== 'count' && !is_numeric($key) && is_array($value)) {
								// Prendre le premier élément du tableau si c'est un tableau
								if (isset($value[0])) {
									$serviceDnFields[$key] = $value[0];
								}
							}
						}
					}
				}
				$steps[] = array('label' => 'Lecture du compte de service', 'status' => 'ok', 'detail' => 'Attributs du compte de service recuperes.');
			}

			@ldap_unbind($ldap);

			return array(
				'success' => true,
				'message' => 'Connexion LDAP reussie.',
				'count' => $count,
				'base_dn' => LDAP_BASE_DN,
				'filter' => $filter,
				'results' => $rows,
				'service_account' => $serviceDnFields,
				'steps' => $steps,
			);
		} catch (Exception $e) {
			if ($ldap) {
				@ldap_unbind($ldap);
			}

			$steps[] = array('label' => 'Erreur', 'status' => 'error', 'detail' => $e->getMessage());

			return array(
				'success' => false,
				'message' => 'Echec connexion LDAP: ' . $e->getMessage(),
				'steps' => $steps,
			);
		}
	}
}
