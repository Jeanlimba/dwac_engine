<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Mission</div>
                <h2 class="page-title"><?= htmlspecialchars($data['mission']->title) ?></h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= URLROOT ?>/supervisor/missions" class="btn btn-white">Retour</a>
                <a href="<?= URLROOT ?>/supervisor/editMission/<?= $data['mission']->id ?>" class="btn btn-primary">Modifier</a>
                <form method="POST" action="<?= URLROOT ?>/supervisor/deleteMission/<?= $data['mission']->id ?>" class="d-inline" onsubmit="return confirm('Supprimer cette mission ?')">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Informations</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Code Mission</label>
                            <div class="badge bg-blue-lt"><?= htmlspecialchars($data['mission']->mission_code ?? '-') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Partenaire</label>
                            <div class="fw-bold"><?= htmlspecialchars($data['mission']->partner_name) ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Période</label>
                            <div>Du <?= date('d/m/Y', strtotime($data['mission']->date_start)) ?> au <?= date('d/m/Y', strtotime($data['mission']->date_end)) ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Durée & Intensité</label>
                            <div class="fw-bold"><?= $data['mission']->duration_days ?> jours (<?= $data['mission']->hours_per_day ?>h/jour)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Moyen de déplacement</label>
                            <div class="fw-bold"><?= htmlspecialchars($data['mission']->means_of_transport ?: '-') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Revenu estimé</label>
                            <div class="badge bg-green-lt"><?= number_format($data['mission']->estimated_revenue, 2) ?> USD</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Statut</label>
                            <span class="badge bg-blue-lt"><?= htmlspecialchars($data['mission']->status) ?></span>
                        </div>
                        <?php if($data['mission']->description): ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Description</label>
                            <div><?= nl2br(htmlspecialchars($data['mission']->description)) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Structure du Budget de la Mission (Tableau à 2 niveaux) -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Structure du Budget de la Mission</h3>
                        <div class="card-actions">
                            <button class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modal-import-template">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><polyline points="7 9 12 4 17 9" /><line x1="12" y1="4" x2="12" y2="16" /></svg>
                                Importer Modèle
                            </button>
                            <button class="btn btn-outline-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modal-save-template">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><circle cx="12" cy="14" r="2" /><polyline points="14 4 14 8 8 8 8 4" /></svg>
                                Enregistrer comme modèle
                            </button>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-budget-main">
                                Nouvelle Ligne Principale
                            </button>
                        </div>
                    </div>
                    <div id="budget-table-container">
                        <?php require APPROOT . '/Views/supervisor/_budget_table.php'; ?>
                    </div>
                </div>

                <!-- Toast for AJAX feedback -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="budget-toast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body" id="budget-toast-body"></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Équipe -->
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Équipe de mission</h3></div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-sm">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Rôle dans la mission</th>
                                    <th>Taux horaire</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['team'] as $member): ?>
                                <tr>
                                    <td><?= htmlspecialchars($member->prenom . ' ' . $member->nom) ?></td>
                                    <td><?= htmlspecialchars($member->role_in_mission) ?></td>
                                    <td><?= number_format($member->hourly_rate, 2) ?> $/h</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ordres de Mission -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Ordres de Mission</h3>
                        <div class="card-actions">
                            <a href="<?= URLROOT ?>/supervisor/createMissionOrder/<?= $data['mission']->id ?>" class="btn btn-primary btn-sm">
                                Nouvel Ordre
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-sm">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Type</th>
                                    <th>Bénéficiaire</th>
                                    <th>Période</th>
                                    <th>Statut</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['missionOrders'] as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order->order_number) ?></td>
                                    <td><span class="badge bg-<?= $order->type == 'collectif' ? 'purple' : 'azure' ?>-lt"><?= ucfirst($order->type) ?></span></td>
                                    <td><?= $order->employee_id ? htmlspecialchars($order->prenom . ' ' . $order->nom) : 'Collectif' ?></td>
                                    <td class="small">
                                        <?= date('d/m/Y', strtotime($order->departure_date)) ?> - <?= date('d/m/Y', strtotime($order->return_date)) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status_class = [
                                                'Brouillon' => 'bg-secondary',
                                                'En attente' => 'bg-warning',
                                                'Validé' => 'bg-success',
                                                'Rejeté' => 'bg-danger'
                                            ][$order->status] ?? 'bg-info';
                                        ?>
                                        <span class="badge <?= $status_class ?> text-white"><?= $order->status ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if($_SESSION['user_role'] === 'manager' && $order->status === 'En attente'): ?>
                                            <a href="<?= URLROOT ?>/supervisor/validateMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-success">Valider</a>
                                            <a href="<?= URLROOT ?>/supervisor/rejectMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-danger">Rejeter</a>
                                        <?php endif; ?>
                                        <?php if($order->status === 'Validé'): ?>
                                            <a href="<?= URLROOT ?>/supervisor/downloadMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-outline-primary">Télécharger (DOCX)</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($data['missionOrders'])): ?>
                                <tr><td colspan="6" class="text-center text-muted">Aucun ordre de mission.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dépenses -->
                <div class="card mb-3">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                            <li class="nav-item"><a href="#tab-mission-pending" class="nav-link active" data-bs-toggle="tab">À valider</a></li>
                            <li class="nav-item"><a href="#tab-mission-validated" class="nav-link" data-bs-toggle="tab">Validées</a></li>
                            <li class="nav-item"><a href="#tab-mission-rejected" class="nav-link" data-bs-toggle="tab">Rejetées</a></li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <?php 
                        $mission_tabs = [
                            'pending' => ['id' => 'tab-mission-pending', 'active' => true],
                            'validated' => ['id' => 'tab-mission-validated', 'active' => false],
                            'rejected' => ['id' => 'tab-mission-rejected', 'active' => false]
                        ];
                        foreach($mission_tabs as $status_key => $tab):
                        ?>
                        <div class="tab-pane <?= $tab['active'] ? 'active show' : '' ?>" id="<?= $tab['id'] ?>">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Employé</th>
                                            <th>Catégorie</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total_tab = 0; foreach($data['expenses_by_status'][$status_key] as $expense): $total_tab += $expense->amount; ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($expense->expense_date)) ?></td>
                                            <td><?= htmlspecialchars($expense->prenom . ' ' . $expense->nom) ?></td>
                                            <td><?= htmlspecialchars($expense->budget_detail_label ?? ($expense->budget_item_label ?? $expense->category)) ?></td>
                                            <td><?= number_format($expense->amount, 2) ?> <?= $expense->currency ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if(empty($data['expenses_by_status'][$status_key])): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Aucune dépense.</td></tr>
                                        <?php else: ?>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="3" class="text-end">Total (USD)</td>
                                            <td><?= number_format($total_tab, 2) ?> USD</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>

<!-- Modal Budget Item (Add/Manual) -->
<div class="modal modal-blur fade" id="modal-budget-item" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/addMissionBudgetItem/<?= $data['mission']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Rubrique</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Charge associée</label>
                        <select name="charge_id" class="form-select" required>
                            <option value="">-- Sélectionner une charge --</option>
                            <?php foreach($data['missionCharges'] as $charge): ?>
                                <option value="<?= $charge->id ?>"><?= htmlspecialchars($charge->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unité</label>
                                <input type="text" name="unit" class="form-control" placeholder="ex: Jour" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Equivalent</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="unit_amount" class="form-control" value="0.00" required>
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ligne budgétaire (Détail)</label>
                        <select name="budget_line" class="form-select" required>
                            <option value="">-- Sélectionner une ligne de détail --</option>
                            <?php foreach($data['budgetMainLines'] as $main): ?>
                                <optgroup label="<?= htmlspecialchars($main->code . ' - ' . $main->label) ?>">
                                    <?php foreach($main->details as $det): ?>
                                        <option value="<?= htmlspecialchars($det->code . ' ' . $det->label) ?>"><?= htmlspecialchars($det->code . ' ' . $det->label) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Rubric -->
<div class="modal modal-blur fade" id="modal-edit-rubric" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/editMissionBudgetItem/<?= $data['mission']->id ?>" method="POST">
                <input type="hidden" name="id" id="edit-rubric-id">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la Rubrique</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Charge associée</label>
                        <select name="charge_id" id="edit-rubric-charge" class="form-select" required>
                            <?php foreach($data['missionCharges'] as $charge): ?>
                                <option value="<?= $charge->id ?>"><?= htmlspecialchars($charge->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unité</label>
                                <input type="text" name="unit" id="edit-rubric-unit" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Equivalent</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="unit_amount" id="edit-rubric-amount" class="form-control" required>
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ligne budgétaire (Détail)</label>
                        <select name="budget_line" id="edit-rubric-line" class="form-select" required>
                            <option value="">-- Sélectionner une ligne de détail --</option>
                            <?php foreach($data['budgetMainLines'] as $main): ?>
                                <optgroup label="<?= htmlspecialchars($main->code . ' - ' . $main->label) ?>">
                                    <?php foreach($main->details as $det): ?>
                                        <option value="<?= htmlspecialchars($det->code . ' ' . $det->label) ?>"><?= htmlspecialchars($det->code . ' ' . $det->label) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Import Charges -->
<div class="modal modal-blur fade" id="modal-import-charges" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/importMissionCharges/<?= $data['mission']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Importer des charges de mission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Sélectionnez les charges à importer :</p>
                    <div class="divide-y">
                        <?php foreach($data['missionCharges'] as $charge): ?>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="charge_ids[]" value="<?= $charge->id ?>">
                            <span class="form-check-label"><?= htmlspecialchars($charge->name) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer la sélection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Main Line -->
<div class="modal modal-blur fade" id="modal-edit-budget-main" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/editBudgetMainLine/<?= $data['mission']->id ?>" method="POST" id="form-edit-budget-main">
                <input type="hidden" name="id" id="edit-main-id">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la Ligne Principale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" id="edit-main-code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="label" id="edit-main-label" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Detail Line -->
<div class="modal modal-blur fade" id="modal-edit-budget-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/editBudgetDetailLine/<?= $data['mission']->id ?>" method="POST" id="form-edit-budget-detail">
                <input type="hidden" name="id" id="edit-detail-id">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la Ligne de Détail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" id="edit-detail-code" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Libellé</label>
                                <input type="text" name="label" id="edit-detail-label" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unité Budgétaire</label>
                                <div class="input-group">
                                    <select name="unit" id="edit-detail-unit" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach($data['budgetaryUnits'] as $u): ?>
                                            <option value="<?= htmlspecialchars($u->name) ?>"><?= htmlspecialchars($u->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="new_unit" id="edit-detail-new-unit" class="form-control" placeholder="Ou créer..." style="display:none;">
                                    <button type="button" class="btn btn-icon" onclick="toggleNewUnit('edit-detail')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" step="0.01" name="quantity" id="edit-detail-qty" class="form-control" required oninput="calculateTotal('edit-detail')">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">P.U.</label>
                                <input type="number" step="0.01" name="unit_price" id="edit-detail-price" class="form-control" required oninput="calculateTotal('edit-detail')">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Montant Total Estimé (Calculé)</label>
                        <div class="input-group input-group-flat">
                            <input type="text" id="edit-detail-total-display" class="form-control fw-bold text-end" readonly>
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Budget Main Line -->
<div class="modal modal-blur fade" id="modal-budget-main" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/addBudgetMainLine/<?= $data['mission']->id ?>" method="POST" id="form-budget-main">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Ligne Principale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="ex: 1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="label" class="form-control" placeholder="ex: FRAIS DE PERSONNEL" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Budget Detail Line -->
<div class="modal modal-blur fade" id="modal-budget-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/addBudgetDetailLine/<?= $data['mission']->id ?>" method="POST" id="form-budget-detail">
                <input type="hidden" name="main_line_id" id="detail-main-id">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Détail pour <span id="detail-main-code-span" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" placeholder="ex: 1101" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Libellé</label>
                                <input type="text" name="label" class="form-control" placeholder="ex: Honoraires Auditeurs" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unité Budgétaire</label>
                                <div class="input-group">
                                    <select name="unit" id="add-detail-unit" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach($data['budgetaryUnits'] as $u): ?>
                                            <option value="<?= htmlspecialchars($u->name) ?>"><?= htmlspecialchars($u->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="new_unit" id="add-detail-new-unit" class="form-control" placeholder="Ou créer..." style="display:none;">
                                    <button type="button" class="btn btn-icon" onclick="toggleNewUnit('add-detail')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" step="0.01" name="quantity" id="add-detail-qty" class="form-control" value="1.00" required oninput="calculateTotal('add-detail')">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">P.U.</label>
                                <input type="number" step="0.01" name="unit_price" id="add-detail-price" class="form-control" value="0.00" required oninput="calculateTotal('add-detail')">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Montant Total Estimé (Calculé)</label>
                        <div class="input-group input-group-flat">
                            <input type="text" id="add-detail-total-display" class="form-control fw-bold text-end" value="0.00" readonly>
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Save as Template -->
<div class="modal modal-blur fade" id="modal-save-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/saveBudgetAsTemplate/<?= $data['mission']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Enregistrer comme modèle de budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du modèle</label>
                        <input type="text" name="template_name" class="form-control" placeholder="ex: Budget Standard Audit" required>
                    </div>
                    <p class="text-muted small">Ce modèle pourra être réutilisé pour d'autres missions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Template -->
<div class="modal modal-blur fade" id="modal-import-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/supervisor/importBudgetTemplate/<?= $data['mission']->id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Importer un modèle de budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($data['budgetTemplates'])): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Aucun modèle disponible.</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Choisir un modèle</label>
                            <div class="list-group list-group-flush border rounded">
                                <?php foreach ($data['budgetTemplates'] as $tpl): ?>
                                    <label class="list-group-item list-group-item-action d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="template_id" value="<?= $tpl->id ?>" required>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium"><?= htmlspecialchars($tpl->name) ?></div>
                                            <div class="text-muted small">Créé le <?= date('d/m/Y', strtotime($tpl->created_at)) ?></div>
                                        </div>
                                        <button type="button" class="btn btn-icon btn-sm btn-ghost-danger ms-2" title="Supprimer le modèle" onclick="event.preventDefault(); event.stopPropagation(); confirmDeleteTemplate('<?= URLROOT ?>/supervisor/deleteBudgetTemplate/<?= $tpl->id ?>/<?= $data['mission']->id ?>');">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        </button>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="alert alert-info py-2 small">
                            L'importation ajoutera les lignes du modèle à votre budget actuel.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <?php if (!empty($data['budgetTemplates'])): ?>
                        <button type="submit" class="btn btn-primary">Importer</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// --- AJAX Helpers ---
async function submitBudgetForm(formId, modalId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const html = await response.text();
            document.getElementById('budget-table-container').innerHTML = html;
            if (modal) modal.hide();
            form.reset();
            showToast('Opération réussie !', 'success');
        } else {
            showToast('Erreur lors de l\'enregistrement.', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Erreur réseau.', 'danger');
    }
}

async function deleteBudgetAction(url) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) return;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const html = await response.text();
            document.getElementById('budget-table-container').innerHTML = html;
            showToast('Suppression réussie !', 'success');
        } else {
            showToast('Erreur lors de la suppression.', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Erreur réseau.', 'danger');
    }
}

// Suppression d'un modèle de budget (POST + token CSRF auto-injecté), puis rechargement.
async function confirmDeleteTemplate(url) {
    if (!confirm('Supprimer ce modèle ?')) return;
    try {
        await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        window.location.reload();
    } catch (e) {
        showToast('Erreur réseau.', 'danger');
    }
}

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('budget-toast');
    const bodyEl = document.getElementById('budget-toast-body');
    toastEl.classList.remove('bg-success', 'bg-danger');
    toastEl.classList.add('bg-' + type);
    bodyEl.innerText = message;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Intercept form submissions
document.addEventListener('DOMContentLoaded', function() {
    const budgetForms = [
        { id: 'form-budget-main', modal: 'modal-budget-main' },
        { id: 'form-edit-budget-main', modal: 'modal-edit-budget-main' },
        { id: 'form-budget-detail', modal: 'modal-budget-detail' },
        { id: 'form-edit-budget-detail', modal: 'modal-edit-budget-detail' }
    ];

    budgetForms.forEach(f => {
        const form = document.getElementById(f.id);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBudgetForm(f.id, f.modal);
            });
        }
    });
});

// Wrappers for deletions (called from _budget_table.php)
function deleteBudgetMainLine(id) {
    deleteBudgetAction('<?= URLROOT ?>/supervisor/deleteBudgetMainLine/' + id + '/<?= $data['mission']->id ?>');
}

function deleteBudgetDetailLine(id) {
    deleteBudgetAction('<?= URLROOT ?>/supervisor/deleteBudgetDetailLine/' + id + '/<?= $data['mission']->id ?>');
}

function toggleNewUnit(prefix) {
    const select = document.getElementById(prefix + '-unit');
    const input = document.getElementById(prefix + '-new-unit');
    
    if (input.style.display === 'none') {
        input.style.display = 'block';
        select.style.display = 'none';
        select.value = '';
    } else {
        input.style.display = 'none';
        select.style.display = 'block';
        input.value = '';
    }
}

function calculateTotal(prefix) {
    const qty = parseFloat(document.getElementById(prefix + '-qty').value) || 0;
    const price = parseFloat(document.getElementById(prefix + '-price').value) || 0;
    const total = qty * price;
    document.getElementById(prefix + '-total-display').value = total.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' USD';
}

function openAddDetailModal(mainId, mainCode) {
    document.getElementById('detail-main-id').value = mainId;
    document.getElementById('detail-main-code-span').innerText = mainCode;
    
    // Reset fields
    document.getElementById('add-detail-qty').value = "1.00";
    document.getElementById('add-detail-price').value = "0.00";
    
    // Reset unit inputs
    document.getElementById('add-detail-new-unit').style.display = 'none';
    document.getElementById('add-detail-unit').style.display = 'block';
    
    calculateTotal('add-detail');
    
    var myModal = new bootstrap.Modal(document.getElementById('modal-budget-detail'));
    myModal.show();
}

function openEditMainLineModal(main) {
    document.getElementById('edit-main-id').value = main.id;
    document.getElementById('edit-main-code').value = main.code;
    document.getElementById('edit-main-label').value = main.label;
    var myModal = new bootstrap.Modal(document.getElementById('modal-edit-budget-main'));
    myModal.show();
}

function openEditDetailLineModal(detail) {
    document.getElementById('edit-detail-id').value = detail.id;
    document.getElementById('edit-detail-code').value = detail.code;
    document.getElementById('edit-detail-label').value = detail.label;
    document.getElementById('edit-detail-unit').value = detail.unit || '';
    document.getElementById('edit-detail-qty').value = detail.quantity;
    document.getElementById('edit-detail-price').value = detail.unit_price;
    
    // Ensure display correct input
    document.getElementById('edit-detail-new-unit').style.display = 'none';
    document.getElementById('edit-detail-unit').style.display = 'block';
    
    calculateTotal('edit-detail');
    
    var myModal = new bootstrap.Modal(document.getElementById('modal-edit-budget-detail'));
    myModal.show();
}

function openEditRubricModal(rubric) {
    document.getElementById('edit-rubric-id').value = rubric.id;
    document.getElementById('edit-rubric-unit').value = rubric.unit;
    document.getElementById('edit-rubric-amount').value = rubric.unit_amount;
    document.getElementById('edit-rubric-line').value = rubric.budget_line;
    
    if (rubric.charge_id) {
        document.getElementById('edit-rubric-charge').value = rubric.charge_id;
    }

    var myModal = new bootstrap.Modal(document.getElementById('modal-edit-rubric'));
    myModal.show();
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
