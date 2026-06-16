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

// Gérer la requête POST pour enregistrer ou supprimer une expérience
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
        exit;
    }
    csrf_check_or_die(); // Protection CSRF

    $tenant_id = $_SESSION['tenant_id'];

    // Action de suppression
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $experience_id = $_POST['experience_id'] ?? null;
        if (!$experience_id) {
            echo json_encode(['success' => false, 'message' => 'ID expérience manquant.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM employee_experiences WHERE id = :id AND tenant_id = :tenant_id");
            $stmt->execute(['id' => $experience_id, 'tenant_id' => $tenant_id]);
            echo json_encode(['success' => true, 'message' => 'Expérience supprimée avec succès.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
        }
        exit;
    }

    $employee_id = $_POST['employee_id'] ?? null;
    $experience_id = $_POST['experience_id'] ?? null;

    $response = ['success' => false, 'message' => 'Une erreur inconnue est survenue.'];

    if (isset($_POST['save_experience'])) {

        if (!$employee_id) {
            $response['message'] = 'ID employé manquant.';
            echo json_encode($response);
            exit;
        }

        $entreprise = trim($_POST['entreprise'] ?? '');
        $poste = trim($_POST['poste'] ?? '');
        $date_debut = trim($_POST['date_debut'] ?? '');
        $date_fin = !empty(trim($_POST['date_fin'])) ? trim($_POST['date_fin']) : NULL;
        $description = trim($_POST['description'] ?? '');

        // Validation
        $errors = [];
        if (empty($entreprise)) $errors[] = 'Entreprise';
        if (empty($poste)) $errors[] = 'Poste';
        if (empty($date_debut)) $errors[] = 'Date de début';

        if (!empty($errors)) {
            $response['message'] = 'Les champs suivants sont requis : ' . implode(', ', $errors) . '.';
            echo json_encode($response);
            exit;
        }

        try {
            if ($experience_id) { // Mise à jour
                $sql = "UPDATE employee_experiences SET entreprise = :entreprise, poste = :poste, date_debut = :date_debut, date_fin = :date_fin, description = :description WHERE id = :id AND employee_id = :employee_id AND tenant_id = :tenant_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'entreprise' => $entreprise,
                    'poste' => $poste,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin,
                    'description' => $description,
                    'id' => $experience_id,
                    'employee_id' => $employee_id,
                    'tenant_id' => $tenant_id
                ]);
                $response = ['success' => true, 'message' => 'Expérience mise à jour avec succès.'];
            } else { // Création
                $sql = "INSERT INTO employee_experiences (employee_id, tenant_id, entreprise, poste, date_debut, date_fin, description) VALUES (:employee_id, :tenant_id, :entreprise, :poste, :date_debut, :date_fin, :description)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'employee_id' => $employee_id,
                    'tenant_id' => $tenant_id,
                    'entreprise' => $entreprise,
                    'poste' => $poste,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin,
                    'description' => $description
                ]);
                $response = ['success' => true, 'message' => 'Expérience ajoutée avec succès.'];
            }
        } catch (PDOException $e) {
            $response['message'] = 'Erreur base de données : ' . $e->getMessage();
        }
    }

    echo json_encode($response);
    exit;
}

// Requête GET : Afficher le formulaire
if (!isset($_SESSION['user_id'])) {
    exit('Accès refusé');
}

$employee_id = $_GET['employee_id'] ?? null;
$experience_id = $_GET['experience_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'];

$experience = null;
if ($experience_id) {
    $stmt = $pdo->prepare("SELECT * FROM employee_experiences WHERE id = :id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $experience_id, 'tenant_id' => $tenant_id]);
    $experience = $stmt->fetch();
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $experience ? 'Modifier' : 'Ajouter' ?> une expérience</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
</div>
<div class="modal-body">
    <form id="experience_form" method="POST">
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id ?? '') ?>">
        <?php if ($experience): ?>
            <input type="hidden" name="experience_id" value="<?= htmlspecialchars($experience['id'] ?? '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Entreprise</label>
            <input type="text" name="entreprise" class="form-control" value="<?= htmlspecialchars($experience['entreprise'] ?? '') ?>" required placeholder="Ex: Google, BCDC, etc.">
        </div>
        <div class="mb-3">
            <label class="form-label">Poste occupé</label>
            <input type="text" name="poste" class="form-control" value="<?= htmlspecialchars($experience['poste'] ?? '') ?>" required placeholder="Ex: Comptable, Développeur, etc.">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($experience['date_debut'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($experience['date_fin'] ?? '') ?>">
                    <small class="text-muted">Laisser vide si vous y travaillez toujours.</small>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Description des tâches</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Décrivez brièvement vos responsabilités..."><?= htmlspecialchars($experience['description'] ?? '') ?></textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="save_experience_button" class="btn btn-primary">
        Enregistrer
    </button>
</div>
