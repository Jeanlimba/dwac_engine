<?php
require_once 'config/database.php';
require_once 'app/Core/Database.php';

use Core\Database;

$db = new Database();
$sql = file_get_contents($argv[1]);

// Split by semicolon if multiple queries
$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        echo "Executing: $query\n";
        $db->query($query);
        if ($db->execute()) {
            echo "Success\n";
        } else {
            echo "Failed\n";
        }
    }
}
