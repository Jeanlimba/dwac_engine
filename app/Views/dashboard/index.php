<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php
// --- Synthèse semaine "style GitHub" : heatmap employés × jours (lun-ven) ---
$weekDays = [];
$wd = new DateTime($data['week_start']);
for ($i = 0; $i < 5; $i++) { $weekDays[] = clone $wd; $wd->modify('+1 day'); } // lundi -> vendredi
$dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven'];

// Cumul des heures par employé et par jour.
$hoursMap = [];
foreach ($data['ts_detailed'] ?? [] as $e) {
    $dur = (strtotime($e->end_time) - strtotime($e->start_time)) / 3600;
    $hoursMap[$e->employee_id][$e->date] = ($hoursMap[$e->employee_id][$e->date] ?? 0) + $dur;
}
$tsHasHours = false;
foreach ($data['ts_performance'] ?? [] as $p) {
    if ((float) ($p->total_hours ?? 0) > 0) { $tsHasHours = true; break; }
}
// Niveau d'intensité (0 à 4) selon les heures d'une journée.
if (!function_exists('ts_level')) {
    function ts_level($h) {
        if ($h <= 0) return 0;
        if ($h < 2)  return 1;
        if ($h < 4)  return 2;
        if ($h < 6)  return 3;
        return 4;
    }
}
?>
<style>
    .ts-heatmap { display:flex; flex-direction:column; gap:6px; overflow-x:auto; padding-bottom:4px; }
    .ts-hm-row { display:flex; align-items:center; gap:6px; }
    .ts-hm-emp { width:140px; flex:0 0 140px; font-size:.85rem; color:var(--ink-soft); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ts-hm-daylabel { width:34px; flex:0 0 34px; text-align:center; font-size:.68rem; color:var(--muted); line-height:1.15; }
    .ts-hm-cell { width:34px; height:34px; flex:0 0 34px; border-radius:4px; background:#ebedf0; }
    .ts-hm-total { margin-left:6px; font-size:.8rem; color:var(--muted); white-space:nowrap; }
    .ts-hm-legend { display:flex; align-items:center; gap:4px; justify-content:flex-end; font-size:.72rem; color:var(--muted); margin-top:12px; }
    .ts-hm-legend .box { width:14px; height:14px; border-radius:3px; display:inline-block; background:#ebedf0; }
    .ts-hm-l1 { background:#9be9a8 !important; } .ts-hm-l2 { background:#40c463 !important; }
    .ts-hm-l3 { background:#30a14e !important; } .ts-hm-l4 { background:#216e39 !important; }
    :root[data-bs-theme="dark"] .ts-hm-cell, :root[data-bs-theme="dark"] .ts-hm-legend .box { background:#1b2735; }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tableau de Bord Gestionnaire</h2>
                <div class="text-muted mt-1">Aperçu de votre organisation aujourd'hui</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <!-- Stats -->
            <div class="col-md-6 col-xl-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-blue text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= (int) $data['stats']['employees_count'] ?> Employés</div>
                                <div class="text-muted">Effectif total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-purple text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><line x1="12" y1="12" x2="12" y2="12.01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= (int) $data['stats']['active_missions_count'] ?> / <?= (int) $data['stats']['missions_count'] ?> Missions</div>
                                <div class="text-muted">Missions en cours</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-yellow text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3.5 5.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 11.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 17.5l1.5 1.5l2.5 -2.5" /><line x1="11" y1="6" x2="20" y2="6" /><line x1="11" y1="12" x2="20" y2="12" /><line x1="11" y1="18" x2="20" y2="18" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium"><?= (int) $data['stats']['ts_pending_count'] ?> Timesheets</div>
                                <div class="text-muted">En attente de validation</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
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
                                <div class="text-muted">Heures saisies (semaine)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Synthèse Timesheet : heatmap "style GitHub" (semaine) -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">Activité de la semaine</h3>
                            <div class="text-muted small">
                                du <?= e(date('d/m/Y', strtotime($data['week_start']))) ?>
                                au <?= e(date('d/m/Y', strtotime($data['week_end']))) ?>
                            </div>
                        </div>
                        <a href="<?= URLROOT ?>/timesheets/pending" class="btn btn-sm btn-outline-primary">Feuilles à valider</a>
                    </div>
                    <div class="card-body">
                        <?php if ($tsHasHours): ?>
                            <div class="ts-heatmap">
                                <div class="ts-hm-row">
                                    <div class="ts-hm-emp"></div>
                                    <?php foreach ($weekDays as $i => $day): ?>
                                        <div class="ts-hm-daylabel"><?= $dayLabels[$i] ?><br><?= $day->format('d/m') ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php foreach ($data['ts_performance'] as $p): ?>
                                    <div class="ts-hm-row">
                                        <div class="ts-hm-emp" title="<?= e($p->prenom . ' ' . $p->nom) ?>"><?= e($p->prenom . ' ' . $p->nom) ?></div>
                                        <?php foreach ($weekDays as $day):
                                            $h = $hoursMap[$p->employee_id][$day->format('Y-m-d')] ?? 0;
                                            $lvl = ts_level($h);
                                        ?>
                                            <div class="ts-hm-cell<?= $lvl ? ' ts-hm-l' . $lvl : '' ?>" title="<?= number_format($h, 1) ?> h — <?= $day->format('d/m/Y') ?>"></div>
                                        <?php endforeach; ?>
                                        <span class="ts-hm-total"><?= number_format((float) $p->total_hours, 1) ?> h</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="ts-hm-legend">
                                Moins
                                <span class="box"></span>
                                <span class="box ts-hm-l1"></span>
                                <span class="box ts-hm-l2"></span>
                                <span class="box ts-hm-l3"></span>
                                <span class="box ts-hm-l4"></span>
                                Plus
                            </div>
                        <?php else: ?>
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>
                                </div>
                                <p class="empty-title">Aucune heure saisie cette semaine</p>
                                <p class="empty-subtitle text-muted">La grille d'activité se remplira dès que les employés saisiront leurs heures.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Missions par statut -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Missions par statut</h3></div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <?php if (!empty($data['mission_status'])): ?>
                            <div id="chart-mission-status" class="w-100"></div>
                        <?php else: ?>
                            <div class="text-muted text-center py-4">Aucune mission enregistrée.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var mEl = document.getElementById('chart-mission-status');
    if (mEl) {
        new ApexCharts(mEl, {
            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
            series: <?= json_encode(array_map(fn($r) => (int) $r->total, $data['mission_status'] ?? [])) ?>,
            labels: <?= json_encode(array_map(fn($r) => $r->status ?: '—', $data['mission_status'] ?? []), JSON_UNESCAPED_UNICODE) ?>,
            legend: { position: 'bottom' },
            colors: ['#f59f00', '#2fb344', '#206bc4', '#d63939', '#ae3ec9', '#4299e1', '#74b816'],
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: { theme: isDark ? 'dark' : 'light' },
            dataLabels: { enabled: true }
        }).render();
    }
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
