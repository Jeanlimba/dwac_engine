<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <?= isset($data['mission']) ? 'Mission : ' . htmlspecialchars($data['mission']->title) : 'Ordre de Mission Instantané' ?>
                </div>
                <h2 class="page-title">Créer un Ordre de Mission</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= isset($data['mission']) ? URLROOT . '/supervisor/missionDetails/' . $data['mission']->id : URLROOT . '/supervisor/missionOrders' ?>" class="btn btn-white">Annuler</a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form action="<?= URLROOT ?>/supervisor/createMissionOrder<?= isset($data['mission']) ? '/' . $data['mission']->id : '' ?>" method="POST">
                    <input type="hidden" name="mission_id" value="<?= $data['mission']->id ?? '' ?>">
                    <div class="row row-cards">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Numéro d'ordre</label>
                                <input type="text" name="order_number" class="form-control form-control-sm" placeholder="ex: OM/2024/001" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Type d'ordre</label>
                                <select name="type" id="order_type" class="form-select form-select-sm" required>
                                    <option value="personnel">Personnel</option>
                                    <option value="collectif">Collectif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2" id="employee_selection">
                        <label class="form-label">Bénéficiaire (si personnel)</label>
                        <select name="employee_id" class="form-select form-select-sm">
                            <option value="">-- Sélectionner un employé --</option>
                            <?php foreach($data['team'] as $member): ?>
                                <option value="<?= $member->employee_id ?? $member->id ?>"><?= htmlspecialchars($member->prenom . ' ' . $member->nom) ?> <?= isset($member->role_in_mission) ? '(' . htmlspecialchars($member->role_in_mission) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Objet de la mission</label>
                        <input type="text" name="object" class="form-control form-control-sm" placeholder="ex: Audit financier..." required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Itinéraire</label>
                        <input type="text" name="itinerary" class="form-control form-control-sm" placeholder="ex: Kinshasa - Lubumbashi - Kinshasa">
                    </div>

                    <div class="row row-cards">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Transport</label>
                                <input type="text" name="means_of_transport" class="form-control form-control-sm" value="<?= htmlspecialchars($data['mission']->means_of_transport ?? '') ?>" placeholder="ex: Avion, Véhicule...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Date Départ</label>
                                <input type="date" name="departure_date" class="form-control form-control-sm" value="<?= $data['mission']->date_start ?? date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Date Retour</label>
                                <input type="date" name="return_date" class="form-control form-control-sm" value="<?= $data['mission']->date_end ?? date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Enregistrer et Soumettre pour Validation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('order_type').addEventListener('change', function() {
    const empSelection = document.getElementById('employee_selection');
    if (this.value === 'collectif') {
        empSelection.style.display = 'none';
        empSelection.querySelector('select').value = '';
    } else {
        empSelection.style.display = 'block';
    }
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
