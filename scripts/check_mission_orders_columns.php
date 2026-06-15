<?php
require_once 'config/database.php';
require_once 'app/Core/Database.php';

use Core\Database;

$db = new Database();
$db->query("DESCRIBE mission_orders");
$columns = $db->resultSet();

foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}
