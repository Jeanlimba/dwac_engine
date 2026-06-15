<?php
require_once '../config/database.php';

$username = 'super_admin';
$password = 'password123';

$stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    echo "Password '{$password}' is CORRECT for user '{$username}'.\n";
} else {
    echo "Password '{$password}' is WRONG for user '{$username}'.\n";
}
