<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-hierarchy-2 me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 3h4v4h-4z" /><path d="M3 17h4v4h-4z" /><path d="M17 17h4v4h-4z" /><path d="M7 17l5 -4l5 4" /><line x1="12" y1="7" x2="12" y2="13" /></svg>
                    Structure des Affectations
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-department">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Nouvelle Entité
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <?= e($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?= e($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nom de l'Entité</th>
                            <th>Dépend de (Parent)</th>
                            <th>Date de création</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['departments'] as $department): ?>
                            <tr>
                                <td class="font-weight-medium"><?= htmlspecialchars($department->name ?? '') ?></td>
                                <td>
                                    <?php if ($department->parent_name): ?>
                                        <span class="badge bg-blue-lt"><?= htmlspecialchars($department->parent_name) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted text-italic">Aucune (Racine)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('d/m/Y', strtotime($department->created_at)) ?></td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <button type="button" class="btn btn-white btn-icon" data-bs-toggle="modal" data-bs-target="#modal-edit-department-<?= $department->id ?>" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15l3 0l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                        </button>
                                        <button type="button" class="btn btn-white btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-department-<?= $department->id ?>" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['departments'])): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Aucune entité enregistrée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création Département -->
<div class="modal modal-blur fade" id="modal-create-department" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Entité d'Affectation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form action="<?= URLROOT ?>/departments/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom de l'Entité</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entité Parente</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Aucune (Racine)</option>
                            <?php foreach ($data['departments'] as $dept): ?>
                                <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->name ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modaux Édition Département -->
<?php foreach ($data['departments'] as $department): ?>
<div class="modal modal-blur fade" id="modal-edit-department-<?= $department->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'Entité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form action="<?= URLROOT ?>/departments/edit/<?= $department->id ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom de l'Entité</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($department->name ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entité Parente</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Aucune (Racine)</option>
                            <?php foreach ($data['departments'] as $dept): ?>
                                <option value="<?= $dept->id ?>" <?= ($department->parent_id == $dept->id) ? 'selected' : '' ?> <?= ($department->id == $dept->id) ? 'disabled' : '' ?>><?= htmlspecialchars($dept->name ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modaux Suppression Département -->
<?php foreach ($data['departments'] as $department): ?>
<div class="modal modal-blur fade" id="modal-delete-department-<?= $department->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <h3>Êtes-vous sûr ?</h3>
                <div class="text-muted">Voulez-vous vraiment supprimer l'entité '<?= htmlspecialchars($department->name ?? '') ?>' ? Cette action ne peut pas être annulée.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                Annuler
                            </a>
                        </div>
                        <div class="col">
                            <form action="<?= URLROOT ?>/departments/delete/<?= $department->id ?>" method="POST">
                                <button type="submit" class="btn btn-danger w-100">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require APPROOT . '/Views/inc/footer.php'; ?>