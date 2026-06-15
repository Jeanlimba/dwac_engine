<?php
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT id, username, password FROM users");
    $users = $stmt->fetchAll();

    $updated = 0;
    foreach ($users as $user) {
        // If password does not start with $2y$ (default bcrypt prefix), hash it
        if (strpos($user['password'], '$2y$') !== 0) {
            $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $update_stmt->execute(['password' => $hashed, 'id' => $user['id']]);
            echo "Updated password for user: {$user['username']}\n";
            $updated++;
        }
    }

    echo "Finished. Total users updated: {$updated}\n";

} catch (PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
