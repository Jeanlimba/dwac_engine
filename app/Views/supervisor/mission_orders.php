<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Tous les Ordres de Mission
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="<?= URLROOT ?>/supervisor/createMissionOrder" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Nouvel Ordre
                </a>
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
                            <th>N° Ordre</th>
                            <th>Type</th>
                            <th>Bénéficiaire</th>
                            <th>Mission</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['orders'] as $order): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($order->order_number) ?></td>
                            <td>
                                <span class="badge bg-<?= $order->type == 'collectif' ? 'purple' : 'azure' ?>-lt">
                                    <?= ucfirst($order->type) ?>
                                </span>
                            </td>
                            <td>
                                <?= $order->employee_id ? htmlspecialchars($order->prenom . ' ' . $order->nom) : '<span class="text-muted">Collectif</span>' ?>
                            </td>
                            <td>
                                <?php if($order->mission_id): ?>
                                    <a href="<?= URLROOT ?>/supervisor/missionDetails/<?= $order->mission_id ?>">
                                        <?= htmlspecialchars($order->mission_title ?? 'Mission') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted italic">Ordre Instantané</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?= date('d/m/Y', strtotime($order->departure_date)) ?> - <?= date('d/m/Y', strtotime($order->return_date)) ?>
                            </td>
                            <td>
                                <?php 
                                    $status_class = [
                                        'Brouillon' => 'bg-secondary',
                                        'En attente' => 'bg-warning',
                                        'Validé' => 'bg-success',
                                        'Rejeté' => 'bg-danger'
                                    ][$order->status] ?? 'bg-info';
                                ?>
                                <span class="badge <?= $status_class ?> text-white"><?= $order->status ?></span>
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="<?= URLROOT ?>/supervisor/editMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-white">Éditer Studio</a>
                                    <?php if($_SESSION['user_role'] === 'manager' && $order->status === 'En attente'): ?>
                                        <a href="<?= URLROOT ?>/supervisor/validateMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-success">Valider</a>
                                        <a href="<?= URLROOT ?>/supervisor/rejectMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-danger">Rejeter</a>
                                    <?php endif; ?>
                                    
                                    <?php if($order->status === 'Validé'): ?>
                                        <a href="<?= URLROOT ?>/supervisor/downloadMissionOrder/<?= $order->id ?>" class="btn btn-sm btn-outline-primary">
                                            Télécharger (DOCX)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data['orders'])): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun ordre de mission trouvé.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualisation -->
<div class="modal modal-blur fade" id="modal-preview-order" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-full-width modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévisualisation de l'Ordre de Mission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="preview-iframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewOrder(id) {
    const iframe = document.getElementById('preview-iframe');
    iframe.src = '<?= URLROOT ?>/supervisor/printMissionOrder/' + id;
    var myModal = new bootstrap.Modal(document.getElementById('modal-preview-order'));
    myModal.show();
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
