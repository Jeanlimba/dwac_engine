<?php
require_once 'config/database.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS charges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        category ENUM('Administrative', 'Mission') NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
    )");
    echo "Table 'charges' créée avec succès.";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
