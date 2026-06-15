<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Choisir une Mission</h2>
                <div class="text-muted mt-1">Un ordre de mission doit être rattaché à une mission existante.</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Rattacher à une mission</h3></div>
                    <div class="card-body">
                        <form action="<?= URLROOT ?>/supervisor/createMissionOrder" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Sélectionner la mission</label>
                                <select name="mission_id" class="form-select" required>
                                    <option value="">-- Choisir une mission --</option>
                                    <?php foreach($data['missions'] as $mission): ?>
                                        <option value="<?= $mission->id ?>"><?= htmlspecialchars($mission->title) ?> (<?= htmlspecialchars($mission->partner_name) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">Continuer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-header bg-primary-lt"><h3 class="card-title">Ordre Instantané</h3></div>
                    <div class="card-body">
                        <p>Créez un ordre de mission qui n'est pas lié à une mission déjà enregistrée dans le système.</p>
                        <form action="<?= URLROOT ?>/supervisor/createMissionOrder" method="POST">
                            <input type="hidden" name="action" value="create_instant">
                            <input type="hidden" name="mission_id" value="">
                            <div class="form-footer">
                                <button type="submit" class="btn btn-outline-primary w-100">Créer un ordre instantané</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
