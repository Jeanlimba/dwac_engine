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

// IDOR : un employé SIMPLE ne peut consulter que son propre dossier. Les rôles
// privilégiés (admin de tenant = sans employee_id, manager, superviseur)
// consultent tout le tenant (la requête est déjà bornée au tenant en session).
$isPrivileged = empty($_SESSION['employee_id'])
    || in_array($_SESSION['user_role'] ?? '', ['admin', 'manager', 'superviseur'], true);
if (!$isPrivileged && (int) $employee_id !== (int) $_SESSION['employee_id']) {
    http_response_code(403);
    exit('Accès refusé');
}

$stmt = $pdo->prepare("SELECT ec.*, p.nom_poste, d.name AS department_name 
                       FROM employee_contracts ec 
                       LEFT JOIN postes p ON ec.poste_id = p.id 
                       LEFT JOIN departments d ON ec.departement_id = d.id
                       WHERE ec.employee_id = :employee_id AND ec.tenant_id = :tenant_id 
                       ORDER BY ec.date_debut DESC");
$stmt->execute(['employee_id' => $employee_id, 'tenant_id' => $tenant_id]);
$contracts = $stmt->fetchAll();

if (empty($contracts)) {
    echo '<div class="text-muted text-center py-4">Aucun contrat enregistré.</div>';
} else {
    $is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
    echo '<div class="table-responsive">';
    echo '<table class="table table-vcenter card-table">';
    echo '<thead><tr><th>Type</th><th>Affectation / Poste</th><th>Période</th><th>Salaire</th><th>Statut</th><th class="w-1"></th></tr></thead>';
    echo '<tbody>';
    foreach ($contracts as $contract) {
        $status_class = 'bg-green';
        if ($contract['statut'] == 'expired') $status_class = 'bg-yellow';
        if ($contract['statut'] == 'terminated') $status_class = 'bg-red';
        
        $date_fin = $contract['date_fin'] ? date('d/m/Y', strtotime($contract['date_fin'])) : 'Indéterminée';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($contract['type_contrat']) . '</td>';
        echo '<td>';
        echo '<div class="fw-bold">' . htmlspecialchars($contract['department_name'] ?? 'Non assignée') . '</div>';
        echo '<div class="text-muted small">' . htmlspecialchars($contract['nom_poste'] ?? 'Non défini') . '</div>';
        echo '</td>';
        echo '<td class="text-muted">' . date('d/m/Y', strtotime($contract['date_debut'])) . ' - ' . $date_fin . '</td>';
        echo '<td>' . number_format($contract['salaire_base'], 2, ',', ' ') . ' USD</td>';
        echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($contract['statut']) . '</span></td>';
        echo '<td>';
        echo '<div class="btn-list flex-nowrap">';
        if ($contract['fichier_contrat']) {
            $file_url = URLROOT . '/' . htmlspecialchars($contract['fichier_contrat']);
            echo '<button class="btn btn-white btn-icon" onclick="viewFile(\'' . $file_url . '\', \'Contrat - ' . htmlspecialchars($contract['type_contrat']) . '\')" title="Voir le fichier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg></button>';
        }
        if (!$is_employee) {
            echo '<button class="btn btn-white btn-icon" onclick="loadContractForm(' . $employee_id . ', ' . $contract['id'] . ')" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
