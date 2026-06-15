<?php
require_once __DIR__ . '/../config/database.php';
try {
    echo "--- MISSIONS ---\n";
    $stmt = $pdo->query('SELECT id, title, ged_folder_id FROM missions');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
