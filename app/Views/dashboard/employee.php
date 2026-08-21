<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php
// Cumul des heures par jour (semaine courante) pour la bande "Ma semaine".
$dayHours = [];
foreach ($data['week_entries'] ?? [] as $e) {
    $dur = (strtotime($e->end_time) - strtotime($e->start_time)) / 3600;
    $dayHours[$e->date] = ($dayHours[$e->date] ?? 0) + $dur;
}
if (!function_exists('emp_ts_level')) {
    function emp_ts_level($h) {
        if ($h <= 0) return 0; if ($h < 2) return 1; if ($h < 4) return 2; if ($h < 6) return 3; return 4;
    }
}
$weekDays = [];
$wd = new DateTime($data['week_start']);
for ($i = 0; $i < 5; $i++) { $weekDays[] = clone $wd; $wd->modify('+1 day'); } // lun -> ven
$dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven'];

// --- Grille annuelle (façon GitHub) : année en cours ---
$yhours = [];
foreach ($data['year_entries'] ?? [] as $e) {
    $dur = (strtotime($e->end_time) - strtotime($e->start_time)) / 3600;
    $yhours[$e->date] = ($yhours[$e->date] ?? 0) + $dur;
}
$yearNum   = (int) ($data['year'] ?? date('Y'));
$yStart    = new DateTime("$yearNum-01-01");
$yEnd      = new DateTime("$yearNum-12-31");
$gridStart = (clone $yStart)->modify('monday this week');
$gridEnd   = (clone $yEnd)->modify('sunday this week');
$yTotal    = array_sum($yhours);
$monthsFr3 = [1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Aoû',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'];
$yearCols  = [];
$cur = clone $gridStart;
while ($cur <= $gridEnd) {
    $col = ['month' => null, 'cells' => []];
    for ($i = 0; $i < 7; $i++) {
        $inYear = ($cur >= $yStart && $cur <= $yEnd);
        $ds = $cur->format('Y-m-d');
        $h  = $inYear ? ($yhours[$ds] ?? 0) : null;
        $col['cells'][] = ['in' => $inYear, 'h' => $h, 'date' => $ds, 'lvl' => $inYear ? emp_ts_level($h) : 0];
        if ($inYear && $cur->format('j') === '1') { $col['month'] = $monthsFr3[(int) $cur->format('n')]; }
        $cur->modify('+1 day');
    }
    $yearCols[] = $col;
}
?>

<style>
    .my-week { display:flex; gap:12px; flex-wrap:wrap; }
    .my-day { flex:1 1 90px; min-width:90px; border:1px solid var(--line); border-radius:6px; padding:10px; text-align:center; }
    .my-day .lbl { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); }
    .my-day .date { font-size:.72rem; color:var(--muted); }
    .my-day .hrs { font-size:1.15rem; font-weight:700; margin-top:4px; }
    .my-day.l0 .hrs { color:#adb5bd; }
    .my-day.l1 { background:#eafaf0; } .my-day.l2 { background:#d3f3df; }
    .my-day.l3 { background:#9be9a8; } .my-day.l4 { background:#40c463; color:#0b3d1e; }
    :root[data-bs-theme="dark"] .my-day.l1 { background:#12261b; } :root[data-bs-theme="dark"] .my-day.l2 { background:#153726; }
    :root[data-bs-theme="dark"] .my-day.l3 { background:#1c5233; } :root[data-bs-theme="dark"] .my-day.l4 { background:#216e39; color:#fff; }

    /* Mini-grille annuelle (façon GitHub) */
    .emp-year-scroll { overflow-x:auto; padding-bottom:6px; }
    .emp-year-months { display:flex; gap:3px; margin-bottom:4px; }
    .emp-year-months .ym { width:12px; flex:0 0 12px; font-size:9px; color:var(--muted); white-space:nowrap; }
    .emp-year-grid { display:flex; gap:3px; }
    .emp-year-col { display:flex; flex-direction:column; gap:3px; }
    .emp-yc { width:12px; height:12px; flex:0 0 12px; border-radius:2px; background:#ebedf0; display:block; }
    .emp-yc.empty { background:transparent; }
    .emp-yc.l1 { background:#9be9a8; } .emp-yc.l2 { background:#40c463; }
    .emp-yc.l3 { background:#30a14e; } .emp-yc.l4 { background:#216e39; }
    .emp-year-legend { font-size:.72rem; color:var(--muted); display:flex; align-items:center; gap:3px; }
    .emp-year-legend .emp-yc { width:11px; height:11px; }
    :root[data-bs-theme="dark"] .emp-yc { background:#1b2735; }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Bonjour, <?= e($_SESSION['user_firstname'] ?? '') ?></h2>
                <div class="text-muted mt-1">Voici un résumé de votre feuille de temps</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="<?= URLROOT ?>/timesheets" class="btn btn-primary">Ouvrir ma feuille de temps</a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <!-- Stats personnelles -->
            <div class="col-md-6">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= number_format((float) $data['stats']['ts_week_hours'], 1, ',', ' ') ?> h</div>
                                <div class="text-muted">Heures saisies cette semaine</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-yellow text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3.5 5.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 11.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 17.5l1.5 1.5l2.5 -2.5" /><line x1="11" y1="6" x2="20" y2="6" /><line x1="11" y1="12" x2="20" y2="12" /><line x1="11" y1="18" x2="20" y2="18" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= (int) $data['stats']['ts_pending'] ?> activités</div>
                                <div class="text-muted">En attente de validation</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ma semaine -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Ma semaine</h3>
                        <div class="text-muted small">
                            Du <?= e(date('d/m', strtotime($data['week_start']))) ?> au <?= e(date('d/m/Y', strtotime($data['week_end']))) ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="my-week">
                            <?php foreach ($weekDays as $i => $d): ?>
                                <?php $h = $dayHours[$d->format('Y-m-d')] ?? 0; $lvl = emp_ts_level($h); ?>
                                <a class="my-day l<?= $lvl ?> text-reset text-decoration-none" href="<?= URLROOT ?>/timesheets?view=day&date=<?= $d->format('Y-m-d') ?>">
                                    <div class="lbl"><?= $dayLabels[$i] ?></div>
                                    <div class="date"><?= $d->format('d/m') ?></div>
                                    <div class="hrs"><?= number_format($h, 1) ?>h</div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mon année (grille façon GitHub) -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title">Mon année <?= $yearNum ?></h3>
                        <div class="emp-year-legend">Moins
                            <span class="emp-yc"></span><span class="emp-yc l1"></span><span class="emp-yc l2"></span><span class="emp-yc l3"></span><span class="emp-yc l4"></span>
                            Plus
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small mb-2">Total saisi cette année : <strong><?= number_format($yTotal, 0, ',', ' ') ?> h</strong></div>
                        <div class="emp-year-scroll">
                            <div class="emp-year-months">
                                <?php foreach ($yearCols as $c): ?>
                                    <div class="ym"><?= $c['month'] ?? '' ?></div>
                                <?php endforeach; ?>
                            </div>
                            <div class="emp-year-grid">
                                <?php foreach ($yearCols as $c): ?>
                                    <div class="emp-year-col">
                                        <?php foreach ($c['cells'] as $cell): ?>
                                            <?php if (!$cell['in']): ?>
                                                <span class="emp-yc empty"></span>
                                            <?php else: ?>
                                                <a class="emp-yc<?= $cell['lvl'] ? ' l' . $cell['lvl'] : '' ?>" href="<?= URLROOT ?>/timesheets?view=day&date=<?= $cell['date'] ?>" title="<?= number_format((float) $cell['h'], 1) ?> h — <?= date('d/m/Y', strtotime($cell['date'])) ?>"></a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Actions rapides</h3></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/timesheets" class="btn btn-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>
                                    Ma feuille de temps
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/ged" class="btn btn-outline-secondary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                                    Mes Documents
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= URLROOT ?>/employees/details/<?= (int) $_SESSION['employee_id'] ?>" class="btn btn-outline-info w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    Consulter mon Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières activités -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Mes dernières activités (semaine)</h3></div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Horaire</th>
                                    <th>Catégorie</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['week_entries'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune activité saisie cette semaine.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice(array_reverse($data['week_entries']), 0, 8) as $en): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= e(date('d/m/Y', strtotime($en->date))) ?></td>
                                        <td class="text-nowrap"><span class="badge bg-blue-lt"><?= e(substr($en->start_time, 0, 5)) ?> - <?= e(substr($en->end_time, 0, 5)) ?></span></td>
                                        <td><?= e($en->category) ?></td>
                                        <td class="small text-muted"><?= e($en->task_description) ?></td>
                                        <td>
                                            <?php if (($en->status ?? '') === 'valide'): ?><span class="badge bg-success">Validé</span>
                                            <?php elseif (($en->status ?? '') === 'rejete'): ?><span class="badge bg-danger">Rejeté</span>
                                            <?php else: ?><span class="badge bg-warning">En attente</span><?php endif; ?>
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
