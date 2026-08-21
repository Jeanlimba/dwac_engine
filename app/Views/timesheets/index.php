<?php 
require APPROOT . '/Views/inc/header.php'; 

$months_fr = [
    'January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars', 'April' => 'Avril',
    'May' => 'Mai', 'June' => 'Juin', 'July' => 'Juillet', 'August' => 'Août',
    'September' => 'Septembre', 'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'
];
$days_fr = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
?>

<style>
    .gh-container {
        background: #fff;
        border: 1px solid #d0d7de;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Month Grid with Separators */
    .gh-month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        max-width: 480px;
        margin: 0 auto;
        position: relative;
    }
    
    /* Subtle vertical lines between columns */
    .gh-month-grid::before {
        content: "";
        position: absolute;
        top: 0; bottom: 0; left: 0; right: 0;
        background-image: linear-gradient(to right, #f3f4f6 1px, transparent 1px);
        background-size: calc(100% / 7) 100%;
        pointer-events: none;
        z-index: 0;
    }
    
    .gh-week-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 15px;
    }

    .gh-box {
        aspect-ratio: 1;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        z-index: 1;
    }
    
    .gh-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        z-index: 2;
    }

    /* Vibrant Colors with White Text */
    .bg-red { background-color: #d73a49; color: #fff; } /* GitHub Red */
    .bg-orange { background-color: #f66a0a; color: #fff; } /* GitHub Orange */
    .bg-green { background-color: #28a745; color: #fff; } /* GitHub Green */
    .bg-weekend { background-color: #f6f8fa; color: #57606a; border: 1px dashed #d0d7de; }

    .box-label { font-size: 11px; font-weight: 700; margin-bottom: 2px; }
    .box-hours { font-size: 13px; font-weight: 900; }
    
    .grid-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin-bottom: 15px;
        text-align: center;
        max-width: 480px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .day-header-label {
        background: #f1f5f9;
        color: #475569;
        padding: 4px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .page-title-fr {
        font-weight: 800;
        color: #1e293b;
    }

    /* Vue Année : grille de contribution (façon GitHub) */
    .ts-year-scroll { overflow-x: auto; padding-bottom: 6px; }
    .ts-year-months { display: flex; gap: 3px; margin-bottom: 4px; }
    .ts-year-months .ym { width: 13px; flex: 0 0 13px; font-size: 9px; color: #57606a; white-space: nowrap; overflow: visible; }
    .ts-year-grid { display: flex; gap: 3px; }
    .ts-year-col { display: flex; flex-direction: column; gap: 3px; }
    .yc { width: 13px; height: 13px; flex: 0 0 13px; border-radius: 2px; background: #ebedf0; display: block; }
    .yc.empty { background: transparent; }
    .yc.l1 { background: #9be9a8; } .yc.l2 { background: #40c463; }
    .yc.l3 { background: #30a14e; } .yc.l4 { background: #216e39; }
    .ts-year-legend { font-size: .72rem; color: #57606a; display: flex; align-items: center; gap: 3px; }
    .ts-year-legend .yc { width: 12px; height: 12px; }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title page-title-fr">
                    <?php if($data['view'] == 'day'): ?>
                        Détails du <?= date('d/m/Y', strtotime($data['selected_date'])) ?>
                    <?php elseif($data['view'] == 'week'): ?>
                        Semaine <?= $data['start_date']->format('W') ?> (<?= $months_fr[$data['start_date']->format('F')] ?>)
                    <?php elseif($data['view'] == 'year'): ?>
                        Année <?= $data['start_date']->format('Y') ?>
                    <?php else: ?>
                        <?= $months_fr[$data['start_date']->format('F')] ?> <?= $data['start_date']->format('Y') ?>
                    <?php endif; ?>
                </h2>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <div class="btn-group">
                        <?php foreach (['day' => 'Jour', 'week' => 'Semaine', 'month' => 'Mois', 'year' => 'Année'] as $v => $lbl): ?>
                            <a href="?view=<?= $v ?>&date=<?= e($data['selected_date']) ?>" class="btn btn-sm <?= $data['view'] === $v ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $lbl ?></a>
                        <?php endforeach; ?>
                    </div>

                    <div class="btn-group">
                        <a href="?view=<?= $data['view'] ?>&offset=<?= $data['offset'] - 1 ?>&date=<?= $data['selected_date'] ?>" class="btn btn-outline-primary btn-sm btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>
                        </a>
                        <a href="?view=<?= $data['view'] ?>&offset=0" class="btn btn-sm btn-outline-primary">Aujourd'hui</a>
                        <a href="?view=<?= $data['view'] ?>&offset=<?= $data['offset'] + 1 ?>&date=<?= $data['selected_date'] ?>" class="btn btn-outline-primary btn-sm btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <?php if($data['view'] == 'month'): ?>
            <div class="gh-container">
                <div class="grid-header">
                    <?php foreach($days_fr as $day): ?>
                        <div class="day-header-label"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="gh-month-grid">
                    <?php 
                    $current = clone $data['start_date'];
                    $firstDay = (int)$current->format('N');
                    for($i = 1; $i < $firstDay; $i++) echo '<div></div>'; // Offset

                    while($current <= $data['end_date']): 
                        $date_str = $current->format('Y-m-d');
                        $dayOfWeek = (int)$current->format('N');
                        $day_entries = array_filter($data['entries'], function($e) use ($date_str) { return $e->date == $date_str; });
                        $total_hours = array_reduce($day_entries, function($c, $e) { return $c + (strtotime($e->end_time) - strtotime($e->start_time)) / 3600; }, 0);
                        
                        if ($dayOfWeek >= 6) {
                            $color = 'bg-weekend';
                        } else {
                            $color = $total_hours >= 8 ? 'bg-green' : ($total_hours > 0 ? 'bg-orange' : 'bg-red');
                        }
                    ?>
                        <div class="gh-box <?= $color ?>" 
                             title="<?= $current->format('d/m') ?>: <?= number_format($total_hours, 1) ?>h"
                             ondblclick="window.location.href='?view=week&date=<?= $date_str ?>'"
                             data-bs-toggle="tooltip">
                             <span class="box-label"><?= $current->format('d') ?></span>
                        </div>
                    <?php $current->modify('+1 day'); endwhile; ?>
                </div>
                <div class="text-center mt-4 text-muted small">Double-cliquez sur un jour pour ouvrir la vue hebdomadaire.</div>
            </div>

        <?php elseif($data['view'] == 'week'): ?>
            <div class="gh-container">
                <div class="gh-week-grid">
                    <?php 
                    $current = clone $data['start_date'];
                    for($i=0; $i<7; $i++): 
                        $date_str = $current->format('Y-m-d');
                        $dayOfWeek = (int)$current->format('N');
                        $day_entries = array_filter($data['entries'], function($e) use ($date_str) { return $e->date == $date_str; });
                        $total_hours = array_reduce($day_entries, function($c, $e) { return $c + (strtotime($e->end_time) - strtotime($e->start_time)) / 3600; }, 0);
                        
                        if ($dayOfWeek >= 6) {
                            $color = 'bg-weekend';
                        } else {
                            $color = $total_hours >= 8 ? 'bg-green' : ($total_hours > 0 ? 'bg-orange' : 'bg-red');
                        }
                    ?>
                        <div class="gh-box <?= $color ?> py-4" 
                             onclick="window.location.href='?view=day&date=<?= $date_str ?>'">
                             <span class="box-label"><?= $days_fr[$i] ?></span>
                             <span class="box-hours"><?= number_format($total_hours, 1) ?>h</span>
                             <span class="small opacity-75"><?= $current->format('d/m') ?></span>
                        </div>
                    <?php $current->modify('+1 day'); endfor; ?>
                </div>
                <div class="text-center mt-4 text-muted small">Cliquez sur un jour pour gérer les activités détaillées.</div>
            </div>

        <?php elseif($data['view'] == 'day'): ?>
            <?php 
            $sel_date = $data['selected_date'];
            $sel_entries = array_filter($data['entries'], function($e) use ($sel_date) { return $e->date == $sel_date; });
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activités déclarées</h3>
                    <div class="card-actions">
                        <button onclick="addEntry('<?= $sel_date ?>')" class="btn btn-primary btn-sm">
                            + Ajouter une tâche
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Heures</th>
                                <th>Catégorie</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sel_entries)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Aucune activité ce jour.</td></tr>
                            <?php else: ?>
                                <?php foreach($sel_entries as $entry): ?>
                                    <tr>
                                        <td><span class="badge bg-blue-lt"><?= substr($entry->start_time, 0, 5) ?> - <?= substr($entry->end_time, 0, 5) ?></span></td>
                                        <td><strong><?= htmlspecialchars($entry->category) ?></strong></td>
                                        <td class="small"><?= htmlspecialchars($entry->task_description) ?></td>
                                        <td>
                                            <?php if ($entry->status == 'valide'): ?><span class="badge bg-success">Valide</span>
                                            <?php elseif ($entry->status == 'rejete'): ?><span class="badge bg-danger">Rejeté</span>
                                            <?php else: ?><span class="badge bg-warning">Attente</span><?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <button onclick='editEntry(<?= htmlspecialchars(json_encode($entry), ENT_QUOTES, "UTF-8") ?>)' class="btn btn-icon btn-sm"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>
                                                <button onclick="deleteEntry(<?= $entry->id ?>)" class="btn btn-icon btn-sm text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif($data['view'] == 'year'): ?>
            <?php
            $yhours = [];
            foreach ($data['entries'] as $e) {
                $dur = (strtotime($e->end_time) - strtotime($e->start_time)) / 3600;
                $yhours[$e->date] = ($yhours[$e->date] ?? 0) + $dur;
            }
            $ylevel = function ($h) {
                if ($h <= 0) return 0; if ($h < 2) return 1; if ($h < 4) return 2; if ($h < 6) return 3; return 4;
            };
            $yStart = clone $data['start_date'];                 // 1er janvier
            $yEnd   = clone $data['end_date'];                   // 31 décembre
            $gridStart = clone $yStart; $gridStart->modify('monday this week');
            $gridEnd   = clone $yEnd;   $gridEnd->modify('sunday this week');
            $totalYear = array_sum($yhours);
            $cols = [];
            $cur = clone $gridStart;
            while ($cur <= $gridEnd) {
                $col = ['month' => null, 'cells' => []];
                for ($i = 0; $i < 7; $i++) {
                    $inYear = ($cur >= $yStart && $cur <= $yEnd);
                    $ds = $cur->format('Y-m-d');
                    $h  = $inYear ? ($yhours[$ds] ?? 0) : null;
                    $col['cells'][] = ['in' => $inYear, 'h' => $h, 'date' => $ds, 'lvl' => $inYear ? $ylevel($h) : 0];
                    if ($inYear && $cur->format('j') === '1') { $col['month'] = mb_substr($months_fr[$cur->format('F')], 0, 3); }
                    $cur->modify('+1 day');
                }
                $cols[] = $col;
            }
            ?>
            <div class="gh-container">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="text-muted small">Total de l'année : <strong><?= number_format($totalYear, 0, ',', ' ') ?> h</strong></div>
                    <div class="ts-year-legend">Moins
                        <span class="yc"></span><span class="yc l1"></span><span class="yc l2"></span><span class="yc l3"></span><span class="yc l4"></span>
                        Plus
                    </div>
                </div>
                <div class="ts-year-scroll">
                    <div class="ts-year-months">
                        <?php foreach ($cols as $c): ?>
                            <div class="ym"><?= $c['month'] ?? '' ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ts-year-grid">
                        <?php foreach ($cols as $c): ?>
                            <div class="ts-year-col">
                                <?php foreach ($c['cells'] as $cell): ?>
                                    <?php if (!$cell['in']): ?>
                                        <span class="yc empty"></span>
                                    <?php else: ?>
                                        <a class="yc<?= $cell['lvl'] ? ' l' . $cell['lvl'] : '' ?>" href="?view=day&date=<?= $cell['date'] ?>" title="<?= number_format((float) $cell['h'], 1) ?> h — <?= date('d/m/Y', strtotime($cell['date'])) ?>"></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="text-center mt-3 text-muted small">Cliquez sur un jour pour ouvrir sa vue détaillée.</div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Entry -->
<div class="modal modal-blur fade" id="modal-entry" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Déclarer une activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="entry-form">
                <input type="hidden" name="id" id="entry-id">
                <input type="hidden" name="date" id="entry-date">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Heure début</label>
                            <input type="time" name="start_time" id="entry-start" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Heure fin</label>
                            <input type="time" name="end_time" id="entry-end" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Motif / Catégorie</label>
                        <select name="category" id="entry-category" class="form-select" required onchange="toggleMissionFields()">
                            <option value="Mission">En mission</option>
                            <option value="Voyage">En route / Déplacement / Voyage</option>
                            <option value="Courses administratif">Courses administratifs</option>
                            <option value="Autre course">Autres courses</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Autre temps libre">Autre temps libre</option>
                        </select>
                    </div>
                    <div id="mission-fields">
                        <div class="mb-3">
                            <label class="form-label">Sélectionner une mission</label>
                            <select name="mission_id" id="entry-mission-id" class="form-select">
                                <option value="">-- Choisir --</option>
                                <?php foreach($data['missions'] as $m): ?>
                                    <option value="<?= $m->id ?>"><?= htmlspecialchars($m->title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ou saisir manuellement</label>
                            <input type="text" name="custom_mission_name" id="entry-custom-mission" class="form-control" placeholder="Nom de la mission...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description de la tâche</label>
                        <textarea name="task_description" id="entry-desc" class="form-control" rows="3" placeholder="Qu'avez-vous fait ?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modal-entry');
    const entryModal = new bootstrap.Modal(modalElement);
    const entryForm = document.getElementById('entry-form');

    window.addEntry = function(date) {
        entryForm.reset();
        document.getElementById('entry-id').value = '';
        document.getElementById('entry-date').value = date;
        document.getElementById('modal-title').innerText = 'Déclarer une activité - ' + date;
        toggleMissionFields();
        entryModal.show();
    };

    window.editEntry = function(entry) {
        entryForm.reset();
        document.getElementById('entry-id').value = entry.id;
        document.getElementById('entry-date').value = entry.date;
        document.getElementById('entry-start').value = entry.start_time;
        document.getElementById('entry-end').value = entry.end_time;
        document.getElementById('entry-category').value = entry.category;
        document.getElementById('entry-mission-id').value = entry.mission_id || '';
        document.getElementById('entry-custom-mission').value = entry.custom_mission_name || '';
        document.getElementById('entry-desc').value = entry.task_description;
        document.getElementById('modal-title').innerText = 'Modifier l\'activité - ' + entry.date;
        toggleMissionFields();
        entryModal.show();
    };

    window.toggleMissionFields = function() {
        const category = document.getElementById('entry-category').value;
        const fields = document.getElementById('mission-fields');
        if (fields) fields.style.display = (category === 'Mission') ? 'block' : 'none';
    };

    entryForm.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(entryForm);
        
        fetch('<?= URLROOT ?>/timesheets/save', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert('Erreur lors de l\'enregistrement');
            }
        });
    };

    window.deleteEntry = function(id) {
        if(confirm('Supprimer cette déclaration ?')) {
            fetch('<?= URLROOT ?>/timesheets/delete/' + id, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                }
            });
        }
    };
});
</script>
