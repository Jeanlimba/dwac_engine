<?php
require_once __DIR__ . '/../config/database.php';
try {
    $sql = file_get_contents(__DIR__ . '/../database/sql/fix_ged_files_null_user.sql');
    $pdo->exec($sql);
    echo "Table ged_files mise à jour (user_id peut être NULL).\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
