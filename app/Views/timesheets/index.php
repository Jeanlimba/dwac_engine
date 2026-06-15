<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .gh-heatmap {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e6e7e9;
    }
    .gh-day-box {
        width: 32px;
        height: 32px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        position: relative;
    }
    .gh-day-box:hover {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .gh-day-box.selected {
        border: 2px solid #000 !important;
        transform: scale(1.1);
    }
    
    /* Red-Orange-Green Scale */
    .level-empty { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; } /* Red (0h) */
    .level-partial { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; } /* Orange (<8h) */
    .level-full { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; } /* Green (>=8h) */

    .day-label {
        font-size: 9px;
        color: #6e7681;
        margin-bottom: 2px;
        text-align: center;
        text-transform: uppercase;
        width: 32px;
    }
    .view-switcher .btn.active {
        background-color: #206bc4;
        color: #fff;
    }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Mon Timesheet</h2>
                <div class="text-muted mt-1">
                    <?php if($data['view'] == 'month'): ?>
                        Mois de <?= $data['start_date']->format('F Y') ?>
                    <?php else: ?>
                        Semaine du <?= $data['start_date']->format('d/m') ?> au <?= $data['end_date']->format('d/m') ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <div class="btn-group view-switcher">
                        <a href="?view=day&date=<?= $data['selected_date'] ?>" class="btn btn-sm <?= $data['view'] == 'day' ? 'active' : '' ?>">Jour</a>
                        <a href="?view=week&offset=<?= $data['offset'] ?>" class="btn btn-sm <?= $data['view'] == 'week' ? 'active' : '' ?>">Semaine</a>
                        <a href="?view=month&offset=<?= $data['offset'] ?>" class="btn btn-sm <?= $data['view'] == 'month' ? 'active' : '' ?>">Mois</a>
                    </div>
                    
                    <div class="btn-group">
                        <a href="?view=<?= $data['view'] ?>&offset=<?= $data['offset'] - 1 ?>" class="btn btn-outline-primary btn-sm btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>
                        </a>
                        <a href="?view=<?= $data['view'] ?>&offset=0" class="btn btn-sm <?= $data['offset'] == 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Aujourd'hui</a>
                        <a href="?view=<?= $data['view'] ?>&offset=<?= $data['offset'] + 1 ?>" class="btn btn-outline-primary btn-sm btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heatmap Section -->
        <?php if($data['view'] != 'day'): ?>
        <div class="gh-heatmap mt-3">
            <?php 
            $current = clone $data['start_date'];
            while($current <= $data['end_date']): 
                $date_str = $current->format('Y-m-d');
                $day_entries = array_filter($data['entries'], function($e) use ($date_str) {
                    return $e->date == $date_str;
                });
                
                $total_seconds = 0;
                foreach($day_entries as $e) {
                    $total_seconds += strtotime($e->end_time) - strtotime($e->start_time);
                }
                $total_hours = $total_seconds / 3600;
                
                $level = 'empty';
                if ($total_hours >= 8) $level = 'full';
                elseif ($total_hours > 0) $level = 'partial';
                
                $isSelected = ($date_str == $data['selected_date']);
            ?>
            <div class="text-center">
                <div class="day-label"><?= $current->format('D d') ?></div>
                <div class="gh-day-box level-<?= $level ?> <?= $isSelected ? 'selected' : '' ?>" 
                     onclick="window.location.href='?view=day&date=<?= $date_str ?>'"
                     title="<?= number_format($total_hours, 1) ?>h déclarées"
                     data-bs-toggle="tooltip">
                     <?= $total_hours > 0 ? number_format($total_hours, 1) . 'h' : '' ?>
                </div>
            </div>
            <?php $current->modify('+1 day'); endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php 
        $sel_date = $data['selected_date'];
        $sel_entries = array_filter($data['entries'], function($e) use ($sel_date) {
            return $e->date == $sel_date;
        });
        ?>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Détails du <?= date('d/m/Y', strtotime($sel_date)) ?></h3>
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
                            <th>Catégorie / Mission</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sel_entries)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Aucune activité pour ce jour.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($sel_entries as $entry): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="badge bg-blue-lt">
                                            <?= substr($entry->start_time, 0, 5) ?> - <?= substr($entry->end_time, 0, 5) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($entry->category) ?></div>
                                        <?php if ($entry->category == 'Mission'): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($entry->mission_title ?? $entry->custom_mission_name) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($entry->task_description) ?></td>
                                    <td>
                                        <?php if ($entry->status == 'valide'): ?>
                                            <span class="badge bg-success">Valide</span>
                                        <?php elseif ($entry->status == 'rejete'): ?>
                                            <span class="badge bg-danger">Rejeté</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <?php if ($entry->status != 'valide'): ?>
                                                <button onclick='editEntry(<?= json_encode($entry) ?>)' class="btn btn-icon btn-sm" title="Modifier">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                                </button>
                                                <button onclick="deleteEntry(<?= $entry->id ?>)" class="btn btn-icon btn-sm text-danger" title="Supprimer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
        fields.style.display = (category === 'Mission') ? 'block' : 'none';
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
