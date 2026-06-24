<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Présence — Tableau de bord</h2>
                <div class="text-muted mt-1"><?= date('d/m/Y') ?></div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= URLROOT ?>/attendance/report" class="btn btn-outline-primary">
                    Voir les rapports
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Derniers pointages du jour</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>Heure</th>
                                    <th>Matricule</th>
                                    <th>Employé</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['logs'] as $log): ?>
                                <tr>
                                    <td><?= date('H:i:s', strtotime($log->date_heure)) ?></td>
                                    <td><span class="text-muted"><?= htmlspecialchars($log->matricule ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($log->prenom . ' ' . $log->nom) ?></td>
                                    <td>
                                        <?php if ((int) $log->type_pointage === 0): ?>
                                            <span class="badge bg-success me-1"></span> Entrée
                                        <?php else: ?>
                                            <span class="badge bg-danger me-1"></span> Sortie
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data['logs'])): ?>
                                <tr><td colspan="4" class="text-center text-muted">Aucun pointage aujourd'hui.</td></tr>
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
