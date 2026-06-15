<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Validations en attente</h2>
                <div class="text-muted mt-1">
                    <?= count($data['pending']) ?> déclaration(s) à valider
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <?php if (empty($data['pending'])): ?>
            <div class="card-body text-center py-5">
                <div class="text-muted">Aucune déclaration en attente de validation.</div>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Heures</th>
                            <th>Activité</th>
                            <th>Description</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['pending'] as $entry): ?>
                        <tr>
                            <td>
                                <div class="font-weight-medium"><?= htmlspecialchars($entry->prenom . ' ' . $entry->nom) ?></div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($entry->date)) ?></td>
                            <td>
                                <span class="badge bg-blue-lt">
                                    <?= substr($entry->start_time, 0, 5) ?> - <?= substr($entry->end_time, 0, 5) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($entry->category) ?>
                                <?php if ($entry->category == 'Mission'): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($entry->mission_title ?? $entry->custom_mission_name) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($entry->task_description) ?></small>
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <button onclick="openValidateModal(<?= $entry->id ?>, '<?= htmlspecialchars($entry->prenom . ' ' . $entry->nom) ?>')" class="btn btn-sm btn-success">
                                        Valider
                                    </button>
                                    <button onclick="openRejectModal(<?= $entry->id ?>)" class="btn btn-sm btn-outline-danger">
                                        Rejeter
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Validation -->
<div class="modal modal-blur fade" id="modal-validate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Valider l'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="validate-form">
                <input type="hidden" name="id" id="validate-id">
                <div class="modal-body">
                    <p>Attribuer une note de performance pour le travail de <strong id="validate-employee-name"></strong> :</p>
                    <div class="mb-3">
                        <label class="form-label">Note (1 à 5)</label>
                        <div class="d-flex gap-3 justify-content-center py-3">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rating" value="<?= $i ?>" <?= $i == 4 ? 'checked' : '' ?>>
                                <span class="form-check-label"><?= $i ?></span>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <div class="text-center text-muted small">
                            1: Très médiocre | 3: Satisfaisant | 5: Excellent
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Valider & Noter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal modal-blur fade" id="modal-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter l'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reject-form">
                <input type="hidden" name="id" id="reject-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Motif du rejet</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Expliquez pourquoi cette déclaration est rejetée..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Rejeter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const validateModal = new bootstrap.Modal(document.getElementById('modal-validate'));
    const rejectModal = new bootstrap.Modal(document.getElementById('modal-reject'));

    window.openValidateModal = function(id, name) {
        document.getElementById('validate-id').value = id;
        document.getElementById('validate-employee-name').innerText = name;
        validateModal.show();
    };

    window.openRejectModal = function(id) {
        document.getElementById('reject-id').value = id;
        rejectModal.show();
    };

    document.getElementById('validate-form').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('<?= URLROOT ?>/timesheets/validate', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) window.location.reload();
            else alert('Erreur lors de la validation');
        });
    };

    document.getElementById('reject-form').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('<?= URLROOT ?>/timesheets/reject', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) window.location.reload();
            else alert('Erreur lors du rejet');
        });
    };
});
</script>
