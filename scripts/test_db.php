<?php
require_once 'config/database.php';
$stmt = $pdo->query("SELECT id, tenant_id, nom, prenom FROM employees");
$employees = $stmt->fetchAll();
var_dump($employees);

$stmt = $pdo->query("SELECT id, tenant_id, username FROM users");
$users = $stmt->fetchAll();
var_dump($users);
