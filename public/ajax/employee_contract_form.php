<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
        exit;
    }

    try {
        require_once '../../config/database.php';
        require_once '../../src/functions.php';
        csrf_check_or_die(); // Protection CSRF (functions.php chargé juste au-dessus)

        $employee_id = $_POST['employee_id'] ?? null;
        $tenant_id = $_POST['tenant_id'] ?? null;
        $contract_id = $_POST['contract_id'] ?? null;

        if (isset($_POST['save_contract'])) {
            if (!$employee_id || !$tenant_id) {
                echo json_encode(['success' => false, 'message' => 'ID employé ou ID tenant manquant.']);
                exit;
            }

            $type_contrat = trim($_POST['type_contrat'] ?? '');
            $date_debut = trim($_POST['date_debut'] ?? '');
            $date_fin = !empty(trim($_POST['date_fin'])) ? trim($_POST['date_fin']) : NULL;
            $salaire_base = isset($_POST['salaire_base']) && $_POST['salaire_base'] !== '' ? trim($_POST['salaire_base']) : NULL;
            $statut_contrat = trim($_POST['statut_contrat'] ?? 'active');
            $commentaire = trim($_POST['commentaire'] ?? '');
            
            // Nouvelles infos administratives liées au contrat
            $departement_id = !empty($_POST['departement_id']) ? $_POST['departement_id'] : null;
            $poste_id = !empty($_POST['poste_id']) ? $_POST['poste_id'] : null;
            $statut_agent = $_POST['statut_agent'] ?? 'Actif';
            $role_systeme = $_POST['role_systeme'] ?? 'employee';

            if (empty($type_contrat) || empty($date_debut) || is_null($salaire_base) || empty($poste_id) || empty($departement_id)) {
                echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires (Type, Affectation, Poste, Date début, Salaire).']);
                exit;
            }

            $fichier_contrat_path = handle_upload('fichier_contrat', '../uploads/contracts/');
            if ($fichier_contrat_path) {
                $fichier_contrat_path = str_replace('../', '', $fichier_contrat_path);
            }

            if ($contract_id) {
                $sql = "UPDATE employee_contracts SET 
                        type_contrat = :type_contrat, date_debut = :date_debut, date_fin = :date_fin, 
                        salaire_base = :salaire_base, statut = :statut, commentaire = :commentaire, 
                        poste_id = :poste_id, departement_id = :departement_id";
                if ($fichier_contrat_path) $sql .= ", fichier_contrat = :fichier_contrat";
                $sql .= " WHERE id = :id AND employee_id = :employee_id AND tenant_id = :tenant_id";
                
                $params = [
                    'type_contrat' => $type_contrat, 'date_debut' => $date_debut, 'date_fin' => $date_fin,
                    'salaire_base' => $salaire_base, 'statut' => $statut_contrat, 'commentaire' => $commentaire,
                    'poste_id' => $poste_id, 'departement_id' => $departement_id,
                    'id' => $contract_id, 'employee_id' => $employee_id, 'tenant_id' => $tenant_id
                ];
                if ($fichier_contrat_path) $params['fichier_contrat'] = $fichier_contrat_path;
                $pdo->prepare($sql)->execute($params);
            } else {
                $sql = "INSERT INTO employee_contracts (employee_id, tenant_id, departement_id, poste_id, type_contrat, date_debut, date_fin, salaire_base, statut, fichier_contrat, commentaire) 
                        VALUES (:employee_id, :tenant_id, :departement_id, :poste_id, :type_contrat, :date_debut, :date_fin, :salaire_base, :statut, :fichier_contrat, :commentaire)";
                $pdo->prepare($sql)->execute([
                    'employee_id' => $employee_id, 'tenant_id' => $tenant_id, 'departement_id' => $departement_id,
                    'poste_id' => $poste_id, 'type_contrat' => $type_contrat, 'date_debut' => $date_debut, 
                    'date_fin' => $date_fin, 'salaire_base' => $salaire_base, 'statut' => $statut_contrat, 
                    'fichier_contrat' => $fichier_contrat_path, 'commentaire' => $commentaire
                ]);
            }

            // SYNCHRONISATION : Si le contrat est actif, on met à jour le profil principal de l'agent
            if ($statut_contrat === 'active') {
                $update_emp = $pdo->prepare("UPDATE employees SET 
                                            departement_id = :departement_id, 
                                            poste_id = :poste_id, 
                                            date_embauche = :date_embauche,
                                            statut = :statut, 
                                            role = :role 
                                            WHERE id = :id AND tenant_id = :tenant_id");
                $update_emp->execute([
                    'departement_id' => $departement_id,
                    'poste_id' => $poste_id,
                    'date_embauche' => $date_debut,
                    'statut' => $statut_agent,
                    'role' => $role_systeme,
                    'id' => $employee_id,
                    'tenant_id' => $tenant_id
                ]);
            }

            echo json_encode(['success' => true, 'message' => 'Contrat enregistré et profil administratif synchronisé.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit;
}

require_once '../../config/database.php';
$employee_id = $_GET['employee_id'] ?? null;
$contract_id = $_GET['contract_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'];

$contract = null;
if ($contract_id) {
    $stmt = $pdo->prepare("SELECT * FROM employee_contracts WHERE id = :id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $contract_id, 'tenant_id' => $tenant_id]);
    $contract = $stmt->fetch();
}

$emp = ['poste_id' => '', 'departement_id' => '', 'date_embauche' => '', 'statut' => 'Actif', 'role' => 'employee'];
if ($employee_id) {
    $stmt_emp = $pdo->prepare("SELECT poste_id, departement_id, date_embauche, statut, role FROM employees WHERE id = :id AND tenant_id = :tenant_id");
    $stmt_emp->execute(['id' => $employee_id, 'tenant_id' => $tenant_id]);
    $emp = $stmt_emp->fetch(PDO::FETCH_ASSOC) ?: $emp;
}

$departments = $pdo->prepare("SELECT id, name FROM departments WHERE tenant_id = :tenant_id ORDER BY name ASC");
$departments->execute(['tenant_id' => $tenant_id]);
$departments = $departments->fetchAll(PDO::FETCH_ASSOC);

$postes = $pdo->prepare("SELECT id, nom_poste FROM postes WHERE tenant_id = :tenant_id ORDER BY nom_poste ASC");
$postes->execute(['tenant_id' => $tenant_id]);
$postes = $postes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $contract ? 'Modifier' : 'Ajouter' ?> un contrat & Affectation</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
</div>
<div class="modal-body">
    <form id="contract_form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id ?? '') ?>">
        <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenant_id ?? '') ?>">
        <?php if ($contract): ?>
            <input type="hidden" name="contract_id" value="<?= htmlspecialchars($contract['id'] ?? '') ?>">
        <?php endif; ?>

        <div class="hr-text hr-text-left mt-0 mb-3 text-primary">Poste & Administration</div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Affectation</label>
                    <div class="input-group">
                        <select name="departement_id" id="select-contract-dept" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <?php 
                            $curr_dept = $contract['departement_id'] ?? $emp['departement_id'];
                            foreach ($departments as $dept): 
                            ?>
                                <option value="<?= $dept['id'] ?>" <?= ($curr_dept == $dept['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary btn-icon" type="button" onclick="openQuickAddForContract('departement')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Poste</label>
                    <div class="input-group">
                        <select name="poste_id" id="select-contract-poste" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <?php 
                            $curr_poste = $contract['poste_id'] ?? $emp['poste_id'];
                            foreach ($postes as $poste): 
                            ?>
                                <option value="<?= $poste['id'] ?>" <?= ($curr_poste == $poste['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($poste['nom_poste']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary btn-icon" type="button" onclick="openQuickAddForContract('poste')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Statut de l'agent</label>
                    <select name="statut_agent" class="form-select">
                        <?php $s = $emp['statut']; ?>
                        <option value="Actif" <?= ($s == 'Actif') ? 'selected' : '' ?>>Actif</option>
                        <option value="Inactif" <?= ($s == 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                        <option value="Congé" <?= ($s == 'Congé') ? 'selected' : '' ?>>En Congé</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Rôle Système</label>
                    <select name="role_systeme" class="form-select">
                        <?php $r = $emp['role']; ?>
                        <option value="employee" <?= ($r == 'employee') ? 'selected' : '' ?>>--</option>
                        <option value="superviseur" <?= ($r == 'superviseur') ? 'selected' : '' ?>>Superviseur</option>
                        <option value="manager" <?= ($r == 'manager') ? 'selected' : '' ?>>Manager</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="hr-text hr-text-left mb-3 text-primary">Détails du Contrat</div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Type de contrat</label>
                    <select name="type_contrat" class="form-select" required>
                        <option value="CDI" <?= (($contract['type_contrat'] ?? '') === 'CDI') ? 'selected' : '' ?>>CDI</option>
                        <option value="CDD" <?= (($contract['type_contrat'] ?? '') === 'CDD') ? 'selected' : '' ?>>CDD</option>
                        <option value="Stage" <?= (($contract['type_contrat'] ?? '') === 'Stage') ? 'selected' : '' ?>>Stage</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Salaire de base (USD)</label>
                    <input type="number" step="0.01" name="salaire_base" class="form-control" value="<?= htmlspecialchars($contract['salaire_base'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($contract['date_debut'] ?? $emp['date_embauche']) ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de fin (si CDD/Stage)</label>
                    <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($contract['date_fin'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Statut du contrat</label>
                    <select name="statut_contrat" class="form-select">
                        <option value="active" <?= (($contract['statut'] ?? 'active') === 'active') ? 'selected' : '' ?>>Actif</option>
                        <option value="expired" <?= (($contract['statut'] ?? '') === 'expired') ? 'selected' : '' ?>>Expiré</option>
                        <option value="terminated" <?= (($contract['statut'] ?? '') === 'terminated') ? 'selected' : '' ?>>Rupture</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Fichier joint</label>
                    <input type="file" name="fichier_contrat" class="form-control" accept=".pdf,image/*">
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Observations</label>
                    <textarea name="commentaire" class="form-control" rows="2"><?= htmlspecialchars($contract['commentaire'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="save_contract_button" class="btn btn-primary">Enregistrer et synchroniser</button>
</div>

<script>
window.openQuickAddForContract = function(type) {
    let name = prompt("Entrez le nom du nouveau " + type + " :");
    if (name && name.trim() !== "") {
        let formData = new FormData();
        formData.append('name', name);
        let url = '<?= URLROOT ?>/ajax/ajax_create_' + (type === 'departement' ? 'departement' : 'poste') + '.php';
        
        fetch(url, { method: 'POST', body: formData })
        .then(r => r.json()).then(data => {
            if (data.success) {
                let selectId = type === 'departement' ? 'select-contract-dept' : 'select-contract-poste';
                let select = document.getElementById(selectId);
                let val = type === 'departement' ? data.department.id : data.item.id;
                let text = type === 'departement' ? data.department.name : data.item.name;
                let option = new Option(text, val, true, true);
                select.add(option);
            } else alert(data.message);
        });
    }
}
</script>
