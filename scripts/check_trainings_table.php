<?php
require_once 'config/database.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'employee_trainings'");
    if ($stmt->fetch()) {
        echo "Table exists\n";
    } else {
        echo "Table does not exist. Creating it...\n";
        $sql = file_get_contents('database/sql/employee_trainings.sql');
        $pdo->exec($sql);
        echo "Table created successfully\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
