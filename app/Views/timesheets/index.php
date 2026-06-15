<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Mon Timesheet</h2>
                <div class="text-muted mt-1">
                    Semaine du <?= $data['monday']->format('d/m/Y') ?> au <?= $data['saturday']->format('d/m/Y') ?> (Dimanche inclus pour info)
                </div>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <?php if ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager'): ?>
                        <a href="<?= URLROOT ?>/timesheets/pending" class="btn btn-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h10a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2z" /><polyline points="9 11 12 14 20 6" /><path d="M3 7v10a2 2 0 0 0 2 2h2" /></svg>
                            Validations en attente
                        </a>
                    <?php endif; ?>
                    <a href="<?= URLROOT ?>/timesheets/heatmap" class="btn btn-outline-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="4" width="16" height="16" rx="2" /><line x1="8" y1="4" x2="8" y2="20" /><line x1="12" y1="4" x2="12" y2="20" /><line x1="16" y1="4" x2="16" y2="20" /><line x1="4" y1="8" x2="20" y2="8" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="16" x2="20" y2="16" /></svg>
                        Vue Semestrielle
                    </a>
                    <a href="?week=<?= $data['week_offset'] - 1 ?>" class="btn btn-icon" title="Semaine précédente">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>
                    </a>
                    <a href="<?= URLROOT ?>/timesheets" class="btn <?= $data['week_offset'] == 0 ? 'btn-primary' : '' ?>">Cette semaine</a>
                    <a href="?week=<?= $data['week_offset'] + 1 ?>" class="btn btn-icon" title="Semaine suivante">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <?php 
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            for ($i = 0; $i < 7; $i++): 
                $current_date = clone $data['monday'];
                $current_date->modify("+$i days");
                $date_str = $current_date->format('Y-m-d');
                
                $day_entries = array_filter($data['entries'], function($e) use ($date_str) {
                    return $e->date == $date_str;
                });
                
                $total_seconds = 0;
                foreach($day_entries as $e) {
                    $total_seconds += strtotime($e->end_time) - strtotime($e->start_time);
                }
                $total_hours = $total_seconds / 3600;
                $progress = min(100, ($total_hours / 8) * 100);
                $progress_class = $total_hours >= 8 ? 'bg-success' : ($total_hours > 0 ? 'bg-primary' : 'bg-gray-300');
            ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><?= $days[$i] ?> <?= $current_date->format('d/m') ?></h3>
                            <div class="text-muted small"><?= number_format($total_hours, 1) ?> / 8h déclarées</div>
                        </div>
                        <div class="card-actions">
                            <button onclick="addEntry('<?= $date_str ?>')" class="btn btn-sm btn-outline-primary">
                                + Ajouter une tâche
                            </button>
                        </div>
                    </div>
                    <div class="progress progress-sm card-progress">
                        <div class="progress-bar <?= $progress_class ?>" style="width: <?= $progress ?>%" role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <?php if (!empty($day_entries)): ?>
                    <div class="list-group list-group-flush list-group-hoverable">
                        <?php foreach($day_entries as $entry): ?>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="badge bg-blue-lt"><?= substr($entry->start_time, 0, 5) ?> - <?= substr($entry->end_time, 0, 5) ?></span>
                                </div>
                                <div class="col">
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($entry->category) ?>
                                        <?php if ($entry->category == 'Mission'): ?>
                                            : <?= htmlspecialchars($entry->mission_title ?? $entry->custom_mission_name) ?>
                                        <?php endif; ?>
                                        <?php if ($entry->status == 'valide'): ?>
                                            <span class="badge bg-success ms-2">Valide (<?= $entry->rating ?>/5)</span>
                                        <?php elseif ($entry->status == 'rejete'): ?>
                                            <span class="badge bg-danger ms-2" title="<?= htmlspecialchars($entry->rejection_reason) ?>">Rejeté</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning ms-2">Soumis</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small"><?= htmlspecialchars($entry->task_description) ?></div>
                                    <?php if ($entry->status == 'rejete'): ?>
                                        <div class="text-danger small"><strong>Raison du rejet:</strong> <?= htmlspecialchars($entry->rejection_reason) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-list">
                                        <?php if ($entry->status != 'valide'): ?>
                                        <button onclick='editEntry(<?= json_encode($entry) ?>)' class="btn btn-icon btn-sm" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                        </button>
                                        <button onclick="deleteEntry(<?= $entry->id ?>)" class="btn btn-icon btn-sm text-danger" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endfor; ?>
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
