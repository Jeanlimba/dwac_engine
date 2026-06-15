<?php
require_once __DIR__ . '/../config/database.php';

try {
    $sql = file_get_contents(__DIR__ . '/../database/sql/update_missions_add_ged_folder.sql');
    $pdo->exec($sql);
    echo "Migration missions (ged_folder_id) terminée avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
}
