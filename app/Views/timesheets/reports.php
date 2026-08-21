<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php
/* =========================================================================
 * Logique du rapport de performance Timesheet
 * -------------------------------------------------------------------------
 * - Objectif période  : jours ouvrés (lun-ven) × norme journalière.
 * - Attendu "à ce jour": jours ouvrés ÉCOULÉS (bornés à aujourd'hui) × norme.
 *   Le STATUT se base sur l'attendu à ce jour pour ne pas pénaliser une
 *   période encore en cours (ex. semaine non terminée).
 * - Heures sup. : cumul des heures d'un même employé sur une même journée
 *   au-delà de la norme journalière (ou travail le dimanche).
 * ========================================================================= */
$daily_norm = 8; // heures/jour attendues

$start = new DateTime($data['start_date']);
$end   = new DateTime($data['end_date']);

// Jours ouvrés de la période complète (objectif affiché).
$work_days = 0;
$t = clone $start;
while ($t <= $end) { if ($t->format('N') < 6) $work_days++; $t->modify('+1 day'); }
$expected_full = $work_days * $daily_norm;

// Jours ouvrés écoulés jusqu'à aujourd'hui (base du statut).
$today  = new DateTime('today');
$effEnd = ($end > $today) ? $today : $end;
$elapsed_work_days = 0;
if ($effEnd >= $start) {
    $t = clone $start;
    while ($t <= $effEnd) { if ($t->format('N') < 6) $elapsed_work_days++; $t->modify('+1 day'); }
}
$expected_elapsed = $elapsed_work_days * $daily_norm;

// Cumul des heures par employé et par jour (pour la détection d'heures sup.).
$dailyTotals = [];
foreach ($data['detailed'] as $d) {
    $dur = (strtotime($d->end_time) - strtotime($d->start_time)) / 3600;
    $k = $d->employee_id . '|' . $d->date;
    $dailyTotals[$k] = ($dailyTotals[$k] ?? 0) + $dur;
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Rapports de Performance (Timesheet)</h2>
                <div class="text-muted mt-1">
                    Objectif : <?= $daily_norm ?> h/jour ouvré
                    &middot; attendu à ce jour : <strong><?= $expected_elapsed ?> h</strong>
                    (<?= $elapsed_work_days ?>/<?= $work_days ?> jours ouvrés écoulés)
                </div>
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
                        <input type="date" name="start_date" class="form-control" value="<?= e($data['start_date']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="end_date" class="form-control" value="<?= e($data['end_date']) ?>">
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
                            <th>Total heures</th>
                            <th>Heures mission</th>
                            <th>Part mission</th>
                            <th>Note moy.</th>
                            <th>Validés</th>
                            <th>Suivi quota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['performance'] as $p):
                            $total_h   = (float) ($p->total_hours ?? 0);
                            $mission_h = (float) ($p->mission_hours ?? 0);
                            // Part du temps consacré aux missions : null si aucune heure (pas de donnée).
                            $perf_rate = $total_h > 0 ? ($mission_h / $total_h) * 100 : null;
                            $avg_rating = (float) ($p->avg_rating ?? 0);
                            // Statut basé sur l'attendu ÉCOULÉ ; null si la période n'a pas commencé.
                            $quota_rate = $expected_elapsed > 0 ? ($total_h / $expected_elapsed) * 100 : null;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex py-1 align-items-center">
                                    <span class="avatar me-2"><?= e(substr($p->prenom, 0, 1) . substr($p->nom, 0, 1)) ?></span>
                                    <div class="flex-fill">
                                        <div class="font-weight-medium"><?= e($p->prenom . ' ' . $p->nom) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap"><?= number_format($total_h, 1) ?>h <span class="text-muted">/ <?= $expected_full ?>h</span></td>
                            <td class="text-nowrap"><?= number_format($mission_h, 1) ?>h</td>
                            <td style="min-width:140px">
                                <?php if ($perf_rate === null): ?>
                                    <span class="text-muted">—</span>
                                <?php else: ?>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= number_format($perf_rate, 0) ?>%</span>
                                        <div class="progress progress-xs w-100">
                                            <div class="progress-bar bg-primary" style="width: <?= $perf_rate ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($avg_rating > 0): ?>
                                    <span class="badge bg-purple-lt"><?= number_format($avg_rating, 1) ?> / 5</span>
                                <?php else: ?>
                                    <span class="text-muted small">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $p->validated_entries ?></td>
                            <td>
                                <?php if ($quota_rate === null): ?>
                                    <span class="badge bg-secondary">À venir</span>
                                <?php else:
                                    $label = 'Critique'; $cls = 'bg-danger';
                                    if ($quota_rate >= 90)      { $label = 'À jour';    $cls = 'bg-success'; }
                                    elseif ($quota_rate >= 60)  { $label = 'Correct';   $cls = 'bg-info'; }
                                    elseif ($quota_rate >= 30)  { $label = 'En retard'; $cls = 'bg-warning'; }
                                ?>
                                    <span class="badge <?= $cls ?>" title="<?= number_format($quota_rate, 0) ?>% de l'attendu à ce jour (<?= $expected_elapsed ?>h)"><?= $label ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['performance'])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun employé sur cette période.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Détails des activités</h3>
                <div class="card-actions text-muted small">Les lignes surlignées signalent des heures supplémentaires (&gt; <?= $daily_norm ?>h/jour ou week-end).</div>
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
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['detailed'] as $d):
                            $duration  = (strtotime($d->end_time) - strtotime($d->start_time)) / 3600;
                            $dayTotal  = $dailyTotals[$d->employee_id . '|' . $d->date] ?? $duration;
                            $dow        = (int) date('N', strtotime($d->date));
                            $is_weekend = $dow >= 6; // samedi (6) ou dimanche (7) : hors semaine de travail
                            $ot_hours   = max(0, $dayTotal - $daily_norm); // heures sup. cumulées ce jour-là
                            $is_flag    = $is_weekend || $ot_hours > 0;
                        ?>
                        <tr class="<?= $is_flag ? 'bg-warning-lt' : '' ?>">
                            <td class="text-nowrap">
                                <?= date('d/m/Y', strtotime($d->date)) ?>
                                <?= $is_weekend ? '<span class="badge bg-red ms-1">' . ($dow === 7 ? 'Dimanche' : 'Samedi') . '</span>' : '' ?>
                            </td>
                            <td><?= e($d->prenom . ' ' . $d->nom) ?></td>
                            <td class="text-nowrap"><?= e(substr($d->start_time, 0, 5)) ?> - <?= e(substr($d->end_time, 0, 5)) ?></td>
                            <td class="text-nowrap">
                                <?= number_format($duration, 1) ?>h
                                <?php if ($ot_hours > 0): ?>
                                    <span class="badge bg-orange-lt ms-1" title="Cumul du jour : <?= number_format($dayTotal, 1) ?>h">+<?= number_format($ot_hours, 1) ?>h sup.</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($d->category) ?></td>
                            <td><?= $d->rating ? (int) $d->rating . '/5' : '-' ?></td>
                            <td>
                                <?php if ($d->status === 'valide'): ?>
                                    <span class="badge bg-success">Validé</span>
                                <?php elseif ($d->status === 'rejete'): ?>
                                    <span class="badge bg-danger">Rejeté</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">En attente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['detailed'])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucune activité sur cette période.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
