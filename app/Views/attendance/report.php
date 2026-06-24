<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <h2 class="page-title">Rapports de présence</h2>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <form action="<?= URLROOT ?>/attendance/report" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employé</label>
                        <select name="employee_id" class="form-select">
                            <option value="">Tous les employés</option>
                            <?php foreach ($data['employees'] as $emp): ?>
                                <option value="<?= $emp->id ?>" <?= ($data['filters']['employee_id'] == $emp->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp->prenom . ' ' . $emp->nom) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Du</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Au</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Matricule</th>
                            <th>Arrivée</th>
                            <th>Sortie</th>
                            <th>Présence</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['results'] as $res): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($res->date_jour)) ?></td>
                            <td><?= htmlspecialchars($res->prenom . ' ' . $res->nom) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($res->matricule ?? '') ?></td>
                            <td class="text-success fw-bold"><?= date('H:i:s', strtotime($res->heure_arrivee)) ?></td>
                            <td class="text-danger fw-bold"><?= date('H:i:s', strtotime($res->heure_sortie)) ?></td>
                            <td><?= htmlspecialchars($res->duree_presence) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="showAttendanceDetails(<?= (int) $res->employe_id ?>, '<?= htmlspecialchars($res->date_jour) ?>', '<?= htmlspecialchars(addslashes($res->prenom . ' ' . $res->nom)) ?>')">
                                    Détails
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['results'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">Aucun pointage sur la période.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails -->
<div class="modal modal-blur fade" id="modal-attendance-details" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendance-modal-title">Détails du jour</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-vcenter">
                    <thead><tr><th>Heure</th><th>Type</th></tr></thead>
                    <tbody id="attendance-modal-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function showAttendanceDetails(empId, date, name) {
        var modal = new bootstrap.Modal(document.getElementById('modal-attendance-details'));
        var content = document.getElementById('attendance-modal-body');
        document.getElementById('attendance-modal-title').innerText = name + ' — ' + date;
        content.innerHTML = '<tr><td colspan="2" class="text-center">Chargement...</td></tr>';
        modal.show();

        fetch('<?= URLROOT ?>/attendance/details?employee_id=' + encodeURIComponent(empId) + '&date=' + encodeURIComponent(date))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                content.innerHTML = '';
                if (!data.length) {
                    content.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Aucun passage.</td></tr>';
                    return;
                }
                data.forEach(function (l) {
                    var badge = l.type_pointage === 0
                        ? '<span class="badge bg-success">Entrée</span>'
                        : '<span class="badge bg-danger">Sortie</span>';
                    content.innerHTML += '<tr><td>' + l.heure + '</td><td>' + badge + '</td></tr>';
                });
            });
    }
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
