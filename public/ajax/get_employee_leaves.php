<?php
session_start();
require_once '../../config/database.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);
$public_pos = strpos($script_dir, '/public');
if ($public_pos !== false) {
    $project_root = substr($script_dir, 0, $public_pos);
} else {
    $project_root = dirname($script_dir);
}
if (!defined('URLROOT')) {
    define('URLROOT', $protocol . "://" . $host . $project_root);
}

if (!isset($_SESSION['user_id'])) {
    exit('Accès refusé');
}

$employee_id = $_GET['employee_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'];

if (!$employee_id) {
    exit('ID employé manquant');
}

$stmt = $pdo->prepare("SELECT lr.*, lt.name as leave_type_name FROM leave_requests lr 
                       JOIN leave_types lt ON lr.leave_type_id = lt.id
                       WHERE lr.employee_id = :employee_id AND lt.tenant_id = :tenant_id 
                       ORDER BY lr.start_date DESC");
$stmt->execute(['employee_id' => $employee_id, 'tenant_id' => $tenant_id]);
$leaves = $stmt->fetchAll();

if (empty($leaves)) {
    echo '<div class="text-muted text-center py-4">Aucune demande de congé enregistrée.</div>';
} else {
    $is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
    echo '<div class="table-responsive">';
    echo '<table class="table table-vcenter card-table">';
    echo '<thead><tr><th>Type</th><th>Période</th><th>Statut</th><th class="w-1"></th></tr></thead>';
    echo '<tbody>';
    foreach ($leaves as $leave) {
        $period = date('d/m/Y', strtotime($leave['start_date'])) . ' - ' . date('d/m/Y', strtotime($leave['end_date']));
        $status_badge = '';
        switch($leave['status']) {
            case 'pending': $status_badge = '<span class="badge bg-yellow">En attente</span>'; break;
            case 'approved': $status_badge = '<span class="badge bg-green">Approuvé</span>'; break;
            case 'rejected': $status_badge = '<span class="badge bg-red">Refusé</span>'; break;
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($leave['leave_type_name']) . '</td>';
        echo '<td class="text-muted">' . $period . '</td>';
        echo '<td>' . $status_badge . '</td>';
        echo '<td>';
        echo '<div class="btn-list flex-nowrap">';
        if ($leave['attachment_path']) {
            $file_url = URLROOT . '/' . htmlspecialchars($leave['attachment_path']);
            echo '<button class="btn btn-white btn-icon" onclick="viewFile(\'' . $file_url . '\', \'Justificatif - ' . htmlspecialchars($leave['leave_type_name']) . '\')" title="Voir le justificatif"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg></button>';
        }
        if (!$is_employee) {
            echo '<button class="btn btn-white btn-icon" onclick="loadLeaveForm(' . $employee_id . ', ' . $leave['id'] . ')" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
