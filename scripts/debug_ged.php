<?php
// Sécurité : utilitaire réservé à la ligne de commande (jamais via le web).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once __DIR__ . '/../config/database.php';
try {
    echo "--- GED FOLDERS ---\n";
    $stmt = $pdo->query('SELECT id, name, user_id, parent_id, is_root FROM ged_folders');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- GED SHARES ---\n";
    $stmt = $pdo->query('SELECT * FROM ged_shares');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- USERS ---\n";
    $stmt = $pdo->query('SELECT id, username, employee_id FROM users');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
