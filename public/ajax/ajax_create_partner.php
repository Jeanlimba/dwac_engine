<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
    exit;
}

require_once '../../config/database.php';

$tenant_id = $_SESSION['tenant_id'];
$response = ['success' => false, 'message' => 'Une erreur inconnue est survenue.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        $response['message'] = "Le nom du partenaire est requis.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO partners (tenant_id, name, contact_person, email, phone) VALUES (:tenant_id, :name, :contact_person, :email, :phone)");
            $stmt->execute([
                'tenant_id' => $tenant_id, 
                'name' => $name, 
                'contact_person' => $contact_person,
                'email' => $email,
                'phone' => $phone
            ]);
            $new_id = $pdo->lastInsertId();

            $response = [
                'success' => true,
                'message' => "Partenaire créé avec succès.",
                'partner' => [
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
