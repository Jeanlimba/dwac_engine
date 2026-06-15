<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Gestion des Missions</h2>
            </div>
            <div class="col-auto ms-auto">
                <button onclick="loadMissionForm()" class="btn btn-primary">
                    Nouvelle Mission
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Titre</th>
                            <th>Partenaire</th>
                            <th>Dates</th>
                            <th>Revenu Est.</th>
                            <th>Statut</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['missions'] as $mission): ?>
                        <tr>
                            <td><span class="badge bg-blue-lt"><?= htmlspecialchars($mission->mission_code ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($mission->title) ?></td>
                            <td><?= htmlspecialchars($mission->partner_name) ?></td>
                            <td class="text-muted">
                                <?= date('d/m/Y', strtotime($mission->date_start)) ?> - <?= date('d/m/Y', strtotime($mission->date_end)) ?>
                            </td>
                            <td><?= number_format($mission->estimated_revenue, 2) ?> USD</td>
                            <td>
                                <span class="badge bg-blue-lt"><?= htmlspecialchars($mission->status) ?></span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/missionDetails/<?= $mission->id ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                            Détails
                                        </a>
                                        <button class="dropdown-item" onclick="loadMissionForm(<?= $mission->id ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                            Modifier
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="<?= URLROOT ?>/supervisor/deleteMission/<?= $mission->id ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette mission ?');">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                            Supprimer
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data['missions'])): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune mission enregistrée.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-ajax" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modal-ajax-content">
            <!-- Chargé via AJAX -->
        </div>
    </div>
</div>

<!-- Modal Quick Add Partner -->
<div class="modal modal-blur fade" id="modal-quick-partner" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
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
function loadMissionForm(missionId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/mission_form.php';
    if(missionId) url += '?mission_id=' + missionId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            
            const scripts = modalContent.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                newScript.text = oldScript.text;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

            const form = document.getElementById('mission_form');
            form.onsubmit = function(e) {
                e.preventDefault();
                const saveBtn = document.getElementById('save_mission_button');
                const formData = new FormData(form);
                
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                fetch('<?= URLROOT ?>/supervisor/saveMissionAjax', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erreur');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Enregistrer';
                    }
                })
                .catch(err => {
                    console.error(err);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Enregistrer';
                });
            };
        });
}

function openQuickAddPartner() {
    var quickModalElement = document.getElementById('modal-quick-partner');
    var quickModal = bootstrap.Modal.getInstance(quickModalElement);
    if (!quickModal) quickModal = new bootstrap.Modal(quickModalElement);
    quickModal.show();
}

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
                if (select) {
                    let option = new Option(data.partner.name, data.partner.id, true, true);
                    select.add(option);
                }
                bootstrap.Modal.getInstance(document.getElementById('modal-quick-partner')).hide();
            } else alert(data.message);
        });
    }
};
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
