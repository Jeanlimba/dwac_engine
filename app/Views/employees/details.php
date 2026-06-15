<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Fiche Employé
                </div>
                <h2 class="page-title">
                    <?= htmlspecialchars($data['employee']->prenom . ' ' . $data['employee']->nom) ?>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= URLROOT ?>/employees" class="btn btn-white">
                    Retour à la liste
                </a>
                <?php if (!$is_employee): ?>
                <button onclick="loadEmployeeInfoForm(<?= $data['employee']->id ?>)" class="btn btn-primary">
                    Modifier
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body p-4 text-center">
                        <?php if(!empty($data['employee']->photo)): ?>
                            <span class="avatar avatar-xl mb-3 avatar-rounded" style="background-image: url(<?= URLROOT . '/' . $data['employee']->photo ?>)"></span>
                        <?php else: ?>
                            <span class="avatar avatar-xl mb-3 avatar-rounded"><?= substr($data['employee']->prenom, 0, 1) . substr($data['employee']->nom, 0, 1) ?></span>
                        <?php endif; ?>
                        <h3 class="m-0 mb-1"><?= htmlspecialchars($data['employee']->prenom . ' ' . $data['employee']->nom) ?></h3>
                        <div class="text-muted"><?= htmlspecialchars($data['employee']->matricule ?? 'Sans matricule') ?></div>
                        <div class="mt-3">
                            <span class="badge bg-green-lt"><?= htmlspecialchars($data['employee']->statut ?? 'Actif') ?></span>
                        </div>
                    </div>
                    <div class="d-flex">
                        <a href="mailto:<?= $data['employee']->email ?>" class="card-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="5" width="18" height="14" rx="2" /><polyline points="3 7 12 13 21 7" /></svg>
                            Email
                        </a>
                        <a href="tel:<?= $data['employee']->telephone_professionnel ?>" class="card-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>
                            Appeler
                        </a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Aperçu Professionnel</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Affectation</label>
                            <div class="fw-bold"><?= htmlspecialchars($data['employee']->department_name ?? 'Non assignée') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Poste</label>
                            <div class="fw-bold"><?= htmlspecialchars($data['employee']->poste_name ?? 'Non défini') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Date d'embauche</label>
                            <div><?= !empty($data['employee']->date_embauche) ? date('d/m/Y', strtotime($data['employee']->date_embauche)) : '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                            <li class="nav-item">
                                <a href="#tabs-info" class="nav-link active" data-bs-toggle="tab">Profil Complet</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-contracts" class="nav-link" data-bs-toggle="tab" onclick="loadContracts(<?= $data['employee']->id ?>)">Contrats</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-experiences" class="nav-link" data-bs-toggle="tab" onclick="loadExperiences(<?= $data['employee']->id ?>)">Expériences</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-trainings" class="nav-link" data-bs-toggle="tab" onclick="loadTrainings(<?= $data['employee']->id ?>)">Formations</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-leaves" class="nav-link" data-bs-toggle="tab" onclick="loadLeaves(<?= $data['employee']->id ?>)">Congés</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active show" id="tabs-info">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="card-title">Informations Personnelles</h3>
                                    <?php if (!$is_employee): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="loadEmployeeInfoForm(<?= $data['employee']->id ?>)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -4 -4l-10.5 10.5v4" /><line x1="13.5" y1="6.5" x2="17.5" y2="10.5" /></svg>
                                        Modifier
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="hr-text hr-text-left mt-0 mb-2 text-primary">Identité</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Nom & Postnom</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->nom . ' ' . ($data['employee']->postnom ?? '')) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Prénom</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->prenom ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Genre</div>
                                        <div><?= ($data['employee']->genre == 'Male') ? 'Homme' : (($data['employee']->genre == 'Female') ? 'Femme' : '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Date de naissance</div>
                                        <div><?= !empty($data['employee']->date_naissance) ? date('d/m/Y', strtotime($data['employee']->date_naissance)) : '-' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Lieu de naissance</div>
                                        <div><?= htmlspecialchars($data['employee']->lieu_naissance ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Nationalité</div>
                                        <div><?= htmlspecialchars($data['employee']->nationalite ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Province d'origine</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->province ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">État Civil</div>
                                        <div><?= htmlspecialchars($data['employee']->etat_civil ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Nombre d'enfants</div>
                                        <div><?= htmlspecialchars($data['employee']->nombre_enfants ?? '0') ?></div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="hr-text hr-text-left mb-2 text-primary">Coordonnées & Contact</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Email Professionnel</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->email ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Téléphone Professionnel</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->telephone_professionnel ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Email Personnel</div>
                                        <div><?= htmlspecialchars($data['employee']->email_personnel ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Téléphone Personnel</div>
                                        <div><?= htmlspecialchars($data['employee']->telephone_personnel ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-label text-muted small">Adresse de résidence</div>
                                        <div><?= htmlspecialchars($data['employee']->adresse ?? '-') ?></div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="hr-text hr-text-left mb-2 text-primary">Urgence</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Personne à contacter</div>
                                        <div class="fw-bold"><?= htmlspecialchars($data['employee']->personne_contact ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label text-muted small">Téléphone d'urgence</div>
                                        <div><?= htmlspecialchars($data['employee']->telephone_contact ?? '-') ?></div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="hr-text hr-text-left mb-2 text-primary">Administration & Compte</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Matricule</div>
                                        <div class="badge bg-blue-lt fw-bold"><?= htmlspecialchars($data['employee']->matricule ?? '-') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Identifiant (Login)</div>
                                        <div class="fw-bold text-primary"><?= htmlspecialchars($data['employee']->username ?? 'Aucun compte') ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-label text-muted small">Mot de passe</div>
                                        <div class="text-muted small"><em>Par défaut (password123)</em></div>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <div class="form-label text-muted small">Rôle Système</div>
                                        <?php 
                                            $role_label = '--';
                                            $role_class = 'bg-secondary-lt';
                                            if($data['employee']->role == 'superviseur') {
                                                $role_label = 'Superviseur';
                                                $role_class = 'bg-purple-lt';
                                            } elseif($data['employee']->role == 'manager') {
                                                $role_label = 'Manager';
                                                $role_class = 'bg-blue-lt';
                                            }
                                        ?>
                                        <div class="badge <?= $role_class ?>"><?= $role_label ?></div>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <div class="form-label text-muted small">Statut</div>
                                        <div class="badge bg-green-lt"><?= htmlspecialchars($data['employee']->statut ?? 'Actif') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-contracts">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>Historique des Contrats</h4>
                                    <?php if (!$is_employee): ?>
                                    <button class="btn btn-sm btn-primary" onclick="loadContractForm(<?= $data['employee']->id ?>)">Ajouter</button>
                                    <?php endif; ?>
                                </div>
                                <div id="contracts-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-experiences">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>Expériences Professionnelles</h4>
                                    <?php if (!$is_employee): ?>
                                    <button class="btn btn-sm btn-primary" onclick="loadExperienceForm(<?= $data['employee']->id ?>)">Ajouter</button>
                                    <?php endif; ?>
                                </div>
                                <div id="experiences-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-trainings">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>Formations & Certifications</h4>
                                    <?php if (!$is_employee): ?>
                                    <button class="btn btn-sm btn-primary" onclick="loadTrainingForm(<?= $data['employee']->id ?>)">Ajouter</button>
                                    <?php endif; ?>
                                </div>
                                <div id="trainings-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-leaves">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>Demandes de Congés</h4>
                                    <?php if (!$is_employee): ?>
                                    <button class="btn btn-sm btn-primary" onclick="loadLeaveForm(<?= $data['employee']->id ?>)">Nouvelle demande</button>
                                    <?php endif; ?>
                                </div>
                                <div id="leaves-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-ajax" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modal-ajax-content">
            <!-- Loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function loadContracts(employeeId) {
    const listContainer = document.getElementById('contracts-list');
    listContainer.innerHTML = '<div class="text-muted text-center py-4">Chargement des contrats...</div>';
    
    fetch('<?= URLROOT ?>/ajax/get_employee_contracts.php?employee_id=' + employeeId)
        .then(response => response.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            listContainer.innerHTML = '<div class="text-danger text-center py-4">Erreur lors du chargement.</div>';
        });
}

function loadContractForm(employeeId, contractId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement du formulaire...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/employee_contract_form.php?employee_id=' + employeeId;
    if (contractId) url += '&contract_id=' + contractId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const range = document.createRange();
            range.selectNode(modalContent);
            const fragment = range.createContextualFragment(html);
            modalContent.innerHTML = '';
            modalContent.appendChild(fragment);
            
            const saveBtn = document.getElementById('save_contract_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('contract_form');
                    const formData = new FormData(form);
                    formData.append('save_contract', '1');
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/employee_contract_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            modal.hide();
                            loadContracts(employeeId);
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

function loadExperiences(employeeId) {
    const listContainer = document.getElementById('experiences-list');
    listContainer.innerHTML = '<div class="text-muted text-center py-4">Chargement des expériences...</div>';
    
    fetch('<?= URLROOT ?>/ajax/get_employee_experiences.php?employee_id=' + employeeId)
        .then(response => response.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            listContainer.innerHTML = '<div class="text-danger text-center py-4">Erreur lors du chargement.</div>';
        });
}

function loadExperienceForm(employeeId, experienceId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement du formulaire...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/employee_experience_form.php?employee_id=' + employeeId;
    if (experienceId) url += '&experience_id=' + experienceId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const range = document.createRange();
            range.selectNode(modalContent);
            const fragment = range.createContextualFragment(html);
            modalContent.innerHTML = '';
            modalContent.appendChild(fragment);
            
            const saveBtn = document.getElementById('save_experience_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('experience_form');
                    const formData = new FormData(form);
                    formData.append('save_experience', '1');
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/employee_experience_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            modal.hide();
                            loadExperiences(employeeId);
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

function deleteExperience(employeeId, experienceId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette expérience ?')) {
        const formData = new FormData();
        formData.append('experience_id', experienceId);
        formData.append('action', 'delete');

        fetch('<?= URLROOT ?>/ajax/employee_experience_form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadExperiences(employeeId);
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur lors de la suppression');
        });
    }
}

function loadLeaves(employeeId) {
    const listContainer = document.getElementById('leaves-list');
    listContainer.innerHTML = '<div class="text-muted text-center py-4">Chargement des congés...</div>';
    
    fetch('<?= URLROOT ?>/ajax/get_employee_leaves.php?employee_id=' + employeeId)
        .then(response => response.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            listContainer.innerHTML = '<div class="text-danger text-center py-4">Erreur lors du chargement.</div>';
        });
}

function loadLeaveForm(employeeId, requestId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement du formulaire...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/leave_request_form.php?employee_id=' + employeeId;
    if (requestId) url += '&request_id=' + requestId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const range = document.createRange();
            range.selectNode(modalContent);
            const fragment = range.createContextualFragment(html);
            modalContent.innerHTML = '';
            modalContent.appendChild(fragment);
            
            const saveBtn = document.getElementById('save_leave_request_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('leave_request_form');
                    const formData = new FormData(form);
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/leave_request_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            modal.hide();
                            loadLeaves(employeeId);
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

function loadTrainings(employeeId) {
    const listContainer = document.getElementById('trainings-list');
    listContainer.innerHTML = '<div class="text-muted text-center py-4">Chargement des formations...</div>';
    
    fetch('<?= URLROOT ?>/ajax/get_employee_trainings.php?employee_id=' + employeeId)
        .then(response => response.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            listContainer.innerHTML = '<div class="text-danger text-center py-4">Erreur lors du chargement.</div>';
        });
}

function loadTrainingForm(employeeId, trainingId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement du formulaire...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/training_form.php?employee_id=' + employeeId;
    if (trainingId) url += '&training_id=' + trainingId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const range = document.createRange();
            range.selectNode(modalContent);
            const fragment = range.createContextualFragment(html);
            modalContent.innerHTML = '';
            modalContent.appendChild(fragment);
            
            const saveBtn = document.getElementById('save_training_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('training_form');
                    const formData = new FormData(form);
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/training_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            modal.hide();
                            loadTrainings(employeeId);
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

function deleteTraining(employeeId, trainingId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette formation ?')) {
        const formData = new FormData();
        formData.append('training_id', trainingId);
        formData.append('action', 'delete');

        fetch('<?= URLROOT ?>/ajax/training_form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTrainings(employeeId);
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur lors de la suppression');
        });
    }
}

function loadEmployeeInfoForm(employeeId = null) {
    const modalElement = document.getElementById('modal-ajax');
    const modalContent = document.getElementById('modal-ajax-content');
    
    if (!modalElement || !modalContent) {
        console.error('Modal elements not found');
        return;
    }

    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
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

// Initial loading of lists
document.addEventListener('DOMContentLoaded', function() {
   loadContracts(<?= $data['employee']->id ?>);
   loadExperiences(<?= $data['employee']->id ?>);
   loadTrainings(<?= $data['employee']->id ?>);
   loadLeaves(<?= $data['employee']->id ?>);
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
