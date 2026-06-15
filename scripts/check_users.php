<?php
require_once '../config/database.php';

$stmt = $pdo->query("SELECT id, username, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($users);
