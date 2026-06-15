<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .gh-heatmap {
        display: flex;
        gap: 4px;
        margin-top: 10px;
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e6e7e9;
    }
    .gh-day-box {
        flex: 1;
        aspect-ratio: 1;
        border-radius: 3px;
        min-height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        transition: transform 0.2s;
        cursor: pointer;
    }
    .gh-day-box:hover {
        transform: scale(1.05);
    }
    .level-0 { background-color: #ebedf0; color: #6e7681; border: 1px solid rgba(27,31,35,0.06); }
    .level-1 { background-color: #9be9a8; color: #216e39; }
    .level-2 { background-color: #40c463; color: #fff; }
    .level-3 { background-color: #30a14e; color: #fff; }
    .level-4 { background-color: #216e39; color: #fff; }

    .day-label {
        font-size: 10px;
        color: #6e7681;
        margin-bottom: 4px;
        text-align: center;
        text-transform: uppercase;
    }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Mon Timesheet</h2>
                <div class="text-muted mt-1">
                    Semaine du <?= $data['monday']->format('d/m/Y') ?> au <?= $data['saturday']->format('d/m/Y') ?>
                </div>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <?php if ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager'): ?>
                        <a href="<?= URLROOT ?>/timesheets/pending" class="btn btn-warning btn-sm">
                            Validations
                        </a>
                    <?php endif; ?>
                    <div class="btn-group">
                        <a href="?week=<?= $data['week_offset'] - 1 ?>" class="btn btn-outline-primary btn-sm btn-icon" title="Précédent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>
                        </a>
                        <a href="<?= URLROOT ?>/timesheets" class="btn btn-sm <?= $data['week_offset'] == 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Cette semaine</a>
                        <a href="?week=<?= $data['week_offset'] + 1 ?>" class="btn btn-outline-primary btn-sm btn-icon" title="Suivant">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GitHub Style Weekly Heatmap -->
        <div class="gh-heatmap mt-3">
            <?php 
            $days_short = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
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
                
                $level = 0;
                if ($total_hours > 0) $level = 1;
                if ($total_hours >= 4) $level = 2;
                if ($total_hours >= 7.5) $level = 3;
                if ($total_hours >= 9) $level = 4;
            ?>
            <div class="flex-grow-1">
                <div class="day-label"><?= $days_short[$i] ?> <?= $current_date->format('d/m') ?></div>
                <div class="gh-day-box level-<?= $level ?>" 
                        onclick="addEntry('<?= $date_str ?>')"
                        title="Cliquez pour ajouter une tâche (<?= number_format($total_hours, 1) ?>h déjà faites)"
                        data-bs-toggle="tooltip">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 5l0 14"></path>
                        <path d="M5 12l14 0"></path>
                        </svg>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="page-body">
    <!-- Espace vide ou réservé pour d'autres widgets futurs -->
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
