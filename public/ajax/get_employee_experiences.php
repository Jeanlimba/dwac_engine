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

$stmt = $pdo->prepare("SELECT * FROM employee_experiences 
                       WHERE employee_id = :employee_id AND tenant_id = :tenant_id 
                       ORDER BY date_debut DESC");
$stmt->execute(['employee_id' => $employee_id, 'tenant_id' => $tenant_id]);
$experiences = $stmt->fetchAll();

if (empty($experiences)) {
    echo '<div class="text-muted text-center py-4">Aucune expérience enregistrée.</div>';
} else {
    $is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
    echo '<div class="table-responsive">';
    echo '<table class="table table-vcenter card-table">';
    echo '<thead><tr><th>Poste</th><th>Entreprise</th><th>Période</th><th class="w-1"></th></tr></thead>';
    echo '<tbody>';
    foreach ($experiences as $exp) {
        $period = date('m/Y', strtotime($exp['date_debut'])) . ' - ' . ($exp['date_fin'] ? date('m/Y', strtotime($exp['date_fin'])) : 'Présent');

        echo '<tr>';
        echo '<td>';
        echo '<div>' . htmlspecialchars($exp['poste']) . '</div>';
        if ($exp['description']) {
            echo '<small class="text-muted">' . htmlspecialchars(substr($exp['description'], 0, 50)) . (strlen($exp['description']) > 50 ? '...' : '') . '</small>';
        }
        echo '</td>';
        echo '<td>' . htmlspecialchars($exp['entreprise'] ?? '-') . '</td>';
        echo '<td class="text-muted">' . $period . '</td>';
        echo '<td>';
        echo '<div class="btn-list flex-nowrap">';
        if (!$is_employee) {
            echo '<button class="btn btn-white btn-icon" onclick="loadExperienceForm(' . $employee_id . ', ' . $exp['id'] . ')" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>';
            echo '<button class="btn btn-white btn-icon text-danger" onclick="deleteExperience(' . $employee_id . ', ' . $exp['id'] . ')" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
