<?php
require_once 'config/database.php';
try {
    $pdo->exec("ALTER TABLE expenses ADD COLUMN expense_date_end DATE NULL AFTER expense_date");
    echo "Column added successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
