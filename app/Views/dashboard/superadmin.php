<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Administration Système</h2>
                <div class="text-muted mt-1">Vue d'ensemble de la plateforme</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-6 col-xl-6">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= $data['stats']['tenants_count'] ?> Entreprises</div>
                                <div class="text-muted">Total des tenants enregistrés</div>
                                <div class="mt-2">
                                    <a href="<?= URLROOT ?>/tenants" class="btn btn-sm btn-outline-primary">Gérer les entreprises</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-6">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= $data['stats']['users_count'] ?> Utilisateurs</div>
                                <div class="text-muted">Comptes d'accès au système</div>
                                <div class="mt-2">
                                    <a href="<?= URLROOT ?>/users" class="btn btn-sm btn-outline-green">Gérer les utilisateurs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dernières entreprises inscrites</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Acronyme</th>
                                    <th>Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['stats']['recent_tenants'] as $tenant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tenant->name) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($tenant->acronym) ?></td>
                                    <td class="text-muted"><?= date('d/m/Y', strtotime($tenant->created_at)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
