<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .gh-container {
        background: #fff;
        border: 1px solid #d0d7de;
        border-radius: 6px;
        padding: 16px;
    }
    
    /* Month Grid: 7 columns (Mon-Sun) */
    .gh-month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        max-width: 450px;
        margin: 0 auto;
    }
    
    /* Week Grid: 7 large boxes */
    .gh-week-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
    }

    .gh-box {
        aspect-ratio: 1;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid rgba(27,31,35,0.06);
        position: relative;
    }
    
    .gh-box:hover {
        transform: scale(1.05);
        filter: brightness(0.95);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .gh-box.selected {
        outline: 2px solid #0969da;
        outline-offset: 2px;
    }

    /* Colors: Red (0h), Orange (<8h), Green (>=8h) */
    .bg-red { background-color: #ffeff0; color: #cf222e; }
    .bg-orange { background-color: #fff8eb; color: #9a6700; }
    .bg-green { background-color: #dafbe1; color: #1a7f37; }

    .box-label { font-size: 10px; font-weight: bold; margin-bottom: 2px; }
    .box-hours { font-size: 12px; font-weight: 800; }
    
    .grid-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin-bottom: 8px;
        text-align: center;
        font-size: 11px;
        color: #57606a;
        font-weight: 600;
        max-width: 450px;
        margin-left: auto;
        margin-right: auto;
    }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <?php if($data['view'] == 'day'): ?>
                        Détails du <?= date('d/m/Y', strtotime($data['selected_date'])) ?>
                    <?php elseif($data['view'] == 'week'): ?>
                        Semaine <?= $data['start_date']->format('W') ?> (<?= $data['start_date']->format('M Y') ?>)
                    <?php else: ?>
                        <?= $data['start_date']->format('F Y') ?>
                    <?php endif; ?>
                </h2>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <?php if($data['view'] != 'month'): ?>
                        <a href="?view=<?= $data['view'] == 'day' ? 'week' : 'month' ?>&offset=<?= $data['offset'] ?>&date=<?= $data['selected_date'] ?>" class="btn btn-outline-secondary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11l-4 4l4 4m-4 -4h11a4 4 0 0 0 0 -8h-1" /></svg>
                            Retour
                        </a>
                    <?php endif; ?>
                    
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
                    <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
                </div>
                <div class="gh-month-grid">
                    <?php 
                    $current = clone $data['start_date'];
                    $firstDay = (int)$current->format('N');
                    for($i = 1; $i < $firstDay; $i++) echo '<div></div>'; // Offset

                    while($current <= $data['end_date']): 
                        $date_str = $current->format('Y-m-d');
                        $day_entries = array_filter($data['entries'], function($e) use ($date_str) { return $e->date == $date_str; });
                        $total_hours = array_reduce($day_entries, function($c, $e) { return $c + (strtotime($e->end_time) - strtotime($e->start_time)) / 3600; }, 0);
                        $color = $total_hours >= 8 ? 'bg-green' : ($total_hours > 0 ? 'bg-orange' : 'bg-red');
                    ?>
                        <div class="gh-box <?= $color ?>" 
                             title="<?= $current->format('d/m') ?>: <?= number_format($total_hours, 1) ?>h"
                             ondblclick="window.location.href='?view=week&date=<?= $date_str ?>'"
                             data-bs-toggle="tooltip">
                             <span class="box-label"><?= $current->format('d') ?></span>
                        </div>
                    <?php $current->modify('+1 day'); endwhile; ?>
                </div>
                <div class="text-center mt-3 text-muted small">Double-cliquez sur un jour pour ouvrir la semaine.</div>
            </div>

        <?php elseif($data['view'] == 'week'): ?>
            <div class="gh-container">
                <div class="gh-week-grid">
                    <?php 
                    $current = clone $data['start_date'];
                    $days_fr = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                    for($i=0; $i<7; $i++): 
                        $date_str = $current->format('Y-m-d');
                        $day_entries = array_filter($data['entries'], function($e) use ($date_str) { return $e->date == $date_str; });
                        $total_hours = array_reduce($day_entries, function($c, $e) { return $c + (strtotime($e->end_time) - strtotime($e->start_time)) / 3600; }, 0);
                        $color = $total_hours >= 8 ? 'bg-green' : ($total_hours > 0 ? 'bg-orange' : 'bg-red');
                    ?>
                        <div class="gh-box <?= $color ?> py-4" 
                             onclick="window.location.href='?view=day&date=<?= $date_str ?>'">
                             <span class="box-label"><?= $days_fr[$i] ?></span>
                             <span class="box-hours"><?= number_format($total_hours, 1) ?>h</span>
                             <span class="small opacity-50"><?= $current->format('d/m') ?></span>
                        </div>
                    <?php $current->modify('+1 day'); endfor; ?>
                </div>
                <div class="text-center mt-3 text-muted small">Cliquez sur un jour pour gérer les détails.</div>
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
                                                <button onclick='editEntry(<?= json_encode($entry) ?>)' class="btn btn-icon btn-sm"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg></button>
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
