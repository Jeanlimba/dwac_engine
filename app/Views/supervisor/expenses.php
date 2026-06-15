<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Validation des Dépenses</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php flash('supervisor_message'); ?>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#tab-pending" class="nav-link active" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
                            À valider
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
                    'pending' => 'Aucune dépense en attente de validation.',
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
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Employé</th>
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
                                                    <td><?= date('d/m/Y', strtotime($expense->expense_date)) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="avatar avatar-xs rounded-circle me-2"><?= substr($expense->prenom, 0, 1) . substr($expense->nom, 0, 1) ?></span>
                                                            <?= htmlspecialchars($expense->prenom . ' ' . $expense->nom) ?>
                                                        </div>
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
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-white btn-sm" onclick="openDetailModal(<?= $expense->id ?>)">
                                                            Détails & Validation
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <td colspan="3" class="text-end fw-bold">Total (USD):</td>
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

<!-- Modal Détails & Validation (Unchanged) -->
<div class="modal modal-blur fade" id="modal-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-process" method="POST">
                <div class="modal-header">
...
            new bootstrap.Modal(document.getElementById('modal-detail')).show();
        });
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
