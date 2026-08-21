<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title"><?= $data['title'] ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form action="<?= URLROOT ?>/expenses/edit/<?= $data['expense']->id ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type / Mission</label>
                            <select name="mission_id" class="form-select" id="select-mission" onchange="loadRubrics(this.value)">
                                <option value="">Administration</option>
                                <optgroup label="Missions">
                                    <?php foreach($data['missions'] as $mission): ?>
                                        <option value="<?= $mission->id ?>" <?= $data['expense']->mission_id == $mission->id ? 'selected' : '' ?>>
                                            Mission: <?= htmlspecialchars($mission->title) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie / Rubrique</label>
                            <div id="category-wrapper" <?= $data['expense']->mission_id ? 'style="display:none;"' : '' ?>>
                                <select name="category" class="form-select" <?= $data['expense']->mission_id ? '' : 'required' ?>>
                                    <?php foreach($data['admin_charges'] as $charge): ?>
                                        <option value="<?= htmlspecialchars($charge->name) ?>" <?= $data['expense']->category == $charge->name ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($charge->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if(empty($data['admin_charges'])): ?>
                                        <option value="Transport" <?= $data['expense']->category == 'Transport' ? 'selected' : '' ?>>Transport</option>
                                        <option value="Logement" <?= $data['expense']->category == 'Logement' ? 'selected' : '' ?>>Logement</option>
                                        <option value="Restauration" <?= $data['expense']->category == 'Restauration' ? 'selected' : '' ?>>Restauration</option>
                                        <option value="Fournitures" <?= $data['expense']->category == 'Fournitures' ? 'selected' : '' ?>>Fournitures</option>
                                        <option value="Communication" <?= $data['expense']->category == 'Communication' ? 'selected' : '' ?>>Communication</option>
                                        <option value="Divers" <?= $data['expense']->category == 'Divers' ? 'selected' : '' ?>>Divers</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div id="rubric-wrapper" <?= $data['expense']->mission_id ? '' : 'style="display:none;"' ?>>
                                <select name="budget_detail_id" class="form-select" id="select-rubric" <?= $data['expense']->mission_id ? 'required' : '' ?>>
                                    <option value="">-- Rubrique --</option>
                                    <?php foreach($data['rubrics'] as $rubric): ?>
                                        <?php $label = ($rubric->main_code ? $rubric->main_code . ' ' : '') . $rubric->code . ' ' . $rubric->label; ?>
                                        <option value="<?= $rubric->id ?>" <?= $data['expense']->budget_detail_id == $rubric->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Montant</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" class="form-control" value="<?= $data['expense']->amount ?>" required>
                                <select name="currency" class="form-select" style="max-width: 100px;">
                                    <option value="USD" <?= $data['expense']->currency == 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="CDF" <?= $data['expense']->currency == 'CDF' ? 'selected' : '' ?>>CDF</option>
                                    <option value="EUR" <?= $data['expense']->currency == 'EUR' ? 'selected' : '' ?>>EUR</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dates</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" name="expense_date" class="form-control" value="<?= $data['expense']->expense_date ?>" required>
                                </div>
                                <div class="col">
                                    <input type="date" name="expense_date_end" class="form-control" value="<?= $data['expense']->expense_date_end ?>" placeholder="Date fin (optionnel)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description / Justification</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['expense']->description) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reçu (Justificatif)</label>
                        <?php if($data['expense']->receipt_path): ?>
                            <div class="mb-2">
                                <a href="<?= e(URLROOT . '/' . $data['expense']->receipt_path) ?>" target="_blank" class="btn btn-sm btn-ghost-secondary">
                                    Voir le reçu actuel
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                        <small class="text-muted">Laissez vide pour conserver le reçu actuel.</small>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="<?= URLROOT ?>/expenses" class="btn btn-link">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadRubrics(missionId) {
    const selectRubric = document.getElementById('select-rubric');
    const rubricWrapper = document.getElementById('rubric-wrapper');
    const categoryWrapper = document.getElementById('category-wrapper');
    const selectCategory = categoryWrapper.querySelector('select');
    
    if (!missionId) {
        rubricWrapper.style.display = 'none';
        categoryWrapper.style.display = 'block';
        selectCategory.setAttribute('required', 'required');
        selectRubric.removeAttribute('required');
        return;
    }

    fetch('<?= URLROOT ?>/expenses/getMissionRubrics/' + missionId)
        .then(response => response.json())
        .then(data => {
            selectRubric.innerHTML = '<option value="">-- Rubrique --</option>';
            data.forEach(rubric => {
                const label = (rubric.main_code ? rubric.main_code + ' ' : '') + rubric.code + ' ' + rubric.label;
                const option = new Option(label, rubric.id);
                selectRubric.add(option);
            });
            
            categoryWrapper.style.display = 'none';
            rubricWrapper.style.display = 'block';
            selectCategory.removeAttribute('required');
            selectRubric.setAttribute('required', 'required');
        });
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
