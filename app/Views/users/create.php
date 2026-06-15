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
                <form action="<?= URLROOT ?>/users/create" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($_SESSION['is_super_admin']): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Entreprise (Tenant)</label>
                            <select name="tenant_id" class="form-select">
                                <option value="">Système (Super Admin)</option>
                                <?php foreach ($data['tenants'] as $tenant): ?>
                                    <option value="<?= $tenant->id ?>"><?= htmlspecialchars($tenant->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-check mt-4">
                                <input type="checkbox" name="is_super_admin" class="form-check-input">
                                <span class="form-check-label">Super Administrateur</span>
                            </label>
                        </div>
                        <?php else: ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lier à un employé (Optionnel)</label>
                            <select name="employee_id" class="form-select">
                                <option value="">Compte Admin (Non-employé)</option>
                                <?php foreach ($data['employees'] as $employee): ?>
                                    <option value="<?= $employee->id ?>"><?= htmlspecialchars($employee->nom . ' ' . $employee->prenom) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
                        <a href="<?= URLROOT ?>/users" class="btn btn-link">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
