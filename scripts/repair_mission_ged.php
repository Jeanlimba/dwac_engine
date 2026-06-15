<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/GedFolder.php';
require_once __DIR__ . '/../app/Models/GedShare.php';
require_once __DIR__ . '/../app/Models/Employee.php';

$gedFolderModel = new GedFolder();
$gedShareModel = new GedShare();
$employeeModel = new Employee();

try {
    $stmt = $pdo->query('SELECT id, title, tenant_id FROM missions WHERE ged_folder_id IS NULL');
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($missions as $mission) {
        // Trouver le superviseur (le premier admin du tenant par simplicité pour le script, 
        // ou on assume que c'est l'utilisateur ID 2 ou 3 selon nos logs précédents)
        // Pour ce script on va chercher l'owner du root folder du tenant
        $stmt = $pdo->prepare('SELECT user_id FROM ged_folders WHERE tenant_id = ? AND is_root = 1 LIMIT 1');
        $stmt->execute([$mission['tenant_id']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($owner) {
            $userId = $owner['user_id'];
            $rootFolder = $gedFolderModel->getRootFolder($userId);
            
            $folderData = [
                'tenant_id' => $mission['tenant_id'],
                'user_id' => $userId,
                'name' => 'Mission : ' . $mission['title'],
                'parent_id' => $rootFolder ? $rootFolder->id : null
            ];
            
            $folderId = $gedFolderModel->createFolder($folderData);
            
            if ($folderId) {
                // Update mission
                $stmt = $pdo->prepare('UPDATE missions SET ged_folder_id = ? WHERE id = ?');
                $stmt->execute([$folderId, $mission['id']]);
                echo "Dossier crée pour : " . $mission['title'] . "\n";

                // Partages
                $stmt = $pdo->prepare('SELECT employee_id FROM mission_team WHERE mission_id = ?');
                $stmt->execute([$mission['id']]);
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($members as $m) {
                    $sharedUserId = $employeeModel->getUserIdByEmployeeId($m['employee_id']);
                    if ($sharedUserId) {
                        $gedShareModel->addShare([
                            'folder_id' => $folderId,
                            'file_id' => null,
                            'shared_by' => $userId,
                            'shared_with' => $sharedUserId,
                            'permission' => 'edit'
                        ]);
                        echo "  -> Partagé avec User ID $sharedUserId\n";
                    }
                }
            }
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
