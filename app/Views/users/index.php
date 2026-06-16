<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title"><?= $data['title'] ?></h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?= URLROOT ?>/users/create" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Nouvel Utilisateur
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
                            <th>Username</th>
                            <th>Nom & Prénom</th>
                            <?php if ($_SESSION['is_super_admin']): ?>
                                <th>Entreprise</th>
                            <?php endif; ?>
                            <th>Statut</th>
                            <th>Date Création</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['users'] as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->username) ?></td>
                            <td class="text-muted">
                                <?= $user->nom ? htmlspecialchars($user->nom . ' ' . $user->prenom) : '<span class="badge bg-blue-lt">Admin</span>' ?>
                            </td>
                            <?php if ($_SESSION['is_super_admin']): ?>
                                <td class="text-muted"><?= $user->tenant_name ?? 'Système' ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($user->status === 'active'): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bloqué</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($user->created_at)) ?></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="<?= URLROOT ?>/users/edit/<?= $user->id ?>" class="btn btn-white btn-sm">Modifier</a>
                                    <?php if (!$user->is_super_admin): ?>
                                        <form method="POST" action="<?= URLROOT ?>/users/toggle/<?= $user->id ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn <?= $user->status === 'active' ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                                <?= $user->status === 'active' ? 'Bloquer' : 'Débloquer' ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= URLROOT ?>/users/delete/<?= $user->id ?>" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>
                                    <?php endif; ?>
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
