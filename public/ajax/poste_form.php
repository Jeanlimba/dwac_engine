<?php
session_start();
require_once '../../config/database.php';

// Handle POST request to save a new poste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit;
    }
    csrf_check_or_die(); // Protection CSRF

    $nom_poste = trim($_POST['nom_poste'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom_poste)) {
        echo json_encode(['success' => false, 'message' => 'Position name is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO postes (nom_poste, description) VALUES (:nom_poste, :description)");
        $stmt->execute(['nom_poste' => $nom_poste, 'description' => $description]);
        $new_poste_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Position added successfully.',
            'poste' => [
                'id' => $new_poste_id,
                'nom_poste' => $nom_poste
            ]
        ]);
    } catch (PDOException $e) {
        // Check for duplicate entry
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(['success' => false, 'message' => 'This position name already exists.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit;
}

// Display the form for GET request
?>
<div class="modal-header">
    <h5 class="modal-title">Add New Position</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div id="poste-form-alert-placeholder"></div>
    <form id="poste_form" action="ajax/poste_form.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Position Name</label>
            <input type="text" name="nom_poste" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
    <button type="button" id="save_poste_button" class="btn btn-primary">Save Position</button>
</div>
