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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
        exit;
    }
    csrf_check_or_die(); // Protection CSRF

    $employee_id = $_POST['id'] ?? null;
    $tenant_id = $_SESSION['tenant_id'];

    $data = [
        'tenant_id' => $tenant_id,
        'matricule' => $_POST['matricule'] ?? '',
        'nom' => $_POST['nom'] ?? '',
        'postnom' => $_POST['postnom'] ?? '',
        'prenom' => $_POST['prenom'] ?? '',
        'genre' => $_POST['genre'] ?? '',
        'etat_civil' => $_POST['etat_civil'] ?? '',
        'date_naissance' => !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null,
        'lieu_naissance' => $_POST['lieu_naissance'] ?? '',
        'nationalite' => $_POST['nationalite'] ?? '',
        'adresse' => $_POST['adresse'] ?? '',
        'province' => $_POST['province'] ?? '',
        'telephone_personnel' => $_POST['telephone_personnel'] ?? '',
        'telephone_professionnel' => $_POST['telephone_professionnel'] ?? '',
        'email_personnel' => $_POST['email_personnel'] ?? '',
        'email' => $_POST['email'] ?? '',
        'nombre_enfants' => !empty($_POST['nombre_enfants']) ? $_POST['nombre_enfants'] : 0,
        'personne_contact' => $_POST['personne_contact'] ?? '',
        'telephone_contact' => $_POST['telephone_contact'] ?? '',
        'role' => $_POST['role'] ?? 'employee',
        'statut' => $_POST['statut'] ?? 'Actif',
    ];

    $photo_path = handle_upload('photo', '../uploads/photos/');
    if ($photo_path) {
        $data['photo'] = str_replace('../', '', $photo_path);
    }

    try {
        if ($employee_id) {
            $sql = "UPDATE employees SET 
                    matricule = :matricule, nom = :nom, postnom = :postnom, prenom = :prenom, 
                    genre = :genre, etat_civil = :etat_civil, date_naissance = :date_naissance, 
                    lieu_naissance = :lieu_naissance, nationalite = :nationalite, adresse = :adresse, 
                    province = :province, telephone_personnel = :telephone_personnel, 
                    telephone_professionnel = :telephone_professionnel, email_personnel = :email_personnel, 
                    email = :email, nombre_enfants = :nombre_enfants, personne_contact = :personne_contact, 
                    telephone_contact = :telephone_contact, role = :role, statut = :statut" . 
                    ($photo_path ? ", photo = :photo" : "") . 
                    " WHERE id = :id AND tenant_id = :tenant_id";
            
            $data['id'] = $employee_id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $message = 'Informations personnelles mises à jour.';
        } else {
            if (!isset($data['photo'])) $data['photo'] = null;
            $sql = "INSERT INTO employees (tenant_id, matricule, nom, postnom, prenom, genre, etat_civil, 
                    date_naissance, lieu_naissance, nationalite, adresse, province, 
                    telephone_personnel, telephone_professionnel, email_personnel, email, 
                    nombre_enfants, personne_contact, telephone_contact, role, statut, photo) 
                    VALUES (:tenant_id, :matricule, :nom, :postnom, :prenom, :genre, :etat_civil, 
                    :date_naissance, :lieu_naissance, :nationalite, :adresse, :province, 
                    :telephone_personnel, :telephone_professionnel, :email_personnel, :email, 
                    :nombre_enfants, :personne_contact, :telephone_contact, :role, :statut, :photo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $new_employee_id = $pdo->lastInsertId();

            // Automatiquement créer un compte utilisateur
            $stmt_tenant = $pdo->prepare("SELECT acronym FROM tenants WHERE id = :id");
            $stmt_tenant->execute(['id' => $tenant_id]);
            $tenant = $stmt_tenant->fetch();
            $acronym = $tenant ? $tenant['acronym'] : 'EVO';

            $username = generate_unique_username($pdo, $data['prenom'], $data['nom'], $acronym, $tenant_id);
            $default_password = password_hash(defined('DEFAULT_PASSWORD') ? DEFAULT_PASSWORD : 'password123', PASSWORD_DEFAULT);

            $stmt_user = $pdo->prepare("INSERT INTO users (tenant_id, employee_id, username, password, is_super_admin) 
                                       VALUES (:tenant_id, :employee_id, :username, :password, 0)");
            $stmt_user->execute([
                'tenant_id' => $tenant_id,
                'employee_id' => $new_employee_id,
                'username' => $username,
                'password' => $default_password
            ]);

            $message = 'Employé créé et compte utilisateur généré : ' . $username . '. Veuillez maintenant lui assigner un contrat.';
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit;
}

$employee_id = $_GET['employee_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'];
$employee = null;
if ($employee_id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $employee_id, 'tenant_id' => $tenant_id]);
    $employee = $stmt->fetch();
}

$nat_stmt = $pdo->prepare("SELECT name FROM nationalities WHERE tenant_id = :tenant_id ORDER BY name");
$nat_stmt->execute(['tenant_id' => $tenant_id]);
$nationalities = $nat_stmt->fetchAll(PDO::FETCH_COLUMN);

$prov_stmt = $pdo->prepare("SELECT name FROM provinces WHERE tenant_id = :tenant_id ORDER BY name");
$prov_stmt->execute(['tenant_id' => $tenant_id]);
$provinces = $prov_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $employee ? 'Modifier' : 'Ajouter' ?> un employé</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="employee_info_form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $employee['id'] ?? '' ?>">
        
        <div class="row">
            <div class="col-md-12 text-center mb-3">
                <div class="form-label">Photo de profil</div>
                <?php if(!empty($employee['photo'])): ?>
                    <img src="<?= URLROOT . '/' . $employee['photo'] ?>" class="avatar avatar-xl mb-2 avatar-rounded">
                <?php endif; ?>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            
            <div class="col-md-12">
                <div class="hr-text hr-text-left mt-0 mb-3 text-primary">Identité</div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Matricule</label>
                    <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($employee['matricule'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($employee['nom'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Postnom</label>
                    <input type="text" name="postnom" class="form-control" value="<?= htmlspecialchars($employee['postnom'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($employee['prenom'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Genre</label>
                    <select name="genre" class="form-select">
                        <option value="Male" <?= (isset($employee['genre']) && $employee['genre'] == 'Male') ? 'selected' : '' ?>>Homme</option>
                        <option value="Female" <?= (isset($employee['genre']) && $employee['genre'] == 'Female') ? 'selected' : '' ?>>Femme</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">État Civil</label>
                    <select name="etat_civil" class="form-select">
                        <option value="Célibataire" <?= (isset($employee['etat_civil']) && $employee['etat_civil'] == 'Célibataire') ? 'selected' : '' ?>>Célibataire</option>
                        <option value="Marié" <?= (isset($employee['etat_civil']) && $employee['etat_civil'] == 'Marié') ? 'selected' : '' ?>>Marié</option>
                        <option value="Divorcé" <?= (isset($employee['etat_civil']) && $employee['etat_civil'] == 'Divorcé') ? 'selected' : '' ?>>Divorcé</option>
                        <option value="Veuf" <?= (isset($employee['etat_civil']) && $employee['etat_civil'] == 'Veuf') ? 'selected' : '' ?>>Veuf</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($employee['date_naissance'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control" value="<?= htmlspecialchars($employee['lieu_naissance'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nationalité</label>
                    <div class="input-group">
                        <select name="nationalite" id="select-nationalite" class="form-select">
                            <option value="">Sélectionner...</option>
                            <?php foreach ($nationalities as $nat): ?>
                                <option value="<?= htmlspecialchars($nat) ?>" <?= (isset($employee['nationalite']) && $employee['nationalite'] == $nat) ? 'selected' : '' ?>><?= htmlspecialchars($nat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-icon" onclick="openQuickAddModal('nationalite')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Province d'origine</label>
                    <div class="input-group">
                        <select name="province" id="select-province" class="form-select">
                            <option value="">Sélectionner...</option>
                            <?php foreach ($provinces as $prov): ?>
                                <option value="<?= htmlspecialchars($prov) ?>" <?= (isset($employee['province']) && $employee['province'] == $prov) ? 'selected' : '' ?>><?= htmlspecialchars($prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-icon" onclick="openQuickAddModal('province')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nombre d'enfants</label>
                    <input type="number" name="nombre_enfants" class="form-control" value="<?= htmlspecialchars($employee['nombre_enfants'] ?? '0') ?>">
                </div>
            </div>

            <div class="col-md-12">
                <div class="hr-text hr-text-left mb-3 text-primary">Coordonnées</div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email Professionnel</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Téléphone Professionnel</label>
                    <input type="text" name="telephone_professionnel" class="form-control" value="<?= htmlspecialchars($employee['telephone_professionnel'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email Personnel</label>
                    <input type="email" name="email_personnel" class="form-control" value="<?= htmlspecialchars($employee['email_personnel'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Téléphone Personnel</label>
                    <input type="text" name="telephone_personnel" class="form-control" value="<?= htmlspecialchars($employee['telephone_personnel'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Adresse de résidence</label>
                    <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($employee['adresse'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-12">
                <div class="hr-text hr-text-left mb-3 text-primary">Contact d'Urgence</div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Personne à contacter</label>
                    <input type="text" name="personne_contact" class="form-control" value="<?= htmlspecialchars($employee['personne_contact'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Téléphone d'urgence</label>
                    <input type="text" name="telephone_contact" class="form-control" value="<?= htmlspecialchars($employee['telephone_contact'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-12">
                <div class="hr-text hr-text-left mb-3 text-primary">Administration</div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Rôle Système</label>
                    <select name="role" class="form-select">
                        <option value="employee" <?= (isset($employee['role']) && $employee['role'] == 'employee') ? 'selected' : '' ?>>--</option>
                        <option value="superviseur" <?= (isset($employee['role']) && $employee['role'] == 'superviseur') ? 'selected' : '' ?>>Superviseur</option>
                        <option value="manager" <?= (isset($employee['role']) && $employee['role'] == 'manager') ? 'selected' : '' ?>>Manager</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="Actif" <?= (isset($employee['statut']) && $employee['statut'] == 'Actif') ? 'selected' : '' ?>>Actif</option>
                        <option value="Inactif" <?= (isset($employee['statut']) && $employee['statut'] == 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                        <option value="Congé" <?= (isset($employee['statut']) && $employee['statut'] == 'Congé') ? 'selected' : '' ?>>En Congé</option>
                        <option value="Suspendu" <?= (isset($employee['statut']) && $employee['statut'] == 'Suspendu') ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="save_employee_info_button" class="btn btn-primary">Enregistrer</button>
</div>

<!-- Modal Rapide -->
<div class="modal modal-blur fade" id="modal-quick-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quick-add-title">Ajouter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" id="quick-add-name" class="form-control" placeholder="Entrez le nom...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-quick-add-save">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
window.currentQuickAddType = '';
window.openQuickAddModal = function(type) {
    window.currentQuickAddType = type;
    let title = "Ajouter ";
    switch(type) {
        case 'nationalite': title += "une Nationalité"; break;
        case 'province': title += "une Province d'origine"; break;
    }
    document.getElementById('quick-add-title').innerText = title;
    document.getElementById('quick-add-name').value = '';
    
    var quickModalElement = document.getElementById('modal-quick-add');
    var quickModal = bootstrap.Modal.getInstance(quickModalElement);
    if (!quickModal) quickModal = new bootstrap.Modal(quickModalElement);
    quickModal.show();
}

document.getElementById('btn-quick-add-save').onclick = function() {
    let name = document.getElementById('quick-add-name').value;
    if (name && name.trim() !== "") {
        let type = window.currentQuickAddType;
        let url = '<?= URLROOT ?>/ajax/ajax_create_' + type + '.php';
        let formData = new FormData();
        formData.append('name', name);
        this.disabled = true;
        fetch(url, { method: 'POST', body: formData })
        .then(r => r.json()).then(data => {
            this.disabled = false;
            if (data.success) {
                let selectId = 'select-' + (type === 'nationalite' ? 'nationalite' : 'province');
                let select = document.getElementById(selectId);
                let option = new Option(data.item.name, data.item.name, true, true);
                select.add(option);
                bootstrap.Modal.getInstance(document.getElementById('modal-quick-add')).hide();
            } else alert(data.message);
        });
    }
};
</script>
