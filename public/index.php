<?php
// Include Helper Functions
require_once __DIR__ . '/../src/functions.php';

// Load Config from .env (avant la config d'erreurs : elle en dépend)
load_env(__DIR__ . '/../.env');

/*
 * Gestion des erreurs selon l'environnement.
 * Le .env étant partagé (local + prod, sélectionnés par détection d'hôte), on
 * décide de l'affichage des erreurs à partir de l'hôte courant, comme le fait
 * config/database.php. En PRODUCTION on n'affiche JAMAIS les erreurs à l'écran
 * (fuite de chemins, requêtes SQL, secrets) : on les journalise uniquement.
 * APP_DEBUG=true dans le .env peut forcer l'affichage si vraiment nécessaire.
 */
$isLocalEnv = is_local_host($_SERVER['HTTP_HOST'] ?? '');
$appDebug = defined('APP_DEBUG') ? filter_var(APP_DEBUG, FILTER_VALIDATE_BOOLEAN) : false;

error_reporting(E_ALL);
ini_set('log_errors', '1');
if ($isLocalEnv || $appDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Initialize Session
init_session();

/*
 * En-têtes de sécurité HTTP (défense en profondeur, appliqués à toutes les
 * réponses). NB : pas de Content-Security-Policy ici — l'interface utilise du
 * JS inline (handlers onclick, scripts inline) qu'une CSP stricte casserait ;
 * la CSP fera l'objet d'un chantier dédié et testé.
 */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/*
 * Gestion centralisée des erreurs non rattrapées (PRODUCTION uniquement).
 * En dev, on laisse PHP afficher l'erreur (display_errors). En prod, on
 * journalise et on affiche une page 500 générique (pas de fuite, pas de page
 * blanche). En dev, ces handlers ne sont pas posés.
 */
if (!($isLocalEnv || $appDebug)) {
    $renderError500 = function () {
        if (!headers_sent()) {
            http_response_code(500);
        }
        $page = dirname(__DIR__) . '/app/Views/errors/500.php';
        if (is_file($page)) {
            include $page;
        } else {
            echo 'Une erreur interne est survenue.';
        }
    };

    set_exception_handler(function ($e) use ($renderError500) {
        error_log('[evolution] Uncaught ' . get_class($e) . ': ' . $e->getMessage()
            . ' @ ' . $e->getFile() . ':' . $e->getLine());
        $renderError500();
        exit;
    });

    register_shutdown_function(function () use ($renderError500) {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            error_log('[evolution] Fatal: ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
            $renderError500();
        }
    });
}

// Define URLROOT dynamically
$protocol = "http";
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) {
    $protocol = "https";
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = "https";
}

$host = $_SERVER['HTTP_HOST'];
// Récupère le chemin du script et retire index.php ainsi que public/ pour trouver la racine
$script_path = str_replace(['/public/index.php', '/index.php'], '', $_SERVER['SCRIPT_NAME']);
$urlroot = $protocol . "://" . $host . $script_path;
define('URLROOT', rtrim($urlroot, '/'));

// Default SITENAME if not in .env
if (!defined('SITENAME')) define('SITENAME', 'DWAC Engine');

define('APPROOT', dirname(dirname(__FILE__)) . '/app');

// Autoload Core Libraries
spl_autoload_register(function($className){
    // Si c'est dans Core namespace, on enlève le préfixe pour trouver le fichier
    if (strpos($className, 'Core\\') === 0) {
        $className = str_replace('Core\\', '', $className);
    }
    $className = str_replace('\\', '/', $className);
    $file = dirname(__DIR__) . '/app/Core/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Init Core Library
$init = new App();
