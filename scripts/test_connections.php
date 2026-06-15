<?php
require_once 'src/functions.php';
load_env('.env');

echo "Testing ONLINE configuration...\n";
$host = ONLINE_DB_HOST;
$db = ONLINE_DB_NAME;
$user = ONLINE_DB_USER;
$pass = ONLINE_DB_PASS;

echo "Attempt 1: Host=$host, DB=$db, User=$user\n";
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "SUCCESS: Connection to ONLINE database successful using $host!\n";
} catch (PDOException $e) {
    echo "FAILED: Connection using $host failed: " . $e->getMessage() . "\n";
    
    echo "Attempt 2: Trying localhost as fallback...\n";
    $dsn_fallback = "mysql:host=localhost;dbname=$db;charset=utf8mb4";
    try {
        $pdo_fallback = new PDO($dsn_fallback, $user, $pass);
        echo "SUCCESS: Connection to ONLINE database successful using localhost!\n";
        echo "TIP: You should probably change ONLINE_DB_HOST to 'localhost' in your .env file.\n";
    } catch (PDOException $e2) {
        echo "FAILED: Connection using localhost also failed: " . $e2->getMessage() . "\n";
    }
}

echo "\nTesting LOCAL configuration...\n";
$dsn_local = "mysql:host=" . LOCAL_DB_HOST . ";dbname=" . LOCAL_DB_NAME . ";charset=utf8mb4";
try {
    $pdo_local = new PDO($dsn_local, LOCAL_DB_USER, LOCAL_DB_PASS);
    echo "SUCCESS: Connection to LOCAL database successful!\n";
} catch (PDOException $e) {
    echo "FAILED: Connection to LOCAL database failed: " . $e->getMessage() . "\n";
}
