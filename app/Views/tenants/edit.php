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
                <form action="<?= URLROOT ?>/tenants/edit/<?= $data['tenant']->id ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom de l'entreprise</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['tenant']->name) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Acronyme</label>
                            <input type="text" name="acronym" class="form-control" value="<?= htmlspecialchars($data['tenant']->acronym) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($data['tenant']->address) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['tenant']->phone) ?>" required>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="<?= URLROOT ?>/tenants" class="btn btn-link">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
