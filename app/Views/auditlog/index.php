<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Journal d'audit</h2>
                <div class="text-muted mt-1"><?= (int) $data['total'] ?> entrée(s)</div>
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
                            <th>Date / heure</th>
                            <th>Utilisateur</th>
                            <?php if ($data['is_super']): ?><th>Tenant</th><?php endif; ?>
                            <th>Action</th>
                            <th>Détails</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['logs'] as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?= htmlspecialchars($log->created_at) ?></td>
                            <td><?= htmlspecialchars($log->username ?? '— (système / supprimé)') ?></td>
                            <?php if ($data['is_super']): ?><td class="text-muted"><?= htmlspecialchars((string) ($log->tenant_id ?? '—')) ?></td><?php endif; ?>
                            <td><span class="badge bg-blue-lt"><?= htmlspecialchars($log->action) ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($log->details ?? '') ?></td>
                            <td class="text-muted"><small><?= htmlspecialchars($log->ip_address ?? '') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['logs'])): ?>
                        <tr><td colspan="<?= $data['is_super'] ? 6 : 5 ?>" class="text-center text-muted">Aucune entrée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
                $page = (int) $data['page'];
                $totalPages = (int) ceil($data['total'] / $data['perPage']);
            ?>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-muted">Page <?= $page ?> / <?= $totalPages ?></p>
                <ul class="pagination m-0 ms-auto">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= URLROOT ?>/auditlog?page=<?= max(1, $page - 1) ?>">Précédent</a>
                    </li>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= URLROOT ?>/auditlog?page=<?= min($totalPages, $page + 1) ?>">Suivant</a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
