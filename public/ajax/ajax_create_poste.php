<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
    exit;
}

if (!isset($_SESSION['tenant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tenant ID introuvable dans la session.']);
    exit;
}

require_once '../../config/database.php';

$tenant_id = $_SESSION['tenant_id'];
$response = ['success' => false, 'message' => 'Une erreur inconnue est survenue.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $response['message'] = 'Le nom du poste est requis.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO postes (tenant_id, nom_poste) VALUES (:tenant_id, :nom_poste)");
            $stmt->execute(['tenant_id' => $tenant_id, 'nom_poste' => $name]);
            $new_id = $pdo->lastInsertId();

            $response = [
                'success' => true,
                'message' => 'Poste créé avec succès.',
                'item' => [
                    'id' => $new_id,
                    'name' => $name
                ]
            ];
        } catch (PDOException $e) {
            $response['message'] = 'Erreur base de données : ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Requête invalide.';
}

echo json_encode($response);
exit;
