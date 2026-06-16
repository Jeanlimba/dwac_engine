<script>
document.addEventListener("DOMContentLoaded", function() {
    const previewModal = new bootstrap.Modal(document.getElementById('modal-preview'));
    const previewContainer = document.getElementById('preview-container');
    const previewTitle = document.getElementById('preview-title');
    
    const renameModal = new bootstrap.Modal(document.getElementById('modal-rename'));
    const shareModal = new bootstrap.Modal(document.getElementById('modal-share'));
    const manageAccessModal = new bootstrap.Modal(document.getElementById('modal-manage-access'));
    const externalLinkModal = new bootstrap.Modal(document.getElementById('modal-external-link'));
    const destinationModal = new bootstrap.Modal(document.getElementById('modal-destination'));
    
    const currentFolderId = "<?= $data['current_folder']->id ?>";

    // Copier / Déplacer
    function openDestinationModal(id, type, name, action) {
        document.getElementById('destination-item-id').value = id;
        document.getElementById('destination-type').value = type;
        document.getElementById('destination-title').innerText = (action === 'copy' ? 'Copier "' : 'Déplacer "') + name + '"';
        document.getElementById('form-destination').action = "<?= URLROOT ?>/ged/" + action;
        document.getElementById('btn-destination-submit').innerText = action === 'copy' ? 'Copier ici' : 'Déplacer ici';

        const select = document.getElementById('destination-select');
        select.innerHTML = '<option value="">Chargement...</option>';
        
        destinationModal.show();

        fetch("<?= URLROOT ?>/ged/getFoldersTree")
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '';
                // On crée une map pour construire l'arborescence visuelle (indentation)
                const folderMap = {};
                folders.forEach(f => {
                    if (!folderMap[f.parent_id]) folderMap[f.parent_id] = [];
                    folderMap[f.parent_id].push(f);
                });

                function addOptions(parentId, depth) {
                    if (folderMap[parentId]) {
                        folderMap[parentId].forEach(f => {
                            // On empêche de déplacer un dossier dans lui-même
                            if (type === 'folder' && f.id == id) return;
                            
                            const option = document.createElement('option');
                            option.value = f.id;
                            option.innerText = "\u00A0\u00A0".repeat(depth) + (depth > 0 ? "└─ " : "") + f.name;
                            if (f.id == currentFolderId) option.selected = true;
                            select.appendChild(option);
                            addOptions(f.id, depth + 1);
                        });
                    }
                }
                addOptions(null, 0);
            });
    }

    document.querySelectorAll('.copy-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            openDestinationModal(this.getAttribute('data-id'), this.getAttribute('data-type'), this.getAttribute('data-name'), 'copy');
        });
    });

    document.querySelectorAll('.move-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            openDestinationModal(this.getAttribute('data-id'), this.getAttribute('data-type'), this.getAttribute('data-name'), 'move');
        });
    });

    // Visualisation de fichiers
    document.querySelectorAll('.view-file').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('.dropdown')) return;

            const name = this.getAttribute('data-name');
            const type = this.getAttribute('data-type').toLowerCase();
            const physical = this.getAttribute('data-physical');
            const url = "<?= URLROOT ?>/public/uploads/ged/" + physical;

            previewTitle.innerText = name;
            previewContainer.innerHTML = '';

            if (type === 'pdf') {
                previewContainer.innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border: none;"></iframe>`;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(type)) {
                previewContainer.innerHTML = `<img src="${url}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
            } else if (['mp4', 'webm', 'ogv'].includes(type)) {
                previewContainer.innerHTML = `<video src="${url}" controls autoplay style="max-width: 100%; max-height: 100%;"></video>`;
            } else if (['mp3', 'wav', 'ogg'].includes(type)) {
                previewContainer.innerHTML = `
                    <div class="text-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-primary mb-3" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><audio src="${url}" controls autoplay class="mt-3 w-100"></audio>
                    </div>`;
            } else {
                previewContainer.innerHTML = `
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                        <p>Aperçu non disponible</p>
                        <a href="${url}" class="btn btn-primary" target="_blank">Télécharger</a>
                    </div>`;
            }
            previewModal.show();
        });
    });

    // Renommage
    document.querySelectorAll('.rename-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('rename-id').value = this.getAttribute('data-id');
            document.getElementById('rename-type').value = this.getAttribute('data-type');
            document.getElementById('rename-name').value = this.getAttribute('data-name');
            renameModal.show();
        });
    });

    // Partage interne
    document.querySelectorAll('.share-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('share-item-id').value = this.getAttribute('data-id');
            document.getElementById('share-type').value = this.getAttribute('data-type');
            document.getElementById('share-title').innerText = 'Partager "' + this.getAttribute('data-name') + '"';
            shareModal.show();
        });
    });

    // Gérer les accès (Log)
    document.querySelectorAll('.manage-access-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const name = this.getAttribute('data-name');
            const body = document.getElementById('access-list-body');

            document.getElementById('manage-access-title').innerText = 'Accès pour "' + name + '"';
            body.innerHTML = '<tr><td colspan="3" class="text-center">Chargement...</td></tr>';
            
            manageAccessModal.show();

            fetch("<?= URLROOT ?>/ged/getShares/" + type + "/" + id)
                .then(response => response.json())
                .then(data => {
                    body.innerHTML = '';
                    // Jeton CSRF pour les formulaires générés dynamiquement
                    // (l'injection auto ne couvre que les formulaires présents au chargement).
                    const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
                    if (data.length === 0) {
                        body.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Aucun partage actif.</td></tr>';
                    } else {
                        data.forEach(share => {
                            let permLabel = share.permission === 'edit' ? 'Édition' : (share.permission === 'download' ? 'Téléchargement' : 'Lecture');
                            let permClass = share.permission === 'edit' ? 'bg-red-lt' : (share.permission === 'download' ? 'bg-green-lt' : 'bg-blue-lt');
                            
                            body.innerHTML += `
                                <tr>
                                    <td>${share.shared_with_name}</td>
                                    <td><span class="badge ${permClass}">${permLabel}</span></td>
                                    <td>
                                        <form method="POST" action="<?= URLROOT ?>/ged/revokeShare/${share.id}/${currentFolderId}" class="m-0" onsubmit="return confirm('Retirer ce partage ?');">
                                            <input type="hidden" name="csrf_token" value="${csrfToken}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                                        </form>
                                    </td>
                                </tr>`;
                        });
                    }
                });
        });
    });

    // Lien de dépôt client (Externe)
    document.querySelectorAll('.external-link-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            
            fetch("<?= URLROOT ?>/ged/generateExternalLink/" + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('external-url-input').value = data.url;
                        externalLinkModal.show();
                    } else {
                        alert('Erreur lors de la génération du lien.');
                    }
                });
        });
    });

    document.getElementById('copy-link-btn').onclick = function() {
        const input = document.getElementById('external-url-input');
        input.select();
        document.execCommand('copy');
        this.innerText = 'Copié !';
        setTimeout(() => this.innerText = 'Copier', 2000);
    };

    // --- Drag & Drop pour l'upload ---
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const fileListPreview = document.getElementById('file-list-preview');
    const btnSubmitUpload = document.getElementById('btn-submit-upload');

    if (dropZone) {
        // Au clic sur la zone, on ouvre le sélecteur de fichier
        dropZone.addEventListener('click', () => fileInput.click());

        // Empêcher le comportement par défaut du navigateur lors du drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        // Effets visuels
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        // Gestion du dépôt
        dropZone.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });

        // Gestion du changement via le sélecteur classique
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                // Pour que le formulaire envoie bien les fichiers déposés, 
                // on les assigne à l'input file (en utilisant DataTransfer pour la compatibilité)
                const dataTransfer = new DataTransfer();
                let previewContent = '<strong>Fichiers sélectionnés :</strong><ul class="mb-0">';
                
                for (let file of files) {
                    dataTransfer.items.add(file);
                    previewContent += `<li>${file.name} (${(file.size / 1024).toFixed(1)} KB)</li>`;
                }
                previewContent += '</ul>';

                fileInput.files = dataTransfer.files;
                fileListPreview.innerHTML = previewContent;
                btnSubmitUpload.disabled = false;
            } else {
                fileListPreview.innerHTML = '';
                btnSubmitUpload.disabled = true;
            }
        }
    }
});
</script>
