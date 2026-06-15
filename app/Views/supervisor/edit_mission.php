<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Mission</div>
                <h2 class="page-title">Modifier la Mission</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= URLROOT ?>/supervisor/missionDetails/<?= $data['mission']->id ?>" class="btn btn-white">Annuler</a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form action="<?= URLROOT ?>/supervisor/editMission/<?= $data['mission']->id ?>" method="POST">
                    <div class="row row-cards">
                        <div class="col-md-7">
                            <div class="mb-2">
                                <label class="form-label">Titre de la mission</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['mission']->title) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-2">
                                <label class="form-label">Partenaire</label>
                                <div class="input-group">
                                    <select name="partner_id" id="select-mission-partner" class="form-select" required>
                                        <?php foreach($data['partners'] as $partner): ?>
                                            <option value="<?= $partner->id ?>" <?= $data['mission']->partner_id == $partner->id ? 'selected' : '' ?>><?= htmlspecialchars($partner->name) ?></option>
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
                                <input type="date" name="date_start" class="form-control" value="<?= $data['mission']->date_start ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_end" class="form-control" value="<?= $data['mission']->date_end ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Nb Jours</label>
                                <input type="number" name="duration_days" class="form-control" value="<?= $data['mission']->duration_days ?? '0' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">H/Jour</label>
                                <input type="number" step="0.5" name="hours_per_day" class="form-control" value="<?= $data['mission']->hours_per_day ?? '0.0' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label">Moyens de déplacement</label>
                                <div class="row g-2">
                                    <?php 
                                    $selected_means = isset($data['mission']->means_of_transport) ? array_map('trim', explode(', ', $data['mission']->means_of_transport)) : [];
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
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="En attente" <?= $data['mission']->status == 'En attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="En cours" <?= $data['mission']->status == 'En cours' ? 'selected' : '' ?>>En cours</option>
                                    <option value="Terminée" <?= $data['mission']->status == 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                                    <option value="Annulée" <?= $data['mission']->status == 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Revenu (USD)</label>
                                <input type="number" step="0.01" name="estimated_revenue" class="form-control" value="<?= $data['mission']->estimated_revenue ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($data['mission']->description) ?>" placeholder="Brève description...">
                            </div>
                        </div>
                    </div>

                    <div class="hr-text hr-text-left mb-3 text-primary">Équipe de mission</div>
                    <div id="team-members-container">
                        <?php foreach($data['team'] as $index => $member): ?>
                        <div class="row g-2 team-member-row mb-2">
                            <div class="col-md-5">
                                <select name="team[<?= $index ?>][employee_id]" class="form-select">
                                    <option value="">Choisir un employé...</option>
                                    <?php foreach($data['employees'] as $emp): ?>
                                        <option value="<?= $emp->id ?>" <?= $member->employee_id == $emp->id ? 'selected' : '' ?>><?= htmlspecialchars($emp->prenom . ' ' . $emp->nom) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="team[<?= $index ?>][role]" class="form-select">
                                    <option value="Auditeur" <?= $member->role_in_mission == 'Auditeur' ? 'selected' : '' ?>>Auditeur</option>
                                    <option value="Team Leader" <?= $member->role_in_mission == 'Team Leader' ? 'selected' : '' ?>>Team Leader</option>
                                    <option value="Expert" <?= $member->role_in_mission == 'Expert' ? 'selected' : '' ?>>Expert</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.01" name="team[<?= $index ?>][hourly_rate]" class="form-control" value="<?= $member->hourly_rate ?>" placeholder="Taux horaire">
                                    <span class="input-group-text">$/h</span>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-icon btn-outline-danger remove-member-btn"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['team'])): ?>
                        <div class="row g-2 team-member-row mb-2">
                            <div class="col-md-5">
                                <select name="team[0][employee_id]" class="form-select">
                                    <option value="">Choisir un employé...</option>
                                    <?php foreach($data['employees'] as $emp): ?>
                                        <option value="<?= $emp->id ?>"><?= htmlspecialchars($emp->prenom . ' ' . $emp->nom) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="team[0][role]" class="form-select">
                                    <option value="Auditeur">Auditeur</option>
                                    <option value="Team Leader">Team Leader</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" step="0.01" name="team[0][hourly_rate]" class="form-control" placeholder="Taux horaire">
                                    <span class="input-group-text">$/h</span>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-icon btn-outline-danger remove-member-btn"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-member-btn">
                        Ajouter un membre
                    </button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let memberIndex = <?= max(1, count($data['team'])) ?>;
    const container = document.getElementById('team-members-container');
    const addBtn = document.getElementById('add-member-btn');

    addBtn.addEventListener('click', function() {
        const firstRow = container.querySelector('.team-member-row');
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, '[' + memberIndex + ']');
            el.value = '';
        });
        
        container.appendChild(newRow);
        memberIndex++;
        
        attachRemoveEvent(newRow.querySelector('.remove-member-btn'));
    });

    function attachRemoveEvent(btn) {
        btn.addEventListener('click', function() {
            if (container.querySelectorAll('.team-member-row').length > 1) {
                btn.closest('.team-member-row').remove();
            }
        });
    }

    container.querySelectorAll('.remove-member-btn').forEach(btn => attachRemoveEvent(btn));
});

function openQuickAddPartner() {
    var quickModalElement = document.getElementById('modal-quick-partner');
    var quickModal = bootstrap.Modal.getInstance(quickModalElement);
    if (!quickModal) quickModal = new bootstrap.Modal(quickModalElement);
    quickModal.show();
}
</script>

<div class="modal modal-blur fade" id="modal-quick-partner" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Partenaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom du partenaire</label>
                    <input type="text" id="quick-partner-name" class="form-control" placeholder="Entrez le nom...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-quick-partner-save">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-quick-partner-save').onclick = function() {
    let name = document.getElementById('quick-partner-name').value;
    if (name && name.trim() !== "") {
        let formData = new FormData();
        formData.append('name', name);
        this.disabled = true;
        fetch('<?= URLROOT ?>/ajax/ajax_create_partner.php', { method: 'POST', body: formData })
        .then(r => r.json()).then(data => {
            this.disabled = false;
            if (data.success) {
                let select = document.getElementById('select-mission-partner');
                let option = new Option(data.partner.name, data.partner.id, true, true);
                select.add(option);
                bootstrap.Modal.getInstance(document.getElementById('modal-quick-partner')).hide();
            } else alert(data.message);
        });
    }
};
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
