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

$stmt = $pdo->prepare("SELECT * FROM employee_trainings 
                       WHERE employee_id = :employee_id AND tenant_id = :tenant_id 
                       ORDER BY date_completed DESC");
$stmt->execute(['employee_id' => $employee_id, 'tenant_id' => $tenant_id]);
$trainings = $stmt->fetchAll();

if (empty($trainings)) {
    echo '<div class="text-muted text-center py-4">Aucune formation enregistrée.</div>';
} else {
    $is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
    echo '<div class="table-responsive">';
    echo '<table class="table table-vcenter card-table">';
    echo '<thead><tr><th>Formation</th><th>Institution</th><th>Date d\'achèvement</th><th>Validité</th><th class="w-1"></th></tr></thead>';
    echo '<tbody>';
    foreach ($trainings as $training) {
        $expiry_date = $training['expiry_date'] ? date('d/m/Y', strtotime($training['expiry_date'])) : 'N/A';
        $is_expired = $training['expiry_date'] && strtotime($training['expiry_date']) < time();
        $expiry_class = $is_expired ? 'text-danger fw-bold' : '';

        echo '<tr>';
        echo '<td>';
        echo '<div>' . htmlspecialchars($training['training_name']) . '</div>';
        if ($training['description']) {
            echo '<small class="text-muted">' . htmlspecialchars(substr($training['description'], 0, 50)) . (strlen($training['description']) > 50 ? '...' : '') . '</small>';
        }
        echo '</td>';
        echo '<td>' . htmlspecialchars($training['institution'] ?? '-') . '</td>';
        echo '<td class="text-muted">' . ($training['date_completed'] ? date('d/m/Y', strtotime($training['date_completed'])) : '-') . '</td>';
        echo '<td class="' . $expiry_class . '">' . $expiry_date . '</td>';
        echo '<td>';
        echo '<div class="btn-list flex-nowrap">';
        if ($training['attachment_path']) {
            $file_url = URLROOT . '/' . htmlspecialchars($training['attachment_path']);
            echo '<button class="btn btn-white btn-icon" onclick="viewFile(\'' . $file_url . '\', \'Formation - ' . htmlspecialchars($training['training_name']) . '\')" title="Voir le certificat"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg></button>';
        }
        if (!$is_employee) {
            echo '<button class="btn btn-white btn-icon" onclick="loadTrainingForm(' . $employee_id . ', ' . $training['id'] . ')" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>';
            echo '<button class="btn btn-white btn-icon text-danger" onclick="deleteTraining(' . $employee_id . ', ' . $training['id'] . ')" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
