<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/functions.php';

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

// Gérer la requête POST pour enregistrer ou supprimer une formation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
        exit;
    }

    $tenant_id = $_SESSION['tenant_id'];

    // Action de suppression
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $training_id = $_POST['training_id'] ?? null;
        if (!$training_id) {
            echo json_encode(['success' => false, 'message' => 'ID formation manquant.']);
            exit;
        }

        try {
            // Optionnel : supprimer le fichier physique si nécessaire
            $stmt_file = $pdo->prepare("SELECT attachment_path FROM employee_trainings WHERE id = :id AND tenant_id = :tenant_id");
            $stmt_file->execute(['id' => $training_id, 'tenant_id' => $tenant_id]);
            $file = $stmt_file->fetch();
            if ($file && $file['attachment_path'] && file_exists('../../' . $file['attachment_path'])) {
                // On peut choisir de ne pas supprimer physiquement pour garder une trace, 
                // mais ici on le fait pour rester propre.
                // unlink('../../' . $file['attachment_path']);
            }

            $stmt = $pdo->prepare("DELETE FROM employee_trainings WHERE id = :id AND tenant_id = :tenant_id");
            $stmt->execute(['id' => $training_id, 'tenant_id' => $tenant_id]);
            echo json_encode(['success' => true, 'message' => 'Formation supprimée avec succès.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
        }
        exit;
    }

    $employee_id = $_POST['employee_id'] ?? null;
    $training_id = $_POST['training_id'] ?? null;
    
    $training_name = trim($_POST['training_name'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $date_completed = !empty(trim($_POST['date_completed'])) ? trim($_POST['date_completed']) : NULL;
    $expiry_date = !empty(trim($_POST['expiry_date'])) ? trim($_POST['expiry_date']) : NULL;
    $description = trim($_POST['description'] ?? '');

    if (empty($training_name) || empty($employee_id)) {
        echo json_encode(['success' => false, 'message' => 'Le nom de la formation et l\'ID de l\'employé sont requis.']);
        exit;
    }

    // Gestion de l'upload de fichier
    $attachment_path = $_POST['existing_attachment'] ?? null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_res = handle_upload('attachment', '../uploads/trainings/');
        if ($upload_res) {
            $attachment_path = str_replace('../', '', $upload_res);
        } else {
            echo json_encode(['success' => false, 'message' => 'Échec de l\'upload du certificat.']);
            exit;
        }
    }

    try {
        if ($training_id) { // Mise à jour
            $sql = "UPDATE employee_trainings SET training_name = :training_name, institution = :institution, date_completed = :date_completed, expiry_date = :expiry_date, description = :description, attachment_path = :attachment_path WHERE id = :id AND employee_id = :employee_id AND tenant_id = :tenant_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'training_name' => $training_name,
                'institution' => $institution,
                'date_completed' => $date_completed,
                'expiry_date' => $expiry_date,
                'description' => $description,
                'attachment_path' => $attachment_path,
                'id' => $training_id,
                'employee_id' => $employee_id,
                'tenant_id' => $tenant_id
            ]);
            $message = 'Formation mise à jour avec succès.';
        } else { // Création
            $sql = "INSERT INTO employee_trainings (employee_id, tenant_id, training_name, institution, date_completed, expiry_date, description, attachment_path) VALUES (:employee_id, :tenant_id, :training_name, :institution, :date_completed, :expiry_date, :description, :attachment_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'employee_id' => $employee_id,
                'tenant_id' => $tenant_id,
                'training_name' => $training_name,
                'institution' => $institution,
                'date_completed' => $date_completed,
                'expiry_date' => $expiry_date,
                'description' => $description,
                'attachment_path' => $attachment_path
            ]);
            $message = 'Formation ajoutée avec succès.';
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
    }
    exit;
}

// Requête GET : Afficher le formulaire
$employee_id = $_GET['employee_id'] ?? null;
$training_id = $_GET['training_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'];
$training = null;

if ($training_id) {
    $stmt = $pdo->prepare("SELECT * FROM employee_trainings WHERE id = :id AND employee_id = :employee_id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $training_id, 'employee_id' => $employee_id, 'tenant_id' => $tenant_id]);
    $training = $stmt->fetch();
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $training ? 'Modifier' : 'Ajouter' ?> une formation</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
</div>
<div class="modal-body">
    <form id="training_form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id ?? '') ?>">
        <?php if ($training): ?>
            <input type="hidden" name="training_id" value="<?= htmlspecialchars($training['id'] ?? '') ?>">
            <input type="hidden" name="existing_attachment" value="<?= htmlspecialchars($training['attachment_path'] ?? '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Nom de la formation</label>
            <input type="text" name="training_name" class="form-control" value="<?= htmlspecialchars($training['training_name'] ?? '') ?>" required placeholder="Ex: Certification ITIL, Management, etc.">
        </div>
        <div class="mb-3">
            <label class="form-label">Institution / Centre de formation</label>
            <input type="text" name="institution" class="form-control" value="<?= htmlspecialchars($training['institution'] ?? '') ?>" placeholder="Ex: Udemy, Université de Kinshasa, etc.">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date d'achèvement</label>
                    <input type="date" name="date_completed" class="form-control" value="<?= htmlspecialchars($training['date_completed'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date d'expiration (si applicable)</label>
                    <input type="date" name="expiry_date" class="form-control" value="<?= htmlspecialchars($training['expiry_date'] ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Description / Compétences acquises</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($training['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Certificat / Justificatif (.pdf, image)</label>
            <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
            <?php if ($training && $training['attachment_path']): ?>
                <small class="form-text text-muted">Fichier actuel : <a href="<?= URLROOT . '/' . htmlspecialchars($training['attachment_path']) ?>" target="_blank">Consulter</a></small>
            <?php endif; ?>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="save_training_button" class="btn btn-primary">Enregistrer</button>
</div>
