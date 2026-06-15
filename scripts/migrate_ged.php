<?php
require_once __DIR__ . '/../config/database.php';

try {
    $sql = file_get_contents(__DIR__ . '/../database/sql/create_ged_tables.sql');
    $pdo->exec($sql);
    echo "Tables GED créées avec succès.\n";
} catch (PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage() . "\n");
}
