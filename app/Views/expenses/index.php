<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Mes Dépenses</h2>
            </div>
            <div class="col-auto ms-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-expense">
                    Déclarer une dépense
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php flash('expense_message'); ?>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#tab-pending" class="nav-link active" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
                            En attente
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tab-validated" class="nav-link" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                            Validées
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tab-rejected" class="nav-link" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                            Rejetées
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <?php 
                $status_tabs = [
                    'pending' => 'Aucune dépense en attente.',
                    'validated' => 'Aucune dépense validée.',
                    'rejected' => 'Aucune dépense rejetée.'
                ];
                $is_first = true;
                foreach($status_tabs as $status_key => $empty_message): 
                ?>
                <div class="tab-pane <?php echo $is_first ? 'active show' : ''; ?>" id="tab-<?php echo $status_key; ?>">
                    <div class="card-body">
                        <?php if(empty($data['expenses_by_status'][$status_key])): ?>
                            <div class="text-center text-muted py-4">
                                <?php echo $empty_message; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach($data['expenses_by_status'][$status_key] as $mission_name => $expenses): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title">
                                            <?php if($mission_name === 'Administration'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="4" width="16" height="16" rx="2" /><line x1="9" y1="12" x2="15" y2="12" /><line x1="12" y1="9" x2="12" y2="15" /></svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="10" r="4" /><path d="M6.75 16a8.05 8.05 0 0 0 10.5 0" /><path d="M12 18l-2 4l2 -1l2 1z" /></svg>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($mission_name) ?>
                                        </h3>
                                        <div class="card-actions">
                                            <span class="badge bg-blue-lt"><?= count($expenses) ?> ligne(s)</span>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Catégorie / Rubrique</th>
                                                    <th>Montant</th>
                                                    <th>Statut</th>
                                                    <th class="w-1">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_usd = 0;
                                                foreach($expenses as $expense): 
                                                    if($expense->currency == 'USD') $total_usd += $expense->amount;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($expense->expense_date)) ?>
                                                        <?php if($expense->expense_date_end): ?>
                                                            - <?= date('d/m/Y', strtotime($expense->expense_date_end)) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($expense->budget_detail_label ?? ($expense->budget_item_label ?? $expense->category)) ?></div>
                                                        <?php if($expense->description): ?>
                                                            <div class="text-muted small"><?= htmlspecialchars($expense->description) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="fw-bold"><?= number_format($expense->amount, 2) ?> <?= $expense->currency ?></td>
                                                    <td>
                                                        <?php 
                                                            $status_class = 'bg-yellow-lt';
                                                            if($expense->status == 'Validé Superviseur') $status_class = 'bg-azure-lt';
                                                            if($expense->status == 'Validé Manager') $status_class = 'bg-green-lt';
                                                            if($expense->status == 'Rejeté') $status_class = 'bg-red-lt';
                                                            if($expense->status == 'Modification demandée') $status_class = 'bg-orange-lt';
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= htmlspecialchars($expense->status) ?></span>
                                                        <?php if($expense->validation_comment): ?>
                                                            <div class="text-muted small mt-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 20l1.3 -3.9a9 8 0 1 1 3.4 2.9l-4.7 1" /><line x1="12" y1="12" x2="12" y2="12.01" /><line x1="12" y1="9" x2="12" y2="9.01" /><line x1="12" y1="15" x2="12" y2="15.01" /></svg>
                                                                <?= htmlspecialchars($expense->validation_comment) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">Actions</button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <?php if($expense->receipt_path): ?>
                                                                    <a class="dropdown-item" href="<?= URLROOT . '/' . $expense->receipt_path ?>" target="_blank">Voir le reçu</a>
                                                                <?php endif; ?>
                                                                <?php if($expense->status == 'En attente' || $expense->status == 'Rejeté' || $expense->status == 'Modification demandée'): ?>
                                                                    <a href="<?= URLROOT ?>/expenses/edit/<?= $expense->id ?>" class="dropdown-item">Modifier</a>
                                                                    <a href="<?= URLROOT ?>/expenses/delete/<?= $expense->id ?>" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')">Supprimer</a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <td colspan="2" class="text-end fw-bold">Total (USD):</td>
                                                    <td class="fw-bold"><?= number_format($total_usd, 2) ?> USD</td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                $is_first = false;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvelle Dépense (Unchanged) -->
<div class="modal modal-blur fade" id="modal-expense" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/expenses/add" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Déclarer des Dépenses</h5>
                    <div class="ms-auto me-3">
                        <button type="button" class="btn btn-icon btn-ghost-primary" onclick="refreshExpenseLists()" title="Rafraîchir les listes">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type / Mission</label>
                            <select name="mission_id" class="form-select select-mission-global" onchange="handleGlobalMissionChange(this.value)">
                                <option value="">Administration (Dépenses courantes)</option>
                                <optgroup label="Missions" class="optgroup-missions">
                                    <?php foreach($data['missions'] as $mission): ?>
                                        <option value="<?= $mission->id ?>">Mission: <?= htmlspecialchars($mission->title) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <small class="text-muted">Sélectionnez d'abord le contexte pour charger les rubriques appropriées.</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter" id="expenses-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;" id="th-cat-rub">Catégorie</th>
                                    <th style="width: 150px;">Montant</th>
                                    <th style="width: 100px;">Devise</th>
                                    <th style="width: 150px;">Date</th>
                                    <th>Justification & Reçu</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody id="expenses-body">
                                <tr class="expense-row">
                                    <td>
                                        <div class="category-wrapper">
                                            <select name="category[]" class="form-select select-category" required>
                                                <?php foreach($data['admin_charges'] as $charge): ?>
                                                    <option value="<?= htmlspecialchars($charge->name) ?>"><?= htmlspecialchars($charge->name) ?></option>
                                                <?php endforeach; ?>
                                                <?php if(empty($data['admin_charges'])): ?>
                                                    <option value="Transport">Transport</option>
                                                    <option value="Logement">Logement</option>
                                                    <option value="Restauration">Restauration</option>
                                                    <option value="Fournitures">Fournitures</option>
                                                    <option value="Communication">Communication</option>
                                                    <option value="Divers">Divers</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="rubric-wrapper" style="display:none;">
                                            <select name="budget_detail_id[]" class="form-select select-rubric">
                                                <option value="">-- Rubrique --</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="amount[]" class="form-control" placeholder="0.00" required>
                                    </td>
                                    <td>
                                        <select name="currency[]" class="form-select">
                                            <option value="USD">USD</option>
                                            <option value="CDF">CDF</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="date" name="expense_date[]" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            <button type="button" class="btn btn-icon btn-ghost-secondary" onclick="togglePeriod(this)" title="Ajouter une date de fin">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y1="15" width="2" height="2" /></svg>
                                            </button>
                                        </div>
                                        <input type="date" name="expense_date_end[]" class="form-control mt-1 date-end" style="display:none;">
                                    </td>
                                    <td>
                                        <input type="text" name="description[]" class="form-control mb-1" placeholder="Description">
                                        <input type="file" name="receipt[]" class="form-control form-control-sm" accept="image/*,.pdf">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-icon btn-ghost-danger" onclick="removeRow(this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-ghost-primary mt-2" onclick="addRow()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Ajouter une ligne
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer toutes les dépenses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMissionId = '';
let currentOptions = []; // Will store HTML for options

function handleGlobalMissionChange(missionId) {
    currentMissionId = missionId;
    const th = document.getElementById('th-cat-rub');
    
    if (!missionId) {
        th.innerText = 'Catégorie';
        refreshContextOptions();
    } else {
        th.innerText = 'Rubrique Budget';
        fetch('<?= URLROOT ?>/expenses/getMissionRubrics/' + missionId)
            .then(response => response.json())
            .then(data => {
                let html = '<option value="">-- Sélectionner une rubrique --</option>';
                data.forEach(rubric => {
                    const label = (rubric.main_code ? rubric.main_code + ' ' : '') + rubric.code + ' ' + rubric.label;
                    html += `<option value="${rubric.id}">${label}</option>`;
                });
                currentOptions = html;
                updateAllRowsContext();
            });
    }
}

function refreshContextOptions() {
    fetch('<?= URLROOT ?>/expenses/getExpenseFormData')
        .then(response => response.json())
        .then(data => {
            let html = '';
            data.admin_charges.forEach(c => {
                html += `<option value="${c.name}">${c.name}</option>`;
            });
            if (data.admin_charges.length === 0) {
                ['Transport', 'Logement', 'Restauration', 'Fournitures', 'Communication', 'Divers'].forEach(name => {
                    html += `<option value="${name}">${name}</option>`;
                });
            }
            currentOptions = html;
            updateAllRowsContext();
        });
}

function updateAllRowsContext() {
    const isMission = currentMissionId !== '';
    document.querySelectorAll('.expense-row').forEach(row => {
        const catWrapper = row.querySelector('.category-wrapper');
        const rubWrapper = row.querySelector('.rubric-wrapper');
        const selectCat = row.querySelector('.select-category');
        const selectRub = row.querySelector('.select-rubric');
        
        if (isMission) {
            catWrapper.style.display = 'none';
            rubWrapper.style.display = 'block';
            selectCat.removeAttribute('required');
            selectRub.setAttribute('required', 'required');
            selectRub.innerHTML = currentOptions;
        } else {
            catWrapper.style.display = 'block';
            rubWrapper.style.display = 'none';
            selectCat.setAttribute('required', 'required');
            selectRub.removeAttribute('required');
            selectCat.innerHTML = currentOptions;
        }
    });
}

function togglePeriod(btn) {
    const row = btn.closest('td');
    const dateEnd = row.querySelector('.date-end');
    if (dateEnd.style.display === 'none') {
        dateEnd.style.display = 'block';
        btn.classList.replace('btn-ghost-secondary', 'btn-primary');
        dateEnd.setAttribute('required', 'required');
    } else {
        dateEnd.style.display = 'none';
        dateEnd.value = '';
        btn.classList.replace('btn-primary', 'btn-ghost-secondary');
        dateEnd.removeAttribute('required');
    }
}

function addRow() {
    const tbody = document.getElementById('expenses-body');
    const rows = tbody.querySelectorAll('.expense-row');
    const firstRow = rows[0];
    const newRow = firstRow.cloneNode(true);
    
    // Reset values
    newRow.querySelectorAll('input').forEach(input => {
        if (input.type === 'date') {
            input.value = '<?= date('Y-m-d') ?>';
            if (input.classList.contains('date-end')) {
                input.value = '';
                input.style.display = 'none';
                input.removeAttribute('required');
            }
        } else if (input.type === 'number') {
            input.value = '';
        } else {
            input.value = '';
        }
    });

    // Ensure the new row has the correct context (Mission/Admin)
    const isMission = currentMissionId !== '';
    const catWrapper = newRow.querySelector('.category-wrapper');
    const rubWrapper = newRow.querySelector('.rubric-wrapper');
    const selectCat = newRow.querySelector('.select-category');
    const selectRub = newRow.querySelector('.select-rubric');
    
    if (isMission) {
        catWrapper.style.display = 'none';
        rubWrapper.style.display = 'block';
        selectCat.removeAttribute('required');
        selectRub.setAttribute('required', 'required');
        if (currentOptions.length > 0) selectRub.innerHTML = currentOptions;
    } else {
        catWrapper.style.display = 'block';
        rubWrapper.style.display = 'none';
        selectCat.setAttribute('required', 'required');
        selectRub.removeAttribute('required');
        if (currentOptions.length > 0) selectCat.innerHTML = currentOptions;
    }

    // Reset toggle button
    const toggleBtn = newRow.querySelector('button[onclick="togglePeriod(this)"]');
    if (toggleBtn) {
        toggleBtn.classList.remove('btn-primary');
        toggleBtn.classList.add('btn-ghost-secondary');
    }

    tbody.appendChild(newRow);
}

function removeRow(btn) {
    const tbody = document.getElementById('expenses-body');
    if (tbody.querySelectorAll('.expense-row').length > 1) {
        btn.closest('tr').remove();
    } else {
        alert("Vous devez avoir au moins une ligne.");
    }
}

// Initial state
document.addEventListener('DOMContentLoaded', function() {
    refreshContextOptions();
});

function refreshExpenseLists() {
    fetch('<?= URLROOT ?>/expenses/getExpenseFormData')
        .then(response => response.json())
        .then(data => {
            const selectGlobal = document.querySelector('.select-mission-global');
            const currentVal = selectGlobal.value;
            const optgroup = selectGlobal.querySelector('.optgroup-missions');
            optgroup.innerHTML = '';
            data.missions.forEach(m => {
                const opt = new Option('Mission: ' + m.title, m.id);
                if (m.id == currentVal) opt.selected = true;
                optgroup.add(opt);
            });

            if (!currentVal) {
                let html = '';
                data.admin_charges.forEach(c => {
                    html += `<option value="${c.name}">${c.name}</option>`;
                });
                currentOptions = html;
                updateAllRowsContext();
            } else {
                handleGlobalMissionChange(currentVal);
            }
        });
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
