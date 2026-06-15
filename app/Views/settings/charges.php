<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Configuration des Charges
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="loadChargeForm()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Ajouter une charge
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                            <li class="nav-item">
                                <a href="#tabs-admin" class="nav-link active" data-bs-toggle="tab">Administrative</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-mission" class="nav-link" data-bs-toggle="tab">Mission</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active show" id="tabs-admin">
                                <div id="charges-admin-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-mission">
                                <div id="charges-mission-list">
                                    <div class="text-muted text-center py-4">Chargement...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal AJAX -->
<div class="modal modal-blur fade" id="modal-charge" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="modal-charge-content">
            <!-- Contenu chargé via AJAX -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCharges('Administrative');
    loadCharges('Mission');
});

function loadCharges(category) {
    const containerId = category === 'Administrative' ? 'charges-admin-list' : 'charges-mission-list';
    const container = document.getElementById(containerId);
    
    fetch('<?= URLROOT ?>/ajax/get_charges.php?category=' + category)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div class="text-danger text-center py-4">Erreur lors du chargement.</div>';
        });
}

function loadChargeForm(chargeId = null) {
    const modalElement = document.getElementById('modal-charge');
    const modalContent = document.getElementById('modal-charge-content');
    
    modalContent.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Chargement du formulaire...</div></div>';
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();

    let url = '<?= URLROOT ?>/ajax/charge_form.php';
    if (chargeId) url += '?id=' + chargeId;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            
            const saveBtn = document.getElementById('save_charge_button');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    const form = document.getElementById('charge_form');
                    const formData = new FormData(form);
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>...';

                    fetch('<?= URLROOT ?>/ajax/charge_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            modal.hide();
                            loadCharges('Administrative');
                            loadCharges('Mission');
                        } else {
                            alert(data.message || 'Erreur');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = 'Enregistrer';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Enregistrer';
                    });
                };
            }
        });
}

function deleteCharge(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette charge ?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('<?= URLROOT ?>/ajax/charge_form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCharges('Administrative');
                loadCharges('Mission');
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur lors de la suppression');
        });
    }
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
