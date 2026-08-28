<?php
require_once __DIR__ . '/../includes/bootstrap.php';

Auth::startSession();

// Identifiants locaux de la page setup (a modifier plus tard).
const SETUP_LOCAL_USER = 'setup';
const SETUP_LOCAL_PASS = 'setup';

$setupSessionKey = 'setup_local_auth';
$error = '';
$dbResult = null;
$ldapResult = null;
$ldapDnSearchResult = null;
$ldapSearchDn = LDAP_BASE_DN;
$configResult = null;
$configFile = __DIR__ . '/../config/config.php';

function setupEscapePhpString($value) {
    return str_replace(array('\\', "'"), array('\\\\', "\\'"), (string)$value);
}

function setupReplaceDefineString($content, $constantName, $value) {
    $pattern = "/define\\(\\s*'" . preg_quote($constantName, '/') . "'\\s*,\\s*[^\\n]*\\);/";
    $replacement = "define('" . $constantName . "',     '" . setupEscapePhpString($value) . "');";
    return preg_replace($pattern, $replacement, $content, 1);
}

function setupReplaceDefineInt($content, $constantName, $value) {
    $pattern = "/define\\(\\s*'" . preg_quote($constantName, '/') . "'\\s*,\\s*\\d+\\s*\\);/";
    $replacement = "define('" . $constantName . "',        " . (int)$value . ');';
    return preg_replace($pattern, $replacement, $content, 1);
}

function setupReplaceDefineBool($content, $constantName, $value) {
    $pattern = "/define\\(\\s*'" . preg_quote($constantName, '/') . "'\\s*,\\s*(true|false)\\s*\\);/i";
    $replacement = "define('" . $constantName . "',     " . ($value ? 'true' : 'false') . ');';
    return preg_replace($pattern, $replacement, $content, 1);
}

function setupExpectedDatabaseSchema() {
    return array(
        'pdc_hierarchie' => array('id', 'nom', 'id_parent', 'ordre', 'actif'),
        'pdc_domaines' => array('id', 'hierarchie_id', 'nom', 'ordre'),
        'pdc_projets' => array('id', 'domaine_id', 'titre', 'date_debut', 'date_fin', 'ordre'),
        'pdc_projet_gradients' => array('id', 'projet_id', 'date_gradient', 'couleur', 'libelle'),
        'pdc_projet_jalons' => array('id', 'projet_id', 'date_jalon', 'couleur', 'libelle', 'jalon_reference_id'),
        'pdc_utilisateurs' => array('username', 'displayname', 'dn', 'email', 'niveau_id'),
        'pdc_utilisateurs_roles' => array('username', 'role_dn', 'role'),
        'pdc_parametres' => array('cle', 'valeur'),
        'pdc_journal_modifications' => array('username', 'ip', 'action', 'entite', 'entite_id', 'description', 'date_heure'),
        'pdc_journal_connexions' => array('username', 'ip', 'via_partage', 'share_token', 'date_heure'),
    );
}

function setupSchemaDefaultValue($value) {
    if ($value === null) {
        return null;
    }
    return strtoupper(trim((string)$value)) === 'CURRENT_TIMESTAMP' ? 'CURRENT_TIMESTAMP' : (string)$value;
}

function setupParseColumnDefinition($definition) {
    $definition = trim(rtrim($definition, ','));
    if (!preg_match('/^`([^`]+)`\s+(.+)$/s', $definition, $matches)) {
        return null;
    }

    $details = $matches[2];
    preg_match('/^([a-z]+(?:\([^)]*\))?(?:\s+unsigned)?)/i', $details, $typeMatch);
    $default = null;
    if (preg_match('/\sDEFAULT\s+(CURRENT_TIMESTAMP|NULL|\'([^\']*)\')/i', $details, $defaultMatch)) {
        $default = strtoupper($defaultMatch[1]) === 'NULL'
            ? null
            : (isset($defaultMatch[2]) ? $defaultMatch[2] : strtoupper($defaultMatch[1]));
    }

    return array(
        'name' => $matches[1],
        'type' => strtolower(isset($typeMatch[1]) ? preg_replace('/\s+/', ' ', $typeMatch[1]) : ''),
        'nullable' => stripos($details, 'NOT NULL') === false,
        'default' => setupSchemaDefaultValue($default),
        'auto_increment' => stripos($details, 'AUTO_INCREMENT') !== false,
    );
}

function setupExpectedDatabaseDefinition() {
    $sql = @file_get_contents(__DIR__ . '/../pdc.sql');
    if ($sql === false) {
        throw new Exception('Fichier de reference pdc.sql introuvable.');
    }

    $schema = array();
    preg_match_all('/CREATE TABLE\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE=([^\s;]+)/is', $sql, $tables, PREG_SET_ORDER);
    foreach ($tables as $table) {
        $schema[$table[1]] = array('engine' => strtolower($table[3]), 'columns' => array(), 'indexes' => array(), 'foreign_keys' => array());
        foreach (preg_split('/\r?\n/', $table[2]) as $line) {
            $column = setupParseColumnDefinition($line);
            if ($column !== null) {
                $schema[$table[1]]['columns'][$column['name']] = $column;
            }
        }
    }

    // Les liens de partage sont optionnels et ne font pas partie du controle applicatif.
    unset($schema['pdc_share_links']);

    preg_match_all('/ALTER TABLE\s+`([^`]+)`\s+(.*?);/is', $sql, $alters, PREG_SET_ORDER);
    foreach ($alters as $alter) {
        $tableName = $alter[1];
        if (!isset($schema[$tableName])) {
            continue;
        }
        $body = $alter[2];
        preg_match_all('/MODIFY\s+(`[^`]+`\s+[^,;]+)/i', $body, $modifies, PREG_SET_ORDER);
        foreach ($modifies as $modify) {
            $column = setupParseColumnDefinition($modify[1]);
            if ($column !== null) {
                $schema[$tableName]['columns'][$column['name']] = $column;
            }
        }
        preg_match_all('/ADD\s+(PRIMARY KEY|UNIQUE KEY\s+`([^`]+)`|KEY\s+`([^`]+)`)\s*\(([^)]+)\)/i', $body, $indexes, PREG_SET_ORDER);
        foreach ($indexes as $index) {
            $kind = strtoupper($index[1]) === 'PRIMARY KEY' ? 'PRIMARY' : (stripos($index[1], 'UNIQUE') === 0 ? 'UNIQUE' : 'INDEX');
            $name = $kind === 'PRIMARY' ? 'PRIMARY' : ($index[2] !== '' ? $index[2] : $index[3]);
            $columns = preg_replace('/[`\s]/', '', $index[4]);
            $schema[$tableName]['indexes'][$kind . '|' . $name . '|' . $columns] = true;
        }
        preg_match_all('/ADD\s+CONSTRAINT\s+`([^`]+)`\s+FOREIGN KEY\s*\(([^)]+)\)\s+REFERENCES\s+`([^`]+)`\s*\(([^)]+)\)(?:\s+ON DELETE\s+([A-Z ]+?))?(?:\s+ON UPDATE\s+([A-Z ]+?))?(?:\s|$)/i', $body . ' ', $foreignKeys, PREG_SET_ORDER);
        foreach ($foreignKeys as $foreignKey) {
            $deleteRule = isset($foreignKey[5]) && trim($foreignKey[5]) !== '' ? strtoupper(trim($foreignKey[5])) : 'RESTRICT';
            $updateRule = isset($foreignKey[6]) && trim($foreignKey[6]) !== '' ? strtoupper(trim($foreignKey[6])) : 'RESTRICT';
            $key = $foreignKey[1] . '|' . preg_replace('/[`\s]/', '', $foreignKey[2]) . '|' . $foreignKey[3] . '|' . preg_replace('/[`\s]/', '', $foreignKey[4]) . '|' . $deleteRule . '|' . $updateRule;
            $schema[$tableName]['foreign_keys'][$key] = true;
        }
    }

    return $schema;
}

$configValues = array(
    'db_host' => DB_HOST,
    'db_name' => DB_NAME,
    'db_user' => DB_USER,
    'db_pass' => DB_PASS,
    'db_charset' => DB_CHARSET,
    'offline_mode' => OFFLINE_MODE ? '1' : '0',
    'annuaire_search_url' => ANNUAIRE_SEARCH_URL,
    'ldap_host' => LDAP_HOST,
    'ldap_port' => (string)LDAP_PORT,
    'ldap_base_dn' => LDAP_BASE_DN,
    'ldap_user_dn' => LDAP_USER_DN,
    'ldap_user_dn_pass' => LDAP_USER_DN_PASS,
    'app_url' => APP_URL,
    'session_lifetime' => (string)SESSION_LIFETIME,
);

if (isset($_POST['action']) && $_POST['action'] === 'setup_logout') {
    unset($_SESSION[$setupSessionKey]);
    header('Location: ' . APP_URL . '/setup.php');
    exit;
}

if (empty($_SESSION[$setupSessionKey])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup_login') {
        $username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

        if ($username === SETUP_LOCAL_USER && $password === SETUP_LOCAL_PASS) {
            $_SESSION[$setupSessionKey] = true;
            header('Location: ' . APP_URL . '/setup/');
            exit;
        }

        $error = 'Identifiants de configuration invalides.';
    }

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="color-scheme" content="light dark">
        <script>
            (function () {
                var theme = null;
                try { theme = localStorage.getItem('pdc.theme'); } catch (e) {}
                if (theme !== 'light' && theme !== 'dark') {
                    theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-bs-theme', theme);
            }());
        </script>
        <title>Setup - Connexion</title>
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/pdc.css">
    </head>
    <body class="pdc-login-page">
    <button type="button" class="btn pdc-theme-toggle pdc-theme-toggle-floating" data-pdc-theme-toggle aria-label="Activer le thème sombre" aria-pressed="false">
        <i class="fa-solid fa-moon" aria-hidden="true"></i>
        <span class="pdc-theme-toggle-label">Thème sombre</span>
    </button>
    <div class="pdc-login-shell">
        <div class="pdc-login-container">
            <div class="pdc-login-logo">
                <div class="pdc-login-logo-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <h1>Setup</h1>
                <p class="pdc-login-subtitle">Page de configuration technique</p>
            </div>

            <div class="pdc-login-panel">
                <div class="pdc-login-panel-head">
                    <h2>Connexion locale</h2>
                    <p>Acces reserve a la configuration.</p>
                </div>

                <div class="pdc-login-panel-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="" autocomplete="off" class="pdc-login-form">
                        <input type="hidden" name="action" value="setup_login">
                        <div class="mb-3">
                            <label class="form-label" for="username"><i class="fa-regular fa-user"></i> Utilisateur local</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="setup" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password"><i class="fa-solid fa-lock"></i> Mot de passe local</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="setup" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fa-solid fa-right-to-bracket"></i> Ouvrir la page setup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo APP_URL; ?>/assets/theme.js"></script>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_config') {
        $configValues = array(
            'db_host' => isset($_POST['db_host']) ? trim((string)$_POST['db_host']) : '',
            'db_name' => isset($_POST['db_name']) ? trim((string)$_POST['db_name']) : '',
            'db_user' => isset($_POST['db_user']) ? trim((string)$_POST['db_user']) : '',
            'db_pass' => isset($_POST['db_pass']) ? (string)$_POST['db_pass'] : '',
            'db_charset' => isset($_POST['db_charset']) ? trim((string)$_POST['db_charset']) : '',
            'offline_mode' => isset($_POST['offline_mode']) ? '1' : '0',
            'annuaire_search_url' => isset($_POST['annuaire_search_url']) ? trim((string)$_POST['annuaire_search_url']) : '',
            'ldap_host' => isset($_POST['ldap_host']) ? trim((string)$_POST['ldap_host']) : '',
            'ldap_port' => isset($_POST['ldap_port']) ? trim((string)$_POST['ldap_port']) : '',
            'ldap_base_dn' => isset($_POST['ldap_base_dn']) ? trim((string)$_POST['ldap_base_dn']) : '',
            'ldap_user_dn' => isset($_POST['ldap_user_dn']) ? trim((string)$_POST['ldap_user_dn']) : '',
            'ldap_user_dn_pass' => isset($_POST['ldap_user_dn_pass']) ? (string)$_POST['ldap_user_dn_pass'] : '',
            'app_url' => isset($_POST['app_url']) ? trim((string)$_POST['app_url']) : '',
            'session_lifetime' => isset($_POST['session_lifetime']) ? trim((string)$_POST['session_lifetime']) : '',
        );

        try {
            if (!is_file($configFile) || !is_readable($configFile) || !is_writable($configFile)) {
                throw new Exception('Le fichier config/config.php est introuvable ou non accessible en ecriture.');
            }

            if ($configValues['db_host'] === '' || $configValues['db_name'] === '' || $configValues['db_user'] === '') {
                throw new Exception('DB_HOST, DB_NAME et DB_USER sont obligatoires.');
            }

            if ($configValues['ldap_host'] === '' || $configValues['ldap_base_dn'] === '') {
                throw new Exception('LDAP_HOST et LDAP_BASE_DN sont obligatoires.');
            }

            if ($configValues['annuaire_search_url'] === '' || filter_var($configValues['annuaire_search_url'], FILTER_VALIDATE_URL) === false) {
                throw new Exception('ANNUAIRE_SEARCH_URL doit etre une URL valide.');
            }
            $annuaireScheme = strtolower((string)parse_url($configValues['annuaire_search_url'], PHP_URL_SCHEME));
            if ($annuaireScheme !== 'http' && $annuaireScheme !== 'https') {
                throw new Exception('ANNUAIRE_SEARCH_URL doit utiliser le protocole HTTP ou HTTPS.');
            }

            if (!ctype_digit($configValues['ldap_port'])) {
                throw new Exception('LDAP_PORT doit etre un entier valide.');
            }
            $ldapPort = (int)$configValues['ldap_port'];
            if ($ldapPort < 1 || $ldapPort > 65535) {
                throw new Exception('LDAP_PORT doit etre compris entre 1 et 65535.');
            }

            if (!ctype_digit($configValues['session_lifetime'])) {
                throw new Exception('SESSION_LIFETIME doit etre un entier positif.');
            }
            $sessionLifetime = (int)$configValues['session_lifetime'];
            if ($sessionLifetime < 60) {
                throw new Exception('SESSION_LIFETIME doit etre superieur ou egal a 60 secondes.');
            }

            $content = file_get_contents($configFile);
            if ($content === false) {
                throw new Exception('Impossible de lire config/config.php.');
            }

            $updated = $content;
            $updated = setupReplaceDefineString($updated, 'DB_HOST', $configValues['db_host']);
            $updated = setupReplaceDefineString($updated, 'DB_NAME', $configValues['db_name']);
            $updated = setupReplaceDefineString($updated, 'DB_USER', $configValues['db_user']);
            $updated = setupReplaceDefineString($updated, 'DB_PASS', $configValues['db_pass']);
            $updated = setupReplaceDefineString($updated, 'DB_CHARSET', $configValues['db_charset']);
            $updated = setupReplaceDefineBool($updated, 'OFFLINE_MODE', $configValues['offline_mode'] === '1');
            $updated = setupReplaceDefineString($updated, 'ANNUAIRE_SEARCH_URL', $configValues['annuaire_search_url']);
            $updated = setupReplaceDefineString($updated, 'LDAP_HOST', $configValues['ldap_host']);
            $updated = setupReplaceDefineInt($updated, 'LDAP_PORT', $ldapPort);
            $updated = setupReplaceDefineString($updated, 'LDAP_BASE_DN', $configValues['ldap_base_dn']);
            $updated = setupReplaceDefineString($updated, 'LDAP_USER_DN', $configValues['ldap_user_dn']);
            $updated = setupReplaceDefineString($updated, 'LDAP_USER_DN_PASS', $configValues['ldap_user_dn_pass']);
            $updated = setupReplaceDefineString($updated, 'APP_URL', $configValues['app_url']);
            $updated = setupReplaceDefineInt($updated, 'SESSION_LIFETIME', $sessionLifetime);

            if ($updated === $content) {
                throw new Exception('Aucune modification detectee dans config/config.php.');
            }

            $written = file_put_contents($configFile, $updated, LOCK_EX);
            if ($written === false) {
                throw new Exception('Echec de l\'ecriture dans config/config.php.');
            }

            $configResult = array(
                'success' => true,
                'message' => 'Parametres enregistres dans config/config.php.',
            );
        } catch (Exception $e) {
            $configResult = array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }

    if ($_POST['action'] === 'test_db') {
        $steps = array();
        $currentStep = 'Initialisation du test base';
        $sqlTestQuery = 'SELECT 1 AS ok, NOW() AS server_time, DATABASE() AS current_db';

        try {
            $currentStep = 'Verification extension PDO MySQL';
            if (!extension_loaded('pdo_mysql')) {
                throw new Exception('Extension pdo_mysql non disponible sur PHP.');
            }
            $steps[] = array(
                'label' => $currentStep,
                'status' => 'ok',
                'detail' => 'Extension pdo_mysql chargee.',
            );

            $currentStep = 'Construction de la chaine DSN';
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $steps[] = array(
                'label' => $currentStep,
                'status' => 'ok',
                'detail' => 'DSN construit pour l\'hote ' . DB_HOST . '.',
            );

            $currentStep = 'Ouverture de la connexion PDO';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
            $steps[] = array(
                'label' => $currentStep,
                'status' => 'ok',
                'detail' => 'Connexion etablie avec succes.',
            );

            $currentStep = 'Execution de la requete de verification';
            $stmt = $pdo->query($sqlTestQuery);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception('La requete de test n\'a retourne aucun resultat.');
            }
            $steps[] = array(
                'label' => $currentStep,
                'status' => 'ok',
                'detail' => 'Requete executee et resultat recupere.',
            );

            $currentStep = 'Controle du schema des tables de donnees';
            $expectedSchema = setupExpectedDatabaseDefinition();
            $schemaStmt = $pdo->prepare(
                'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS '
                . 'WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, ORDINAL_POSITION'
            );
            $schemaStmt->execute(array(DB_NAME));
            $actualSchema = array();
            foreach ($schemaStmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
                $tableName = $column['TABLE_NAME'];
                if (!isset($actualSchema[$tableName])) {
                    $actualSchema[$tableName] = array();
                }
                $actualSchema[$tableName][$column['COLUMN_NAME']] = array(
                    'type' => strtolower(preg_replace('/\s+/', ' ', $column['COLUMN_TYPE'])),
                    'nullable' => $column['IS_NULLABLE'] === 'YES',
                    'default' => setupSchemaDefaultValue($column['COLUMN_DEFAULT']),
                    'auto_increment' => stripos($column['EXTRA'], 'auto_increment') !== false,
                );
            }

            $tableStmt = $pdo->prepare('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?');
            $tableStmt->execute(array(DB_NAME));
            $actualTables = array();
            foreach ($tableStmt->fetchAll(PDO::FETCH_ASSOC) as $table) {
                $actualTables[$table['TABLE_NAME']] = strtolower((string)$table['ENGINE']);
            }

            $indexStmt = $pdo->prepare('SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX');
            $indexStmt->execute(array(DB_NAME));
            $actualIndexes = array();
            foreach ($indexStmt->fetchAll(PDO::FETCH_ASSOC) as $index) {
                $tableName = $index['TABLE_NAME'];
                $indexName = $index['INDEX_NAME'];
                if (!isset($actualIndexes[$tableName][$indexName])) {
                    $actualIndexes[$tableName][$indexName] = array('unique' => (int)$index['NON_UNIQUE'] === 0, 'columns' => array());
                }
                $actualIndexes[$tableName][$indexName]['columns'][] = $index['COLUMN_NAME'];
            }

            $foreignKeyStmt = $pdo->prepare('SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, r.DELETE_RULE, r.UPDATE_RULE FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND r.TABLE_NAME = k.TABLE_NAME WHERE k.CONSTRAINT_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION');
            $foreignKeyStmt->execute(array(DB_NAME));
            $actualForeignKeys = array();
            foreach ($foreignKeyStmt->fetchAll(PDO::FETCH_ASSOC) as $foreignKey) {
                $tableName = $foreignKey['TABLE_NAME'];
                $keyName = $foreignKey['CONSTRAINT_NAME'];
                if (!isset($actualForeignKeys[$tableName][$keyName])) {
                    $actualForeignKeys[$tableName][$keyName] = array('columns' => array(), 'ref_table' => $foreignKey['REFERENCED_TABLE_NAME'], 'ref_columns' => array(), 'delete' => strtoupper($foreignKey['DELETE_RULE']), 'update' => strtoupper($foreignKey['UPDATE_RULE']));
                }
                $actualForeignKeys[$tableName][$keyName]['columns'][] = $foreignKey['COLUMN_NAME'];
                $actualForeignKeys[$tableName][$keyName]['ref_columns'][] = $foreignKey['REFERENCED_COLUMN_NAME'];
            }

            $missingTables = array();
            $problems = array();
            foreach ($expectedSchema as $tableName => $expectedTable) {
                if (!isset($actualTables[$tableName])) {
                    $missingTables[] = $tableName;
                    continue;
                }
                if ($actualTables[$tableName] !== $expectedTable['engine']) {
                    $problems[] = $tableName . ' (moteur attendu ' . $expectedTable['engine'] . ', obtenu ' . $actualTables[$tableName] . ')';
                }
                foreach ($expectedTable['columns'] as $columnName => $expectedColumn) {
                    if (!isset($actualSchema[$tableName][$columnName])) {
                        $problems[] = $tableName . '.' . $columnName . ' (colonne absente)';
                        continue;
                    }
                    $actualColumn = $actualSchema[$tableName][$columnName];
                    foreach (array('type', 'nullable', 'default', 'auto_increment') as $property) {
                        if ($actualColumn[$property] !== $expectedColumn[$property]) {
                            $problems[] = $tableName . '.' . $columnName . ' (' . $property . ' attendu ' . var_export($expectedColumn[$property], true) . ', obtenu ' . var_export($actualColumn[$property], true) . ')';
                        }
                    }
                }
                $actualIndexKeys = array();
                if (isset($actualIndexes[$tableName])) {
                    foreach ($actualIndexes[$tableName] as $indexName => $index) {
                        $kind = $indexName === 'PRIMARY' ? 'PRIMARY' : ($index['unique'] ? 'UNIQUE' : 'INDEX');
                        $actualIndexKeys[$kind . '|' . $indexName . '|' . implode(',', $index['columns'])] = true;
                    }
                }
                foreach ($expectedTable['indexes'] as $indexKey => $unused) {
                    if (!isset($actualIndexKeys[$indexKey])) {
                        $problems[] = $tableName . ' (index absent : ' . $indexKey . ')';
                    }
                }
                $actualForeignKeyKeys = array();
                if (isset($actualForeignKeys[$tableName])) {
                    foreach ($actualForeignKeys[$tableName] as $foreignKeyName => $foreignKey) {
                        $actualForeignKeyKeys[$foreignKeyName . '|' . implode(',', $foreignKey['columns']) . '|' . $foreignKey['ref_table'] . '|' . implode(',', $foreignKey['ref_columns']) . '|' . $foreignKey['delete'] . '|' . $foreignKey['update']] = true;
                    }
                }
                foreach ($expectedTable['foreign_keys'] as $foreignKeyKey => $unused) {
                    if (!isset($actualForeignKeyKeys[$foreignKeyKey])) {
                        $problems[] = $tableName . ' (cle etrangere absente : ' . $foreignKeyKey . ')';
                    }
                }
            }

            if (!empty($missingTables)) {
                $problems[] = 'tables absentes : ' . implode(', ', $missingTables);
            }
            if (!empty($problems)) {
                throw new Exception('Schema non conforme a pdc.sql - ' . implode(' ; ', $problems) . '.');
            }

            $steps[] = array(
                'label' => $currentStep,
                'status' => 'ok',
                'detail' => count($expectedSchema) . ' tables controlees : colonnes, types, valeurs par defaut, auto-increments, moteur, index et cles etrangeres conformes.',
            );

            $dbResult = array(
                'success' => true,
                'message' => 'Connexion a la base et controle du schema reussis.',
                'details' => $row,
                'query' => $sqlTestQuery,
                'steps' => $steps,
            );
        } catch (Exception $e) {
            $steps[] = array(
                'label' => $currentStep,
                'status' => 'error',
                'detail' => $e->getMessage(),
            );

            $dbResult = array(
                'success' => false,
                'message' => 'Echec connexion base: ' . $e->getMessage(),
                'steps' => $steps,
            );
        }
    }

    if ($_POST['action'] === 'test_ldap') {
        $ldapFilter = '(objectClass=organizationalUnit)';

        $ldapResult = LDAP::runConnectionDiagnostic($ldapFilter, 500);
    }

    if ($_POST['action'] === 'search_ldap_dn') {
        $ldapSearchDn = isset($_POST['ldap_search_dn']) ? trim((string)$_POST['ldap_search_dn']) : '';
        try {
            $ldapDnSearchResult = LDAP::searchFromDn($ldapSearchDn, 100);
        } catch (Exception $e) {
            $ldapDnSearchResult = array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <script>
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('pdc.theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        }());
    </script>
    <title>Setup - Tests techniques</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/pdc.css">
</head>
<body class="pdc-setup-page">
<button type="button" class="btn pdc-theme-toggle pdc-theme-toggle-floating" data-pdc-theme-toggle aria-label="Activer le thème sombre" aria-pressed="false">
    <i class="fa-solid fa-moon" aria-hidden="true"></i>
    <span class="pdc-theme-toggle-label">Thème sombre</span>
</button>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 m-0"><i class="fa-solid fa-screwdriver-wrench"></i> Setup - Connexions</h1>
        <form method="post" action="" class="m-0">
            <input type="hidden" name="action" value="setup_logout">
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Fermer
            </button>
        </form>
    </div>

    <p class="text-muted mb-4">Tests separes pour la base de donnees et pour LDAP.</p>

    <?php if ($configResult !== null): ?>
    <div class="alert <?php echo $configResult['success'] ? 'alert-success' : 'alert-danger'; ?>" role="alert">
        <?php echo htmlspecialchars($configResult['message'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>

    <?php
    $activeSetupTab = 'config';
    if ($dbResult !== null) {
        $activeSetupTab = 'database';
    } elseif ($ldapResult !== null || $ldapDnSearchResult !== null) {
        $activeSetupTab = 'ldap';
    }
    ?>
    <ul class="nav nav-tabs mb-4" id="setupTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($activeSetupTab === 'config') ? 'active' : ''; ?>" id="config-tab" data-bs-toggle="tab" data-bs-target="#config-pane" type="button" role="tab" aria-controls="config-pane" aria-selected="<?php echo ($activeSetupTab === 'config') ? 'true' : 'false'; ?>">
                <i class="fa-solid fa-sliders"></i> Parametres
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($activeSetupTab === 'database') ? 'active' : ''; ?>" id="database-tab" data-bs-toggle="tab" data-bs-target="#database-pane" type="button" role="tab" aria-controls="database-pane" aria-selected="<?php echo ($activeSetupTab === 'database') ? 'true' : 'false'; ?>">
                <i class="fa-solid fa-database"></i> Base de donnees
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($activeSetupTab === 'ldap') ? 'active' : ''; ?>" id="ldap-tab" data-bs-toggle="tab" data-bs-target="#ldap-pane" type="button" role="tab" aria-controls="ldap-pane" aria-selected="<?php echo ($activeSetupTab === 'ldap') ? 'true' : 'false'; ?>">
                <i class="fa-solid fa-network-wired"></i> LDAP
            </button>
        </li>
    </ul>

    <div class="tab-content" id="setupTabsContent">
    <div class="tab-pane fade <?php echo ($activeSetupTab === 'config') ? 'show active' : ''; ?>" id="config-pane" role="tabpanel" aria-labelledby="config-tab" tabindex="0">
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <strong><i class="fa-solid fa-sliders"></i> Parametres de configuration (config/config.php)</strong>
        </div>
        <div class="card-body">
            <form method="post" action="" class="row g-3">
                <input type="hidden" name="action" value="save_config">

                <div class="col-12 col-md-4">
                    <label class="form-label" for="db_host">DB_HOST</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="<?php echo htmlspecialchars($configValues['db_host'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="db_name">DB_NAME</label>
                    <input type="text" class="form-control" id="db_name" name="db_name" value="<?php echo htmlspecialchars($configValues['db_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="db_charset">DB_CHARSET</label>
                    <input type="text" class="form-control" id="db_charset" name="db_charset" value="<?php echo htmlspecialchars($configValues['db_charset'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="db_user">DB_USER</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" value="<?php echo htmlspecialchars($configValues['db_user'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="db_pass">DB_PASS</label>
                    <input type="text" class="form-control" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($configValues['db_pass'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12">
                    <label class="form-label" for="annuaire_search_url">ANNUAIRE_SEARCH_URL</label>
                    <input type="url" class="form-control" id="annuaire_search_url" name="annuaire_search_url" value="<?php echo htmlspecialchars($configValues['annuaire_search_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://example.test/annuaire.json" required>
                    <div class="form-text">URL HTTP(S) du service d'annuaire externe utilise pour rechercher les utilisateurs.</div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="ldap_host">LDAP_HOST</label>
                    <input type="text" class="form-control" id="ldap_host" name="ldap_host" value="<?php echo htmlspecialchars($configValues['ldap_host'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="ldap_port">LDAP_PORT</label>
                    <input type="number" class="form-control" id="ldap_port" name="ldap_port" min="1" max="65535" value="<?php echo htmlspecialchars($configValues['ldap_port'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="ldap_base_dn">LDAP_BASE_DN</label>
                    <input type="text" class="form-control" id="ldap_base_dn" name="ldap_base_dn" value="<?php echo htmlspecialchars($configValues['ldap_base_dn'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="ldap_user_dn">LDAP_USER_DN</label>
                    <input type="text" class="form-control" id="ldap_user_dn" name="ldap_user_dn" value="<?php echo htmlspecialchars($configValues['ldap_user_dn'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="ldap_user_dn_pass">LDAP_USER_DN_PASS</label>
                    <input type="text" class="form-control" id="ldap_user_dn_pass" name="ldap_user_dn_pass" value="<?php echo htmlspecialchars($configValues['ldap_user_dn_pass'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12 col-md-8">
                    <label class="form-label" for="app_url">APP_URL</label>
                    <input type="text" class="form-control" id="app_url" name="app_url" value="<?php echo htmlspecialchars($configValues['app_url'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="session_lifetime">SESSION_LIFETIME (secondes)</label>
                    <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" min="60" value="<?php echo htmlspecialchars($configValues['session_lifetime'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="offline_mode" name="offline_mode" value="1" <?php echo ($configValues['offline_mode'] === '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="offline_mode">OFFLINE_MODE</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-floppy-disk"></i> Enregistrer les parametres
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <div class="tab-pane fade <?php echo ($activeSetupTab === 'database') ? 'show active' : ''; ?>" id="database-pane" role="tabpanel" aria-labelledby="database-tab" tabindex="0">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <strong><i class="fa-solid fa-database"></i> Test connexion base de donnees</strong>
                </div>
                <div class="card-body">
                    <p class="mb-3">Verifie l'acces PDO et execute une requete simple.</p>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="test_db">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plug-circle-check"></i> Tester la base
                        </button>
                    </form>

                    <?php if ($dbResult !== null): ?>
                    <div class="mt-3 alert <?php echo $dbResult['success'] ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                        <?php echo htmlspecialchars($dbResult['message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php if (!empty($dbResult['steps'])): ?>
                    <ol class="list-group list-group-numbered mt-3">
                        <?php foreach ($dbResult['steps'] as $step): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?php echo htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <small><?php echo htmlspecialchars($step['detail'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <span class="badge <?php echo ($step['status'] === 'ok') ? 'text-bg-success' : 'text-bg-danger'; ?> rounded-pill">
                                <?php echo ($step['status'] === 'ok') ? 'OK' : 'ERREUR'; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                    <?php if (!empty($dbResult['details'])): ?>
                    <ul class="list-group list-group-flush mt-2">
                        <li class="list-group-item"><strong>Requete SQL:</strong> <code><?php echo htmlspecialchars((string)$dbResult['query'], ENT_QUOTES, 'UTF-8'); ?></code></li>
                        <li class="list-group-item"><strong>Base active:</strong> <?php echo htmlspecialchars((string)$dbResult['details']['current_db'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <li class="list-group-item"><strong>Heure serveur SQL:</strong> <?php echo htmlspecialchars((string)$dbResult['details']['server_time'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <li class="list-group-item"><strong>Test SELECT 1:</strong> <?php echo (int)$dbResult['details']['ok']; ?></li>
                    </ul>
                    <div class="mt-3">
                        <label class="form-label"><strong>Resultat SQL (JSON)</strong></label>
                        <pre class="pdc-setup-result-json p-3 border rounded mb-0"><?php echo htmlspecialchars(json_encode($dbResult['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

    </div>

    <div class="tab-pane fade <?php echo ($activeSetupTab === 'ldap') ? 'show active' : ''; ?>" id="ldap-pane" role="tabpanel" aria-labelledby="ldap-tab" tabindex="0">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <strong><i class="fa-solid fa-network-wired"></i> Test connexion LDAP</strong>
                </div>
                <div class="card-body">
                    <p class="mb-3">Verifie la connexion/bind LDAP et interroge les OU.</p>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="test_ldap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plug-circle-check"></i> Tester LDAP
                        </button>
                    </form>

                    <?php if ($ldapResult !== null): ?>
                    <div class="mt-3 alert <?php echo $ldapResult['success'] ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                        <?php echo htmlspecialchars($ldapResult['message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php if (!empty($ldapResult['steps'])): ?>
                    <ol class="list-group list-group-numbered mt-3">
                        <?php foreach ($ldapResult['steps'] as $step): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?php echo htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <small><?php echo htmlspecialchars($step['detail'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <span class="badge <?php echo ($step['status'] === 'ok') ? 'text-bg-success' : 'text-bg-danger'; ?> rounded-pill">
                                <?php echo ($step['status'] === 'ok') ? 'OK' : 'ERREUR'; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                    <?php if (!empty($ldapResult['success'])): ?>
                    <ul class="list-group list-group-flush mt-2">
                        <li class="list-group-item"><strong>Base DN:</strong> <?php echo htmlspecialchars((string)$ldapResult['base_dn'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <li class="list-group-item"><strong>Filtre LDAP:</strong> <code><?php echo htmlspecialchars((string)$ldapResult['filter'], ENT_QUOTES, 'UTF-8'); ?></code></li>
                        <li class="list-group-item"><strong>Nombre d'OU trouvees:</strong> <?php echo (int)$ldapResult['count']; ?></li>
                    </ul>
                    <div class="mt-3">
                        <label class="form-label"><strong>Resultat LDAP (JSON)</strong></label>
                        <pre class="pdc-setup-result-json p-3 border rounded mb-0"><?php echo htmlspecialchars(json_encode($ldapResult['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
                    </div>
                    <?php if (!empty($ldapResult['service_account']) || true ): ?>
                    <div class="mt-3">
                        <label class="form-label"><strong>Attributs du compte de service (ldap_user_dn)</strong></label>
                        <pre class="pdc-setup-result-json p-3 border rounded mb-0"><?php echo htmlspecialchars(json_encode($ldapResult['service_account'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <strong><i class="fa-solid fa-magnifying-glass"></i> Recherche libre depuis un DN</strong>
                </div>
                <div class="card-body">
                    <p class="mb-3">Recherche jusqu'a 100 entrees dans toute la sous-arborescence du DN indique.</p>
                    <form method="post" action="" class="row g-3">
                        <input type="hidden" name="action" value="search_ldap_dn">
                        <div class="col-12">
                            <label class="form-label" for="ldap_search_dn">DN de depart</label>
                            <input type="text" class="form-control" id="ldap_search_dn" name="ldap_search_dn" value="<?php echo htmlspecialchars($ldapSearchDn, ENT_QUOTES, 'UTF-8'); ?>" placeholder="ou=Utilisateurs,dc=exemple,dc=fr" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Rechercher dans LDAP</button>
                        </div>
                    </form>

                    <?php if ($ldapDnSearchResult !== null): ?>
                    <div class="mt-3 alert <?php echo $ldapDnSearchResult['success'] ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                        <?php echo htmlspecialchars($ldapDnSearchResult['message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php if (!empty($ldapDnSearchResult['success'])): ?>
                    <ul class="list-group list-group-flush mt-2">
                        <li class="list-group-item"><strong>DN de depart:</strong> <?php echo htmlspecialchars($ldapDnSearchResult['base_dn'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <li class="list-group-item"><strong>Filtre:</strong> <code><?php echo htmlspecialchars($ldapDnSearchResult['filter'], ENT_QUOTES, 'UTF-8'); ?></code></li>
                        <li class="list-group-item"><strong>Nombre d'entrees:</strong> <?php echo (int)$ldapDnSearchResult['count']; ?></li>
                    </ul>
                    <div class="mt-3">
                        <label class="form-label"><strong>Resultat de la recherche (JSON)</strong></label>
                        <pre class="pdc-setup-result-json p-3 border rounded mb-0"><?php echo htmlspecialchars(json_encode($ldapDnSearchResult['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
    </div>
    </div>
</div>
<script src="<?php echo APP_URL; ?>/assets/js/bootstrap.bundle.js"></script>
<script src="<?php echo APP_URL; ?>/assets/theme.js"></script>
</body>
</html>
