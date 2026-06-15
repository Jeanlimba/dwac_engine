<?php
require_once 'config/database.php';
$stmt = $pdo->query("DESCRIBE employees");
$columns = $stmt->fetchAll();
foreach($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
