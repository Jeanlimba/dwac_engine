<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
    exit;
}

$tenant_id = $_SESSION['tenant_id'];
$mission_id = $_GET['mission_id'] ?? null;

// Données pour les listes
$partners_stmt = $pdo->prepare("SELECT id, name FROM partners WHERE tenant_id = :tenant_id ORDER BY name ASC");
$partners_stmt->execute(['tenant_id' => $tenant_id]);
$partners = $partners_stmt->fetchAll(PDO::FETCH_ASSOC);

$employees_stmt = $pdo->prepare("SELECT id, prenom, nom FROM employees WHERE tenant_id = :tenant_id ORDER BY nom ASC");
$employees_stmt->execute(['tenant_id' => $tenant_id]);
$employees = $employees_stmt->fetchAll(PDO::FETCH_ASSOC);

$mission = null;
$team = [];

if ($mission_id) {
    // Récupérer la mission
    $stmt = $pdo->prepare("SELECT * FROM missions WHERE id = :id AND tenant_id = :tenant_id");
    $stmt->execute(['id' => $mission_id, 'tenant_id' => $tenant_id]);
    $mission = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer l'équipe
    if ($mission) {
        $stmt_team = $pdo->prepare("SELECT * FROM mission_team WHERE mission_id = :mission_id");
        $stmt_team->execute(['mission_id' => $mission_id]);
        $team = $stmt_team->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $mission ? 'Modifier' : 'Nouvelle' ?> Mission</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="mission_form" method="POST">
    <input type="hidden" name="mission_id" value="<?= $mission['id'] ?? '' ?>">
    <div class="modal-body">
        <div class="row row-cards">
            <div class="col-md-7">
                <div class="mb-2">
                    <label class="form-label">Titre de la mission</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($mission['title'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-5">
                <div class="mb-2">
                    <label class="form-label">Partenaire</label>
                    <div class="input-group">
                        <select name="partner_id" id="select-mission-partner" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach($partners as $partner): ?>
                                <option value="<?= $partner['id'] ?>" <?= (isset($mission['partner_id']) && $mission['partner_id'] == $partner['id']) ? 'selected' : '' ?>><?= htmlspecialchars($partner['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary btn-icon" type="button" onclick="openQuickAddPartner()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_start" class="form-control" value="<?= $mission['date_start'] ?? '' ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_end" class="form-control" value="<?= $mission['date_end'] ?? '' ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <label class="form-label">Nb Jours</label>
                    <input type="number" name="duration_days" class="form-control" value="<?= $mission['duration_days'] ?? '0' ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <label class="form-label">H/Jour</label>
                    <input type="number" step="0.5" name="hours_per_day" class="form-control" value="<?= $mission['hours_per_day'] ?? '0.0' ?>" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-2">
                    <label class="form-label">Moyens de déplacement</label>
                    <div class="row g-2">
                        <?php 
                        $selected_means = isset($mission['means_of_transport']) ? array_map('trim', explode(', ', $mission['means_of_transport'])) : [];
                        $means = ['Véhicule', 'Moto', 'Avion', 'Bateau', 'Autre'];
                        foreach($means as $m): 
                        ?>
                        <div class="col-auto">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="means_of_transport[]" value="<?= $m ?>" <?= in_array($m, $selected_means) ? 'checked' : '' ?>>
                                <span class="form-check-label"><?= $m ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php if($mission): ?>
            <div class="col-md-4">
                <div class="mb-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="En attente" <?= $mission['status'] == 'En attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="En cours" <?= $mission['status'] == 'En cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="Terminée" <?= $mission['status'] == 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                        <option value="Annulée" <?= $mission['status'] == 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-4">
                <div class="mb-2">
                    <label class="form-label">Revenu (USD)</label>
                    <input type="number" step="0.01" name="estimated_revenue" class="form-control" value="<?= $mission['estimated_revenue'] ?? '0.00' ?>" required>
                </div>
            </div>
            <div class="col-md-<?= $mission ? '4' : '8' ?>">
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($mission['description'] ?? '') ?>" placeholder="Brève description...">
                </div>
            </div>
        </div>

        <div class="hr-text hr-text-left mb-3 text-primary">Équipe de mission</div>
        <div id="team-members-container-ajax">
            <?php 
            $team_to_show = !empty($team) ? $team : [['employee_id' => '', 'role_in_mission' => 'Auditeur', 'hourly_rate' => '']];
            foreach($team_to_show as $index => $member): 
            ?>
            <div class="row g-2 team-member-row-ajax mb-2">
                <div class="col-md-5">
                    <select name="team[<?= $index ?>][employee_id]" class="form-select">
                        <option value="">Choisir un employé...</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= $member['employee_id'] == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="team[<?= $index ?>][role]" class="form-select">
                        <option value="Auditeur" <?= $member['role_in_mission'] == 'Auditeur' ? 'selected' : '' ?>>Auditeur</option>
                        <option value="Team Leader" <?= $member['role_in_mission'] == 'Team Leader' ? 'selected' : '' ?>>Team Leader</option>
                        <option value="Expert" <?= $member['role_in_mission'] == 'Expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="number" step="0.01" name="team[<?= $index ?>][hourly_rate]" class="form-control" value="<?= $member['hourly_rate'] ?? '' ?>" placeholder="Taux horaire">
                        <span class="input-group-text">$/h</span>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-icon btn-outline-danger remove-member-btn-ajax"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-member-btn-ajax">
            Ajouter un membre
        </button>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" id="save_mission_button" class="btn btn-primary">Enregistrer</button>
    </div>
</form>

<script>
(function() {
    let memberIndex = <?= count($team_to_show) ?>;
    const container = document.getElementById('team-members-container-ajax');
    const addBtn = document.getElementById('add-member-btn-ajax');

    addBtn.onclick = function() {
        const rows = container.querySelectorAll('.team-member-row-ajax');
        const firstRow = rows[0];
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, '[' + memberIndex + ']');
            el.value = '';
        });
        
        container.appendChild(newRow);
        memberIndex++;
        
        attachRemoveEvent(newRow.querySelector('.remove-member-btn-ajax'));
    };

    function attachRemoveEvent(btn) {
        btn.onclick = function() {
            if (container.querySelectorAll('.team-member-row-ajax').length > 1) {
                btn.closest('.team-member-row-ajax').remove();
            }
        };
    }

    container.querySelectorAll('.remove-member-btn-ajax').forEach(btn => attachRemoveEvent(btn));
})();
</script>
