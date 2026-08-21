<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($data['title']) ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <style>
        .file-item:hover { background-color: rgba(32, 107, 196, 0.03); }
        .folder-item { cursor: pointer; }
    </style>
</head>
<body class="bg-light">
    <div class="page">
        <div class="container-xl py-4">
            <div class="text-center mb-4">
                <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC Logo" height="60" class="mb-2">
                <h1 class="h2">DWAC ENGINE - GED</h1>
            </div>

            <!-- Breadcrumbs & Search -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <?php foreach ($data['breadcrumbs'] as $index => $crumb): ?>
                                <li class="breadcrumb-item <?= ($index === count($data['breadcrumbs']) - 1) ? 'active' : '' ?>">
                                    <?php 
                                        $qParam = $data['search_term'] ? '&q=' . urlencode($data['search_term']) : '';
                                    ?>
                                    <?php if ($index === count($data['breadcrumbs']) - 1): ?>
                                        <?= e($crumb->name) ?>
                                    <?php else: ?>
                                        <a href="<?= URLROOT ?>/externalged/deposit/<?= e($data['link']->token) ?>/<?= (int) $crumb->id ?>?sort=<?= e($data['sort']) ?>&dir=<?= e($data['dir']) ?><?= $qParam ?>"><?= e($crumb->name) ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4">
                    <form action="" method="GET">
                        <!-- On conserve les paramètres de tri lors de la recherche -->
                        <input type="hidden" name="sort" value="<?= e($data['sort']) ?>">
                        <input type="hidden" name="dir" value="<?= e($data['dir']) ?>">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                            </span>
                            <input type="text" name="q" class="form-control" placeholder="Rechercher dans ce dossier..." value="<?= e($data['search_term'] ?? '') ?>">
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-cards">
                <!-- Zone d'affichage des fichiers -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                                <?= isset($data['current_folder']) ? e($data['current_folder']->name) : 'Dossier' ?>
                                <?php if ($data['search_term']): ?>
                                    <span class="text-muted small ms-2">(Résultats pour "<?= e($data['search_term']) ?>")</span>
                                <?php endif; ?>
                            </h3>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Trier par
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php 
                                        $qParam = $data['search_term'] ? '&q=' . urlencode($data['search_term']) : '';
                                        $baseUrl = URLROOT . '/externalged/deposit/' . $data['link']->token . '/' . $data['current_folder']->id;
                                    ?>
                                    <a class="dropdown-item <?= ($data['sort'] == 'name' && $data['dir'] == 'ASC') ? 'active' : '' ?>" href="<?= $baseUrl ?>?sort=name&dir=ASC<?= $qParam ?>">Nom (A-Z)</a>
                                    <a class="dropdown-item <?= ($data['sort'] == 'name' && $data['dir'] == 'DESC') ? 'active' : '' ?>" href="<?= $baseUrl ?>?sort=name&dir=DESC<?= $qParam ?>">Nom (Z-A)</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item <?= ($data['sort'] == 'created_at' && $data['dir'] == 'DESC') ? 'active' : '' ?>" href="<?= $baseUrl ?>?sort=created_at&dir=DESC<?= $qParam ?>">Plus récent</a>
                                    <a class="dropdown-item <?= ($data['sort'] == 'created_at' && $data['dir'] == 'ASC') ? 'active' : '' ?>" href="<?= $baseUrl ?>?sort=created_at&dir=ASC<?= $qParam ?>">Plus ancien</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            <!-- Dossiers -->
                            <?php if (isset($data['subfolders']) && is_array($data['subfolders'])): ?>
                                <?php foreach ($data['subfolders'] as $folder): ?>
                                    <a href="<?= URLROOT ?>/externalged/deposit/<?= e($data['link']->token) ?>/<?= (int) $folder->id ?>?sort=<?= e($data['sort']) ?>&dir=<?= e($data['dir']) ?><?= $qParam ?>" class="list-group-item list-group-item-action file-item">
                                        <div class="d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder text-yellow me-3" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium text-dark"><?= e($folder->name) ?></div>
                                                <div class="text-muted small">Dossier • <?= date('d/m/Y H:i', strtotime($folder->created_at)) ?></div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Fichiers -->
                            <?php if (isset($data['files']) && is_array($data['files'])): ?>
                                <?php foreach ($data['files'] as $file): ?>
                                    <div class="list-group-item file-item">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file text-blue" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                                            </div>
                                            <div class="col text-truncate">
                                                <div class="text-reset d-block"><?= e($file->name) ?></div>
                                                <div class="text-muted small mt-n1">
                                                    <?= round($file->size / 1024, 1) ?> KB • <?= date('d/m/Y H:i', strtotime($file->created_at)) ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary view-file-btn" 
                                                        data-url="<?= URLROOT ?>/externalged/viewFile/<?= e($data['link']->token) ?>/<?= (int) $file->id ?>"
                                                        data-name="<?= e($file->name) ?>"
                                                        data-ext="<?= e(strtolower($file->extension)) ?>">
                                                    Visualiser
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (empty($data['subfolders']) && empty($data['files'])): ?>
                                <div class="list-group-item text-center py-4 text-muted">
                                    Ce dossier est vide
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Zone d'upload -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Ajouter des documents</h3>
                            <p class="text-muted small">Vous pouvez glisser-déposer des fichiers dans la zone ci-dessous.</p>
                            
                            <div id="drop-zone" class="border-2 border-dashed rounded-3 p-4 text-center bg-white mb-3" style="cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><polyline points="9 15 12 12 15 15" /><line x1="12" y1="12" x2="12" y2="21" /></svg>
                                <div>Glissez vos fichiers</div>
                                <input type="file" id="file-input" multiple style="display: none;">
                            </div>

                            <div id="file-list" class="mb-3 small"></div>

                            <button id="upload-btn" class="btn btn-primary w-100" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><polyline points="7 9 12 4 17 9" /><line x1="12" y1="4" x2="12" y2="16" /></svg>
                                Importer les fichiers
                            </button>
                        </div>
                        <div class="card-footer text-center text-muted small">
                            Lien valide jusqu'au <?= isset($data['link']->expires_at) ? date('d/m/Y à H:i', strtotime($data['link']->expires_at)) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aperçu -->
    <div class="modal modal-blur fade" id="modal-preview" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="preview-title">Aperçu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="preview-container" style="height: 600px; display: flex; align-items: center; justify-content: center; background: #f1f5f9;">
                        <!-- Contenu dynamique -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('file-input');
            const fileList = document.getElementById('file-list');
            const uploadBtn = document.getElementById('upload-btn');
            let filesToUpload = [];

            // Modal Preview Logic
            const previewModal = new bootstrap.Modal(document.getElementById('modal-preview'));
            const previewContainer = document.getElementById('preview-container');
            const previewTitle = document.getElementById('preview-title');

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.onclick = function() {
                    const url = this.getAttribute('data-url');
                    const name = this.getAttribute('data-name');
                    const ext = this.getAttribute('data-ext');

                    previewTitle.innerText = name;
                    previewContainer.innerHTML = '';

                    if (ext === 'pdf') {
                        previewContainer.innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border: none;"></iframe>`;
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        previewContainer.innerHTML = `<img src="${url}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
                    } else {
                        previewContainer.innerHTML = `
                            <div class="text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                                <p>Aperçu non disponible pour ce type de fichier</p>
                                <a href="${url}" class="btn btn-primary" target="_blank">Ouvrir dans un nouvel onglet</a>
                            </div>`;
                    }
                    previewModal.show();
                };
            });

            if (dropZone) {
                dropZone.onclick = () => fileInput.click();

                fileInput.onchange = (e) => {
                    filesToUpload = Array.from(e.target.files);
                    updateList();
                };

                dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add('bg-blue-lt'); };
                dropZone.ondragleave = () => { dropZone.classList.remove('bg-blue-lt'); };
                dropZone.ondrop = (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('bg-blue-lt');
                    filesToUpload = Array.from(e.dataTransfer.files);
                    updateList();
                };
            }

            function updateList() {
                // Limite alignée sur le serveur (MAX_UPLOAD_MB) pour éviter de
                // laisser sélectionner des fichiers qui seront refusés côté serveur.
                const MAX_MB = <?= (int) (defined('MAX_UPLOAD_MB') ? MAX_UPLOAD_MB : 64) ?>;
                const MAX_FILE_SIZE = MAX_MB * 1024 * 1024;
                const MAX_TOTAL_SIZE = Math.round(MAX_MB * 1.1) * 1024 * 1024; // marge multi-fichiers

                let totalSize = 0;
                let hasError = false;
                let errorMsg = "";

                const listHtml = filesToUpload.map(f => {
                    totalSize += f.size;
                    let fileError = "";
                    if (f.size > MAX_FILE_SIZE) {
                        fileError = ' <span class="text-danger">(Trop volumineux : > ' + MAX_MB + ' Mo)</span>';
                        hasError = true;
                    }
                    return `<div class="p-1 border-bottom text-truncate">${f.name} <span class="text-muted">(${Math.round(f.size/1024)} KB)</span>${fileError}</div>`;
                }).join('');

                if (totalSize > MAX_TOTAL_SIZE) {
                    hasError = true;
                    errorMsg = `<div class="alert alert-danger mt-2 py-2 small">Taille totale excessive : ${Math.round(totalSize / (1024*1024))} Mo (max ${Math.round(MAX_TOTAL_SIZE/(1024*1024))} Mo).</div>`;
                }

                fileList.innerHTML = listHtml + errorMsg;
                uploadBtn.disabled = filesToUpload.length === 0 || hasError;
            }

            uploadBtn.onclick = () => {
                const formData = new FormData();
                filesToUpload.forEach(f => formData.append('files[]', f));
                
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Envoi...';

                // On utilise l'ID du dossier actuel pour l'upload
                fetch('<?= URLROOT ?>/externalged/upload?t=<?= $data['link']->token ?>&f=<?= $data['current_folder']->id ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        throw new Error('Réponse invalide du serveur.');
                    }
                })
                .then(data => {
                    if(data.success) {
                        alert('Fichiers importés avec succès !');
                        window.location.reload();
                    } else {
                        alert('Erreur: ' + (data.message || 'Inconnue'));
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = 'Réessayer';
                    }
                })
                .catch(err => {
                    alert('Erreur lors de l\'envoi: ' + err.message);
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = 'Réessayer';
                });
            };
        });
    </script>
</body>
</html>
