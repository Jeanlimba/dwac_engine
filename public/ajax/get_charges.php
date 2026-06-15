<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    exit('Accès refusé');
}

$category = $_GET['category'] ?? 'Administrative';
$tenant_id = $_SESSION['tenant_id'];

$stmt = $pdo->prepare("SELECT * FROM charges WHERE tenant_id = :tenant_id AND category = :category ORDER BY name ASC");
$stmt->execute(['tenant_id' => $tenant_id, 'category' => $category]);
$charges = $stmt->fetchAll();

if (empty($charges)) {
    echo '<div class="text-muted text-center py-4">Aucune charge enregistrée dans cette catégorie.</div>';
} else {
    echo '<div class="table-responsive">';
    echo '<table class="table table-vcenter card-table">';
    echo '<thead><tr><th>Nom</th><th>Description</th><th class="w-1">Actions</th></tr></thead>';
    echo '<tbody>';
    foreach ($charges as $charge) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($charge['name']) . '</td>';
        echo '<td class="text-muted">' . htmlspecialchars($charge['description'] ?? '-') . '</td>';
        echo '<td>';
        echo '<div class="btn-list flex-nowrap">';
        echo '<button class="btn btn-white btn-icon btn-sm" onclick="loadChargeForm(' . $charge['id'] . ')" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>';
        echo '<button class="btn btn-white btn-icon btn-sm text-danger" onclick="deleteCharge(' . $charge['id'] . ')" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
