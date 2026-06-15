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
                <form action="<?= URLROOT ?>/users/edit/<?= $data['user']->id ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['user']->username) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($_SESSION['is_super_admin']): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Entreprise (Tenant)</label>
                            <select name="tenant_id" class="form-select">
                                <option value="">Système (Super Admin)</option>
                                <?php foreach ($data['tenants'] as $tenant): ?>
                                    <option value="<?= $tenant->id ?>" <?= $data['user']->tenant_id == $tenant->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tenant->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $data['user']->status === 'active' ? 'selected' : '' ?>>Actif</option>
                                <option value="blocked" <?= $data['user']->status === 'blocked' ? 'selected' : '' ?>>Bloqué</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="<?= URLROOT ?>/users" class="btn btn-link">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
