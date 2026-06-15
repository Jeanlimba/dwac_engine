<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .studio-container { display: flex; gap: 20px; height: calc(100vh - 150px); }
    .editor-pane { flex: 0 0 350px; overflow-y: auto; padding: 20px; background: #f8f9fa; border-right: 1px solid #ddd; }
    .preview-pane { flex: 1; overflow-y: auto; padding: 40px; background: #e9ecef; display: flex; justify-content: center; }
    
    .paper-preview {
        background: white;
        width: 210mm;
        min-height: 297mm;
        padding: 10mm 15mm 30mm 15mm;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        font-family: Verdana, Geneva, Tahoma, sans-serif;
        line-height: 1.4;
        color: #000;
        position: relative;
    }
    
    .preview-foot-img { position: absolute; bottom: 0; left: 0; width: 100%; height: auto; }
    
    .preview-header-dynamic { display: flex; align-items: center; gap: 30px; padding-bottom: 15px; margin-bottom: 20px; font-family: Verdana, sans-serif; }
    .preview-logo { height: 90px; }
    .preview-agency-info { flex: 1; font-size: 11px; line-height: 1.4; color: #000; text-align: center; border-bottom: 2px solid yellow; padding-bottom: 10px; }
    .preview-title { text-align: center; margin: 20px 0; }
    .preview-title h2 { border: 2px solid #000; display: inline-block; padding: 5px 20px; text-transform: uppercase; font-size: 20px; font-weight: bold; }
    .preview-content { margin-top: 30px; }
    .preview-content p { margin-bottom: 12px; font-size: 16px; }
    .preview-label { font-weight: bold; width: 220px; display: inline-block; }
    .preview-footer { margin-top: 40px; }
    .preview-date-line { text-align: right; margin-bottom: 20px; font-size: 16px; }
    .preview-signature-block { text-align: left; width: 350px; position: relative; }
    .preview-signature-img {  top: 20px; left: 50px; width: 500px; opacity: 0.8; pointer-events: none; }
    
    [contenteditable="true"]:hover { background: #fff9c4; outline: 1px dashed #fbc02d; cursor: text; }
    [contenteditable="true"]:focus { background: #fff9c4; outline: 2px solid #fbc02d; }

    @media print {
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        #printable-area { position: absolute; left: 0; top: 0; width: 210mm; height: 297mm; padding: 20mm; box-shadow: none; margin: 0; }
        .page-header, .editor-pane, .studio-container { display: none !important; }
        .preview-pane { padding: 0 !important; background: white !important; display: block !important; }
    }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Studio d'Édition - Ordre de Mission</h2>
            </div>
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-success" onclick="saveOrder()">Enregistrer les modifications</button>
                <a href="<?= URLROOT ?>/supervisor/missionOrders" class="btn btn-white">Retour</a>
            </div>
        </div>
    </div>
</div>

<div class="studio-container">
    <div class="editor-pane">
        <div class="mb-4">
            <label class="form-label fw-bold text-success">Barre d'outils (Formatage)</label>
            <div class="btn-group w-100 mb-2">
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('bold')" title="Gras"><i class="ti ti-bold"><b>B</b></i></button>
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('italic')" title="Italique"><i class="ti ti-italic"><i>I</i></i></button>
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('underline')" title="Souligné"><i class="ti ti-underline"><u>U</u></i></button>
            </div>
            <div class="btn-group w-100">
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('justifyLeft')" title="Gauche"><i class="ti ti-align-left">L</i></button>
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('justifyCenter')" title="Centré"><i class="ti ti-align-center">C</i></button>
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('justifyRight')" title="Droite"><i class="ti ti-align-right">R</i></button>
                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="formatText('justifyFull')" title="Justifié"><i class="ti ti-align-justified">J</i></button>
            </div>
            <small class="text-muted mt-2 d-block">Double-cliquez sur un texte à droite pour l'éditer.</small>
        </div>

        <hr>

        <form id="orderForm">
            <div class="mb-3">
                <label class="form-label fw-bold text-primary">Informations Agence</label>
                <input type="text" name="agency_name" class="form-control mb-1" placeholder="Nom de l'agence" value="<?= htmlspecialchars($data['order']->agency_name ?? $data['tenant']->name) ?>" oninput="syncPreview()">
                <textarea name="agency_address" class="form-control mb-1" placeholder="Adresse" rows="4" oninput="syncPreview()"><?= htmlspecialchars($data['order']->agency_address ?? $data['tenant']->address) ?></textarea>
                <input type="text" name="agency_phone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($data['order']->agency_phone ?? $data['tenant']->phone) ?>" oninput="syncPreview()">
            </div>
            
            <hr>
            
            <div class="mb-3">
                <label class="form-label">N° Ordre</label>
                <input type="text" name="order_number" class="form-control" value="<?= htmlspecialchars($data['order']->order_number) ?>" oninput="syncPreview()">
            </div>
            <div class="mb-3">
                <label class="form-label">Bénéficiaire</label>
                <select name="employee_id" class="form-select" onchange="syncPreview(); updateBeneficiaryName(this)">
                    <option value="">-- Collectif --</option>
                    <?php foreach($data['employees'] as $emp): ?>
                        <option value="<?= $emp->id ?>" <?= $emp->id == $data['order']->employee_id ? 'selected' : '' ?> data-name="<?= htmlspecialchars($emp->prenom . ' ' . $emp->nom) ?>" data-poste="<?= htmlspecialchars($emp->poste_name ?? 'Agent') ?>">
                            <?= htmlspecialchars($emp->prenom . ' ' . $emp->nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Objet</label>
                <input type="text" name="object" class="form-control" value="<?= htmlspecialchars($data['order']->object) ?>" oninput="syncPreview()">
            </div>
            <div class="mb-3">
                <label class="form-label">Lieux (Itinéraire)</label>
                <input type="text" name="itinerary" class="form-control" value="<?= htmlspecialchars($data['order']->itinerary) ?>" oninput="syncPreview()">
            </div>
            <div class="mb-3">
                <label class="form-label">Moyen de transport</label>
                <input type="text" name="means_of_transport" class="form-control" value="<?= htmlspecialchars($data['order']->means_of_transport) ?>" oninput="syncPreview()">
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label class="form-label">Départ</label>
                        <input type="date" name="departure_date" class="form-control" value="<?= $data['order']->departure_date ?>" onchange="syncPreview()">
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label class="form-label">Retour</label>
                        <input type="date" name="return_date" class="form-control" value="<?= $data['order']->return_date ?>" onchange="syncPreview()">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Lieu de signature</label>
                <input type="text" name="sign_city" class="form-control" value="<?= htmlspecialchars($data['order']->sign_city ?? 'Kinshasa') ?>" oninput="syncPreview()">
            </div>
            <div class="mb-3">
                <label class="form-label">Nom du Signataire</label>
                <input type="text" name="signatory_name" class="form-control" value="<?= htmlspecialchars($data['order']->signatory_name ?? 'NGUBI Mac') ?>" oninput="syncPreview()">
            </div>
            <div class="mb-3">
                <label class="form-label">Titre du Signataire</label>
                <input type="text" name="signatory_role" class="form-control" value="<?= htmlspecialchars($data['order']->signatory_role ?? 'Managing Director') ?>" oninput="syncPreview()">
            </div>
            <textarea name="footer_text" class="d-none" id="footer_text_input"><?= htmlspecialchars($data['order']->footer_text ?? 'Nous prions aux Autorités Politico-Administratives, militaires et policières de faciliter libre passage, d’apporter assistance et accorder l’immunité liée aux fonctions du porteur de ce document.') ?></textarea>
            <input type="hidden" name="type" id="order_type" value="<?= $data['order']->type ?>">
        </form>
        
        <div class="mt-4 border-top pt-3">
            <h4>Actions Rapides</h4>
            <button onclick="printDirect()" class="btn btn-outline-primary btn-sm w-100 mb-2">Imprimer en PDF (Lancer l'aperçu)</button>
            <a href="<?= URLROOT ?>/supervisor/downloadMissionOrder/<?= $data['order']->id ?>" class="btn btn-outline-secondary btn-sm w-100">Télécharger DOCX</a>
        </div>
    </div>
    
    <!-- Hidden Iframe for Direct Printing -->
    <iframe id="printFrame" style="display:none;"></iframe>
    </div>
    
    <div class="preview-pane">
        <div class="paper-preview" id="printable-area">
            <div class="preview-header-dynamic">
                <img src="<?= URLROOT ?>/assets/dwac.png" class="preview-logo" alt="Logo">
                <div class="preview-agency-info" ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)">
                    <div id="p-agency-name" data-sync="agency_name"><?= htmlspecialchars($data['order']->agency_name ?? $data['tenant']->name) ?></div>
                    <div id="p-agency-address" data-sync="agency_address"><?= htmlspecialchars($data['order']->agency_address ?? $data['tenant']->address) ?></div>
                    <div><span id="p-agency-phone" data-sync="agency_phone"><?= htmlspecialchars($data['order']->agency_phone ?? $data['tenant']->phone) ?></span></div>
                </div>
            </div>
            
            <div class="preview-title">
                <h2 ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)">Ordre de Mission N°<span id="p-order-number-inline" data-sync="order_number"><?= htmlspecialchars($data['order']->order_number) ?></span></h2>
            </div>
            
            <div class="preview-content">
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">NOMS :</span> <strong id="p-beneficiary"><?= $data['order']->employee_id ? htmlspecialchars($data['order']->prenom . ' ' . $data['order']->nom) : 'COLLECTIF' ?></strong></p>
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">FONCTION :</span> <span id="p-fonction"><?= $data['employee'] ? htmlspecialchars($data['employee']->poste_name ?? 'Agent') : 'Equipe de mission' ?></span></p>
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">OBJET :</span> <span id="p-object" data-sync="object"><?= htmlspecialchars($data['order']->object) ?></span></p>
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">LIEUX :</span> <span id="p-itinerary" data-sync="itinerary"><?= htmlspecialchars($data['order']->itinerary ?? '-') ?></span></p>
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">DURÉE :</span> <span id="p-duration">...</span></p>
                <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><span class="preview-label">MOYEN DE DÉPLACEMENT :</span> <span id="p-transport" data-sync="means_of_transport"><?= htmlspecialchars($data['order']->means_of_transport ?? '-') ?></span></p>
                
                <p id="p-footer-text" style="text-align: justify; margin-top: 40px;" ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)" data-sync="footer_text">
                    <?= $data['order']->footer_text ?? 'Nous prions aux Autorités Politico-Administratives, militaires et policières de faciliter libre passage, d’apporter assistance et accorder l’immunité liée aux fonctions du porteur de ce document.' ?>
                </p>
            </div>
            
            <div class="preview-footer">
                <div class="preview-date-line" ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)">
                    Fait à <span id="p-sign-city" data-sync="sign_city"><?= htmlspecialchars($data['order']->sign_city ?? 'Kinshasa') ?></span>, le <span id="p-date-val"><?= $data['order']->validated_at ? date('d/m/Y', strtotime($data['order']->validated_at)) : date('d/m/Y') ?></span>
                </div>
                <div class="preview-signature-block">
                    
                    <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><strong>Pour <span id="p-agency-name-footer" data-sync="agency_name"><?= htmlspecialchars($data['order']->agency_name ?? $data['tenant']->name) ?></span>,</strong></p>
                    <p style="margin-top: 50px;" ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><strong><span id="p-signatory-name" data-sync="signatory_name"><?= htmlspecialchars($data['order']->signatory_name ?? 'NGUBI Mac') ?></span></strong></p>
                    <p ondblclick="makeEditable(this)" onblur="disableEditing(this)" oninput="updateFormFromPreview(this)"><strong><span id="p-signatory-role" data-sync="signatory_role"><?= htmlspecialchars($data['order']->signatory_role ?? 'Managing Director') ?></span></strong></p>

                </div>
                    <img src="<?= URLROOT ?>/assets/signature_cachet.png" class="preview-signature-img" alt="Signature">
            </div>
            
            <img src="<?= URLROOT ?>/assets/foot.png" class="preview-foot-img" alt="Pied de page">
        </div>
    </div>
</div>

<script>
function formatText(command) {
    document.execCommand(command, false, null);
    // Sync active element if it has a sync attribute
    const activeEl = document.activeElement;
    if (activeEl && activeEl.hasAttribute('data-sync')) {
        updateFormFromPreview(activeEl);
    }
}

function makeEditable(el) {
    el.contentEditable = true;
    el.focus();
}

function disableEditing(el) {
    el.contentEditable = false;
}

function updateFormFromPreview(el) {
    const targetName = el.getAttribute('data-sync');
    if (targetName) {
        const input = document.getElementsByName(targetName)[0];
        if (input) {
            input.value = el.innerHTML.trim();
        }
    }
}

function syncPreview() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    
    document.getElementById('p-agency-name').innerHTML = formData.get('agency_name');
    if(document.getElementById('p-agency-name-footer')) {
        document.getElementById('p-agency-name-footer').innerHTML = formData.get('agency_name');
    }
    document.getElementById('p-agency-address').innerHTML = formData.get('agency_address').replace(/\n/g, '<br>');
    document.getElementById('p-agency-phone').innerHTML = formData.get('agency_phone');
    
    document.getElementById('p-order-number-inline').innerHTML = formData.get('order_number');
    document.getElementById('p-object').innerHTML = formData.get('object');
    document.getElementById('p-itinerary').innerHTML = formData.get('itinerary');
    document.getElementById('p-transport').innerHTML = formData.get('means_of_transport');
    
    // Signatory and Date City
    document.getElementById('p-sign-city').innerHTML = formData.get('sign_city');
    document.getElementById('p-signatory-name').innerHTML = formData.get('signatory_name');
    document.getElementById('p-signatory-role').innerHTML = formData.get('signatory_role');
    
    // Footer text
    document.getElementById('p-footer-text').innerHTML = formData.get('footer_text');
    
    // Duration calculation
    const start = new Date(formData.get('departure_date'));
    const end = new Date(formData.get('return_date'));
    if (!isNaN(start) && !isNaN(end)) {
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        const startStr = start.toLocaleDateString('fr-FR', options);
        const endStr = end.toLocaleDateString('fr-FR', options);
        
        document.getElementById('p-duration').innerHTML = `${diffDays} jours (du ${startStr} au ${endStr})`;
    }
}

function printDirect() {
    const iframe = document.getElementById('printFrame');
    iframe.src = '<?= URLROOT ?>/supervisor/printMissionOrder/<?= $data['order']->id ?>';
    iframe.onload = function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}

function updateBeneficiaryName(select) {
    const option = select.options[select.selectedIndex];
    if (select.value === "") {
        document.getElementById('p-beneficiary').innerText = 'COLLECTIF';
        document.getElementById('p-fonction').innerText = 'Equipe de mission';
        document.getElementById('order_type').value = 'collectif';
    } else {
        document.getElementById('p-beneficiary').innerText = option.getAttribute('data-name');
        document.getElementById('p-fonction').innerText = option.getAttribute('data-poste');
        document.getElementById('order_type').value = 'personnel';
    }
}

function saveOrder() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    
    fetch('<?= URLROOT ?>/supervisor/editMissionOrder/<?= $data['order']->id ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Enregistré avec succès !');
        } else {
            alert('Erreur lors de l\'enregistrement.');
        }
    });
}

// Initial sync
window.onload = syncPreview;
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
