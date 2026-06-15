<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Helper Functions
require_once __DIR__ . '/../src/functions.php';

// Initialize Session
init_session();

// Load Config from .env
load_env(__DIR__ . '/../.env');

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
