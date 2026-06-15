<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'evolution');
define('DB_USER', 'root');
define('DB_PASS', '');

$sqlFile = 'database/sql/update_expenses_workflow.sql';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec(file_get_contents($sqlFile));
    echo "Expense workflow columns added.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
