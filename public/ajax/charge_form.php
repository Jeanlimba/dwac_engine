<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$tenant_id = $_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? 'save';
    $id = $_POST['id'] ?? null;

    if ($action === 'delete') {
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM charges WHERE id = :id AND tenant_id = :tenant_id");
        $stmt->execute(['id' => $id, 'tenant_id' => $tenant_id]);
        echo json_encode(['success' => true]);
        exit;
    }

    $category = $_POST['category'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($category) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires']);
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE charges SET category = :category, name = :name, description = :description WHERE id = :id AND tenant_id = :tenant_id");
            $stmt->execute([
                'category' => $category,
                'name' => $name,
                'description' => $description,
                'id' => $id,
                'tenant_id' => $tenant_id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO charges (tenant_id, category, name, description) VALUES (:tenant_id, :category, :name, :description)");
            $stmt->execute([
                'tenant_id' => $tenant_id,
                'category' => $category,
                'name' => $name,
                'description' => $description
            ]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
    }
    exit;
}

// GET request: load form
$id = $_GET['id'] ?? null;
$charge = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM charges WHERE id = :id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $id, 'tenant_id' => $tenant_id]);
    $charge = $stmt->fetch();
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $charge ? 'Modifier' : 'Ajouter' ?> une charge</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="charge_form">
        <input type="hidden" name="id" value="<?= $charge['id'] ?? '' ?>">
        <div class="mb-3">
            <label class="form-label required">Catégorie</label>
            <select name="category" class="form-select" required>
                <option value="Administrative" <?= ($charge && $charge['category'] === 'Administrative') ? 'selected' : '' ?>>Administrative</option>
                <option value="Mission" <?= ($charge && $charge['category'] === 'Mission') ? 'selected' : '' ?>>Mission</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label required">Nom de la charge</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($charge['name'] ?? '') ?>" placeholder="Ex: Loyer, Transport, etc." required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($charge['description'] ?? '') ?></textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="outside" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="save_charge_button" class="btn btn-primary">Enregistrer</button>
</div>
