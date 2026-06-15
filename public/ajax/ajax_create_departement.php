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
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    if (empty($name)) {
        $response['message'] = "Le nom de l'entité est requis.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (tenant_id, name, parent_id) VALUES (:tenant_id, :name, :parent_id)");
            $stmt->execute(['tenant_id' => $tenant_id, 'name' => $name, 'parent_id' => $parent_id]);
            $new_id = $pdo->lastInsertId();

            $response = [
                'success' => true,
                'message' => "Entité d'affectation créée avec succès.",
                'department' => [
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
