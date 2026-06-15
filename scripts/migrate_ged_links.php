<?php
require_once __DIR__ . '/../config/database.php';
try {
    $sql = file_get_contents(__DIR__ . '/../database/sql/create_ged_external_links.sql');
    $pdo->exec($sql);
    echo "Table ged_external_links créée avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
