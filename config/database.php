<?php

// Include common functions
require_once __DIR__ . '/../src/functions.php';

// Load environment variables
load_env(__DIR__ . '/../.env');

/**
 * Détection automatique de l'environnement
 */
$is_local = false;

if (defined('APP_ENV') && APP_ENV !== '') {
    // Signal d'environnement EXPLICITE et fiable (prioritaire) : ne dépend pas
    // de l'en-tête Host, que le client peut falsifier pour forcer la bascule
    // sur la configuration locale.
    $is_local = in_array(strtolower(APP_ENV), ['local', 'dev', 'development'], true);
} elseif (isset($_SERVER['HTTP_HOST'])) {
    // Repli (compatibilité) : détection par hôte si APP_ENV n'est pas défini.
    $is_local = is_local_host($_SERVER['HTTP_HOST']);
} else {
    // En ligne de commande (CLI) : Windows = local en général pour ce projet.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $is_local = true;
    }
}

// Sélection des variables selon l'environnement
if ($is_local) {
    if (!defined('DB_HOST')) define('DB_HOST', defined('LOCAL_DB_HOST') ? LOCAL_DB_HOST : 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', defined('LOCAL_DB_NAME') ? LOCAL_DB_NAME : 'evolution');
    if (!defined('DB_USER')) define('DB_USER', defined('LOCAL_DB_USER') ? LOCAL_DB_USER : 'root');
    if (!defined('DB_PASS')) define('DB_PASS', defined('LOCAL_DB_PASS') ? LOCAL_DB_PASS : '');
} else {
    if (!defined('DB_HOST')) define('DB_HOST', defined('ONLINE_DB_HOST') ? ONLINE_DB_HOST : '');
    if (!defined('DB_NAME')) define('DB_NAME', defined('ONLINE_DB_NAME') ? ONLINE_DB_NAME : '');
    if (!defined('DB_USER')) define('DB_USER', defined('ONLINE_DB_USER') ? ONLINE_DB_USER : '');
    if (!defined('DB_PASS')) define('DB_PASS', defined('ONLINE_DB_PASS') ? ONLINE_DB_PASS : '');
}

// Default user password for new accounts
if (!defined('DEFAULT_PASSWORD')) define('DEFAULT_PASSWORD', 'password123');

// Compatibilité DB_PASSWORD
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', DB_PASS);

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si on est en ligne et que l'hôte configuré échoue, on tente localhost
    if (!$is_local) {
        try {
            $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e2) {
            die("Erreur de connexion au serveur de base de données. Veuillez contacter l'administrateur.");
        }
    } else {
        die("Erreur de connexion (LOCAL) : " . $e->getMessage());
    }
}
