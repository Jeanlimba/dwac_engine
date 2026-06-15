<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title"><?= $data['title'] ?></h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?= URLROOT ?>/tenants/create" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Nouvelle Entreprise
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Acronyme</th>
                            <th>Adresse</th>
                            <th>Contact</th>
                            <th>Date d'inscription</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['tenants'] as $tenant): ?>
                        <tr>
                            <td><?= htmlspecialchars($tenant->name) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($tenant->acronym) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($tenant->address) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($tenant->phone) ?></td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($tenant->created_at)) ?></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="<?= URLROOT ?>/tenants/edit/<?= $tenant->id ?>" class="btn btn-white btn-sm">Modifier</a>
                                    <a href="<?= URLROOT ?>/tenants/delete/<?= $tenant->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ? Tous les utilisateurs et données associés seront supprimés.')">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
