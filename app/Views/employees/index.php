<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Répertoire des Employés
                </h2>
                <div class="text-muted mt-1"><?= count($data['employees']) ?> membre(s) enregistré(s)</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="loadEmployeeInfoForm()" class="btn btn-primary">
                    Ajouter un employé
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
                            <th>Employé</th>
                            <th>Contact</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Présence</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['employees'] as $employee): ?>
                            <tr>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <?php if(!empty($employee->photo)): ?>
                                            <span class="avatar me-2" style="background-image: url(<?= URLROOT . '/' . $employee->photo ?>)"></span>
                                        <?php else: ?>
                                            <span class="avatar me-2"><?= substr($employee->prenom, 0, 1) . substr($employee->nom, 0, 1) ?></span>
                                        <?php endif; ?>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium"><?= htmlspecialchars($employee->prenom . ' ' . $employee->nom) ?></div>
                                            <div class="text-muted"><small>Matricule: <?= htmlspecialchars($employee->matricule) ?></small></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($employee->email) ?></div>
                                    <div class="text-muted"><small><?= htmlspecialchars($employee->telephone_professionnel) ?></small></div>
                                </td>
                                <td>
                                    <?php 
                                        $role_label = '--';
                                        $role_class = 'bg-secondary-lt';
                                        if($employee->role == 'superviseur') {
                                            $role_label = 'Superviseur';
                                            $role_class = 'bg-purple-lt';
                                        } elseif($employee->role == 'manager') {
                                            $role_label = 'Manager';
                                            $role_class = 'bg-blue-lt';
                                        }
                                    ?>
                                    <span class="badge <?= $role_class ?>"><?= $role_label ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $status_class = 'bg-green';
                                        if($employee->statut == 'Inactif') $status_class = 'bg-red';
                                        if($employee->statut == 'Congé') $status_class = 'bg-yellow';
                                    ?>
                                    <span class="status status-dot <?= $status_class ?>"></span> <?= htmlspecialchars($employee->statut) ?>
                                </td>
                                <td>
                                    <?php if (!empty($employee->zk_id)): ?>
                                        <span class="badge bg-success-lt" title="Enrôlé sur la pointeuse (ID machine <?= (int) $employee->zk_id ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-fingerprint me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3" /><path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6" /><path d="M12 11v2a14 14 0 0 0 2.5 8" /><path d="M8 15a18 18 0 0 0 1.8 6" /><path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95" /></svg>
                                            Enrôlé
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-lt" title="Non enrôlé sur la pointeuse">Non enrôlé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="<?= URLROOT ?>/employees/details/<?= $employee->id ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                Détails
                                            </a>
                                            <button class="dropdown-item" onclick="loadEmployeeForm(<?= $employee->id ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                                Modifier
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="<?= URLROOT ?>/employees/delete/<?= $employee->id ?>" class="m-0" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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

<script>
function loadEmployeeInfoForm(employeeId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    if (!modalElement || !modalContent) {
        console.error('Modal elements not found');
        return;
    }

    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) {
        modal = new bootstrap.Modal(modalElement);
    }
    modal.show();

    let url = '<?= URLROOT ?>/ajax/employee_info_form.php';
    if(employeeId) url += '?employee_id=' + employeeId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const range = document.createRange();
            range.selectNode(modalContent);
            const fragment = range.createContextualFragment(html);
            modalContent.innerHTML = '';
            modalContent.appendChild(fragment);

            const saveBtn = document.getElementById('save_employee_info_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('employee_info_form');
                    const formData = new FormData(form);
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/employee_info_form.php', {
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
            }
        });
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
