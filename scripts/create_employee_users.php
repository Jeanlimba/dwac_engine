<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/functions.php';

try {
    // Select all employees who don't have a user account yet
    $sql = "SELECT e.id, e.tenant_id, e.nom, e.prenom, t.acronym 
            FROM employees e 
            JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN users u ON e.id = u.employee_id
            WHERE u.id IS NULL";
    
    $stmt = $pdo->query($sql);
    $employees = $stmt->fetchAll();

    $count = 0;
    $default_password = password_hash(defined('DEFAULT_PASSWORD') ? DEFAULT_PASSWORD : 'password123', PASSWORD_DEFAULT);

    foreach ($employees as $emp) {
        $username = generate_unique_username($pdo, $emp['prenom'], $emp['nom'], $emp['acronym'], $emp['tenant_id']);
        
        $insert_sql = "INSERT INTO users (tenant_id, employee_id, username, password, is_super_admin) 
                       VALUES (:tenant_id, :employee_id, :username, :password, 0)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            'tenant_id' => $emp['tenant_id'],
            'employee_id' => $emp['id'],
            'username' => $username,
            'password' => $default_password
        ]);
        
        echo "Compte créé pour {$emp['prenom']} {$emp['nom']} : {$username}\n";
        $count++;
    }

    echo "Terminé. {$count} comptes créés.\n";

} catch (PDOException $e) {
    die("ERREUR : " . $e->getMessage());
}
