<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Rapports de Performance (Timesheet)</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $data['start_date'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $data['end_date'] ?>">
                    </div>
                    <div class="col-md-4">
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
                            <th>Employé</th>
                            <th>Total Heures</th>
                            <th>Heures Mission</th>
                            <th>Perf. Mission</th>
                            <th>Note Moy.</th>
                            <th>Validés</th>
                            <th>Status Quota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $start = new DateTime($data['start_date']);
                        $end = new DateTime($data['end_date']);
                        $days_count = $start->diff($end)->days + 1;
                        // Exclude Sundays for expected hours if we follow Mon-Sat week
                        $work_days = 0;
                        $temp_date = clone $start;
                        while($temp_date <= $end) {
                            if($temp_date->format('N') < 7) $work_days++;
                            $temp_date->modify('+1 day');
                        }
                        $expected_hours = $work_days * 8;

                        foreach($data['performance'] as $p): 
                            $total_h = (float)($p->total_hours ?? 0);
                            $mission_h = (float)($p->mission_hours ?? 0);
                            $perf_rate = $total_h > 0 ? ($mission_h / $total_h) * 100 : 0;
                            $quota_rate = $expected_hours > 0 ? ($total_h / $expected_hours) * 100 : 0;
                            $avg_rating = (float)($p->avg_rating ?? 0);
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex py-1 align-items-center">
                                    <span class="avatar me-2"><?= substr($p->prenom, 0, 1) ?><?= substr($p->nom, 0, 1) ?></span>
                                    <div class="flex-fill">
                                        <div class="font-weight-medium"><?= htmlspecialchars($p->prenom . ' ' . $p->nom) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($total_h, 1) ?>h / <?= $expected_hours ?>h</td>
                            <td><?= number_format($mission_h, 1) ?>h</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= number_format($perf_rate, 0) ?>%</span>
                                    <div class="progress progress-xs w-100">
                                        <div class="progress-bar bg-blue" style="width: <?= $perf_rate ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($avg_rating > 0): ?>
                                    <span class="badge bg-purple-lt"><?= number_format($avg_rating, 1) ?> / 5</span>
                                <?php else: ?>
                                    <span class="text-muted small">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p->validated_entries ?></td>
                            <td>
                                <?php if ($quota_rate >= 95): ?>
                                    <span class="badge bg-success">Complet</span>
                                <?php elseif ($quota_rate >= 80): ?>
                                    <span class="badge bg-info">Acceptable</span>
                                <?php elseif ($quota_rate >= 50): ?>
                                    <span class="badge bg-warning">Incomplet</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Critique</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Détails des activités (Détection Heures Sup.)</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-mobile-md card-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Heures</th>
                            <th>Durée</th>
                            <th>Catégorie</th>
                            <th>Note</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['detailed'] as $d): 
                            $duration = (strtotime($d->end_time) - strtotime($d->start_time)) / 3600;
                            $is_sunday = date('N', strtotime($d->date)) == 7;
                            $is_overtime = $is_sunday || $duration > 8; // Simple rule: Sunday or > 8h in a single entry
                        ?>
                        <tr class="<?= $is_overtime ? 'bg-yellow-lt' : '' ?>">
                            <td><?= date('d/m/Y', strtotime($d->date)) ?> <?= $is_sunday ? '<span class="badge bg-red">Dimanche</span>' : '' ?></td>
                            <td><?= htmlspecialchars($d->prenom . ' ' . $d->nom) ?></td>
                            <td><?= substr($d->start_time, 0, 5) ?> - <?= substr($d->end_time, 0, 5) ?></td>
                            <td>
                                <span class="<?= $duration > 8 ? 'text-danger fw-bold' : '' ?>">
                                    <?= number_format($duration, 1) ?>h
                                </span>
                            </td>
                            <td><?= htmlspecialchars($d->category) ?></td>
                            <td><?= $d->rating ? $d->rating.'/5' : '-' ?></td>
                            <td>
                                <?php if ($d->status == 'valide'): ?>
                                    <span class="badge bg-success">Valide</span>
                                <?php elseif ($d->status == 'rejete'): ?>
                                    <span class="badge bg-danger">Rejeté</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">En attente</span>
                                <?php endif; ?>
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
