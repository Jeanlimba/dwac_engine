<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Bonjour, <?= $_SESSION['user_firstname'] ?></h2>
                <div class="text-muted mt-1">Voici un résumé de votre activité</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <!-- Stats Personnelles -->
            <div class="col-md-6 col-xl-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-yellow text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= $data['stats']['pending_expenses'] ?> Notes de frais</div>
                                <div class="text-muted">En attente de validation</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 0 0 0 4h2a2 2 0 0 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 6v2m0 8v2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= number_format($data['stats']['approved_expenses_amount'], 2) ?> USD</div>
                                <div class="text-muted">Total remboursé / validé</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raccourcis -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions rapides</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/expenses" class="btn btn-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                    Nouvelle Note de Frais
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/ged" class="btn btn-outline-secondary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                                    Mes Documents
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/employees/details/<?= $_SESSION['employee_id'] ?>" class="btn btn-outline-info w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    Consulter mon Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières dépenses -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mes derniers rapports de frais</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data['stats']['recent_expenses'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Aucune dépense enregistrée.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($data['stats']['recent_expenses'], 0, 5) as $expense): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($expense->expense_date)) ?></td>
                                        <td><?= htmlspecialchars($expense->category) ?></td>
                                        <td><?= number_format($expense->amount, 2) ?> <?= $expense->currency ?></td>
                                        <td>
                                            <?php 
                                                $badge = 'bg-secondary';
                                                if($expense->status === 'Validé' || $expense->status === 'Validé Manager') $badge = 'bg-success';
                                                if($expense->status === 'Rejeté') $badge = 'bg-danger';
                                                if($expense->status === 'En attente') $badge = 'bg-warning';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $expense->status ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
