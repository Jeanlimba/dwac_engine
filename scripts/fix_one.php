<?php
require_once __DIR__ . '/../public/index.php'; // This should load everything
// Actually index.php executes the app. 
// I'll just load the necessary parts.

require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/GedFolder.php';
require_once __DIR__ . '/../app/Models/GedShare.php';
require_once __DIR__ . '/../app/Models/Employee.php';

// Define DB constants manually if needed or load from .env
$env = file_get_contents(__DIR__ . '/../.env');
preg_match('/DB_PASS=(.*)/', $env, $m); define('DB_PASS', trim($m[1] ?? ''));
preg_match('/DB_USER=(.*)/', $env, $m); define('DB_USER', trim($m[1] ?? 'root'));
preg_match('/DB_NAME=(.*)/', $env, $m); define('DB_NAME', trim($m[1] ?? 'evolution'));
preg_match('/DB_HOST=(.*)/', $env, $m); define('DB_HOST', trim($m[1] ?? 'localhost'));

$gedFolderModel = new GedFolder();
$gedShareModel = new GedShare();
$employeeModel = new Employee();
$db = new Database();

// Fix mission 2
$missionId = 2;
$userId = 2; // Superviseur admin-dwac
$tenantId = 1;

$root = $gedFolderModel->getRootFolder($userId);
$folderId = $gedFolderModel->createFolder([
    'tenant_id' => $tenantId,
    'user_id' => $userId,
    'name' => 'Mission : Audit CHIRPA Mars 2025',
    'parent_id' => $root->id
]);

$db->query("UPDATE missions SET ged_folder_id = :fid WHERE id = :id");
$db->bind(':fid', $folderId);
$db->bind(':id', $missionId);
$db->execute();

// Share with members
$db->query("SELECT employee_id FROM mission_team WHERE mission_id = :id");
$db->bind(':id', $missionId);
$members = $db->resultSet();
foreach($members as $m) {
    $sid = $employeeModel->getUserIdByEmployeeId($m->employee_id);
    if($sid) {
        $gedShareModel->addShare([
            'folder_id' => $folderId,
            'file_id' => null,
            'shared_by' => $userId,
            'shared_with' => $sid,
            'permission' => 'edit'
        ]);
    }
}
echo "OK fixed mission 2";
