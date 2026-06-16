<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .ged-item-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 1px solid rgba(101, 109, 119, 0.16);
        background: #fff;
        border-radius: 8px;
    }
    .ged-item-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #206bc4;
    }
    .ged-item-icon {
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 8px 8px 0 0;
    }
    .ged-item-name {
        padding: 8px 10px 2px 10px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
        font-size: 0.85rem;
    }
    .ged-item-info {
        padding: 0 10px 10px 10px;
        text-align: center;
        font-size: 0.7rem;
        color: #656d77;
    }
    .ged-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 10;
    }
    .dropzone-custom {
        border: 2px dashed #dce1e7;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .dropzone-custom:hover, .dropzone-custom.dragover {
        border-color: #206bc4;
        background: #f1f5f9;
    }
    .dropzone-custom .icon {
        width: 48px;
        height: 48px;
        color: #656d77;
        margin-bottom: 10px;
    }
</style>

<?php
function formatSize($bytes) {
    if (!$bytes) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Gestion Électronique des Documents (GED)
                </h2>
                <div class="text-muted mt-1">Gérez vos fichiers et dossiers en toute sécurité.</div>
            </div>
            <!-- Recherche -->
            <div class="col-md-3">
                <form action="<?= URLROOT ?>/ged/search" method="GET">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                        </span>
                        <input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= $data['search_term'] ?? '' ?>">
                    </div>
                </form>
            </div>
            <!-- Tri -->
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 9l4 -4l4 4m-4 -4v14" /><path d="M21 15l-4 4l-4 -4m4 4v-14" /></svg>
                        Trier par
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item <?= ($data['sort'] == 'name' && $data['dir'] == 'ASC') ? 'active' : '' ?>" href="?sort=name&dir=ASC">Nom (A-Z)</a>
                        <a class="dropdown-item <?= ($data['sort'] == 'name' && $data['dir'] == 'DESC') ? 'active' : '' ?>" href="?sort=name&dir=DESC">Nom (Z-A)</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item <?= ($data['sort'] == 'created_at' && $data['dir'] == 'DESC') ? 'active' : '' ?>" href="?sort=created_at&dir=DESC">Plus récent</a>
                        <a class="dropdown-item <?= ($data['sort'] == 'created_at' && $data['dir'] == 'ASC') ? 'active' : '' ?>" href="?sort=created_at&dir=ASC">Plus ancien</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item <?= ($data['sort'] == 'size' && $data['dir'] == 'DESC') ? 'active' : '' ?>" href="?sort=size&dir=DESC">Taille (Max)</a>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <?php if (isset($data['is_search'])): ?>
                        <a href="<?= URLROOT ?>/ged" class="btn btn-outline-secondary">
                            Retour à mon espace
                        </a>
                    <?php else: ?>
                        <!-- <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-folder">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /><line x1="12" y1="10" x2="12" y2="16" /><line x1="9" y1="13" x2="15" y2="13" /></svg>
                            Nouveau dossier
                        </button>
                        <button class="btn btn-success d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><polyline points="9 15 12 12 15 15" /><line x1="12" y1="12" x2="12" y2="21" /></svg>
                            Importer
                        </button> -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Breadcrumbs -->
        <ol class="breadcrumb mb-3" aria-label="breadcrumbs">
            <?php foreach ($data['breadcrumbs'] as $index => $crumb): ?>
                <li class="breadcrumb-item <?= ($index === count($data['breadcrumbs']) - 1) ? 'active' : '' ?>">
                    <a href="<?= URLROOT ?>/ged/folder/<?= $crumb->id ?>"><?= $crumb->name ?></a>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="row row-cards">
            <?php if (empty($data['subfolders']) && empty($data['files'])): ?>
                <div class="col-12">
                    <div class="empty py-5 border-2 border-dashed">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="9" y1="10" x2="9.01" y2="10" /><line x1="15" y1="10" x2="15.01" y2="10" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg>
                        </div>
                        <p class="empty-title">Dossier vide</p>
                        <p class="empty-subtitle text-muted">Utilisez les boutons en haut à droite pour ajouter du contenu.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dossiers -->
            <?php foreach ($data['subfolders'] as $folder): ?>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                    <div class="card ged-item-card h-100" onclick="window.location.href='<?= URLROOT ?>/ged/folder/<?= $folder->id ?>'">
                        <?php if ($folder->share_count > 0): ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge badge-pill bg-blue" title="Partagé avec <?= $folder->share_count ?> personne(s)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($folder->is_mission > 0): ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge badge-pill bg-purple" title="Dossier de mission (Lecture seule)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-briefcase" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><line x1="12" y1="12" x2="12" y2="12.01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="ged-actions dropdown">
                            <a href="#" class="btn btn-sm btn-white dropdown-toggle no-caret" data-bs-toggle="dropdown" onclick="event.stopPropagation();">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dots-vertical" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" /><circle cx="12" cy="5" r="1" /></svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <?php if ($folder->is_mission == 0): ?>
                                    <a class="dropdown-item rename-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" data-type="folder" onclick="event.stopPropagation();">Renommer</a>
                                    <a class="dropdown-item move-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" data-type="folder" onclick="event.stopPropagation();">Déplacer</a>
                                    <a class="dropdown-item copy-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" data-type="folder" onclick="event.stopPropagation();">Copier</a>
                                    <a class="dropdown-item share-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" data-type="folder" onclick="event.stopPropagation();">Partager en interne</a>
                                    <a class="dropdown-item external-link-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" onclick="event.stopPropagation();">Lien de dépôt client</a>
                                    <?php if ($folder->share_count > 0): ?>
                                        <a class="dropdown-item manage-access-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" data-type="folder" onclick="event.stopPropagation();">Gérer les accès</a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="<?= URLROOT ?>/ged/delete" class="m-0" onsubmit="event.stopPropagation(); return confirm('Supprimer ce dossier et tout son contenu ?');">
                                        <input type="hidden" name="type" value="folder">
                                        <input type="hidden" name="id" value="<?= $folder->id ?>">
                                        <input type="hidden" name="current_folder_id" value="<?= $data['current_folder']->id ?>">
                                        <button type="submit" class="dropdown-item text-danger">Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <a class="dropdown-item external-link-item" href="#" data-id="<?= $folder->id ?>" data-name="<?= $folder->name ?>" onclick="event.stopPropagation();">Lien de partage (Dépôt)</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ged-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon <?= $folder->is_mission > 0 ? 'text-purple' : 'text-yellow' ?> icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="currentColor" fill-opacity="0.2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                        </div>
                        <div class="ged-item-name" title="<?= $folder->name ?>">
                            <?= $folder->name ?>
                            <?php if (isset($folder->is_shared_received)): ?>
                                <br><small class="text-blue">De: <?= $folder->shared_by_name ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="ged-item-info">
                            <span class="text-muted small" title="Date d'arrivée">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y="15" width="2" height="2" /></svg>
                                <?= date('d/m/y H:i', strtotime($folder->created_at)) ?>
                            </span>
                            <br>
                            <?= $folder->file_count ?? 0 ?> fichiers • <?= formatSize($folder->total_size ?? 0) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Fichiers -->
            <?php foreach ($data['files'] as $file): ?>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                    <div class="card ged-item-card view-file h-100" 
                         data-id="<?= $file->id ?>" 
                         data-name="<?= $file->name ?>" 
                         data-type="<?= $file->extension ?>"
                         data-physical="<?= $file->physical_name ?>">
                        <div class="ged-actions dropdown">
                            <a href="#" class="btn btn-sm btn-white dropdown-toggle no-caret" data-bs-toggle="dropdown" onclick="event.stopPropagation();">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dots-vertical" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" /><circle cx="12" cy="5" r="1" /></svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="<?= URLROOT ?>/public/uploads/ged/<?= $file->physical_name ?>" target="_blank" onclick="event.stopPropagation();">Télécharger</a>
                                <?php if (($file->is_mission ?? 0) == 0): ?>
                                    <a class="dropdown-item rename-item" href="#" data-id="<?= $file->id ?>" data-name="<?= $file->name ?>" data-type="file" onclick="event.stopPropagation();">Renommer</a>
                                    <a class="dropdown-item move-item" href="#" data-id="<?= $file->id ?>" data-name="<?= $file->name ?>" data-type="file" onclick="event.stopPropagation();">Déplacer</a>
                                    <a class="dropdown-item copy-item" href="#" data-id="<?= $file->id ?>" data-name="<?= $file->name ?>" data-type="file" onclick="event.stopPropagation();">Copier</a>
                                    <a class="dropdown-item share-item" href="#" data-id="<?= $file->id ?>" data-name="<?= $file->name ?>" data-type="file" onclick="event.stopPropagation();">Partager</a>
                                    <a class="dropdown-item" href="#" onclick="event.stopPropagation();">Remplacer</a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="<?= URLROOT ?>/ged/delete" class="m-0" onsubmit="event.stopPropagation(); return confirm('Supprimer ce fichier ?');">
                                        <input type="hidden" name="type" value="file">
                                        <input type="hidden" name="id" value="<?= $file->id ?>">
                                        <input type="hidden" name="current_folder_id" value="<?= $data['current_folder']->id ?? 0 ?>">
                                        <button type="submit" class="dropdown-item text-danger">Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <a class="dropdown-item disabled" href="#">Fichier de mission protégé</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ged-item-icon">
                            <?php 
                            $ext = strtolower($file->extension);
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                <img src="<?= URLROOT ?>/public/uploads/ged/<?= $file->physical_name ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px 8px 0 0;">
                            <?php elseif ($ext === 'pdf'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-red icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="9" y1="9" x2="10" y2="9" /><line x1="9" y1="13" x2="15" y2="13" /><line x1="9" y1="17" x2="15" y2="17" /></svg>
                            <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-blue icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 18h9v-12l-5 2v5l-4 2v3" /><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            <?php elseif (in_array($ext, ['xls', 'xlsx', 'csv'])): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-green icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            <?php endif; ?>
                        </div>
                        <div class="ged-item-name" title="<?= $file->name ?>">
                            <?= $file->name ?>
                        </div>
                        <div class="ged-item-info">
                            <span class="text-muted small" title="Date d'arrivée">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y="15" width="2" height="2" /></svg>
                                <?= date('d/m/y H:i', strtotime($file->created_at)) ?>
                            </span>
                            <br>
                            <?= strtoupper($file->extension) ?> • <?= formatSize($file->size) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Boutons d'ajout (toujours à la fin) -->
            <?php if (!isset($data['is_search'])): ?>
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="card ged-item-card h-100 border-2 border-dashed d-flex align-items-center justify-content-center bg-transparent" 
                     data-bs-toggle="modal" data-bs-target="#modal-folder" style="min-height: 180px;">
                    <div class="text-center text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        <div class="font-weight-bold">Nouveau dossier</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="card ged-item-card h-100 border-2 border-dashed d-flex align-items-center justify-content-center bg-transparent" 
                     data-bs-toggle="modal" data-bs-target="#modal-upload" style="min-height: 180px;">
                    <div class="text-center text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><polyline points="9 15 12 12 15 15" /><line x1="12" y1="12" x2="12" y2="21" /></svg>
                        <div class="font-weight-bold">Importer fichiers</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nouveau Dossier -->
<div class="modal modal-blur fade" id="modal-folder" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/ged/createFolder" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="parent_id" value="<?= $data['current_folder']->id ?>">
                    <div class="mb-3">
                        <label class="form-label">Nom du dossier</label>
                        <input type="text" class="form-control" name="name" placeholder="Ex: Contrats, Factures..." required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le dossier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal modal-blur fade" id="modal-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/ged/upload" method="POST" enctype="multipart/form-data" id="upload-form">
                <div class="modal-header">
                    <h5 class="modal-title">Importer des fichiers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= csrf_field() // Jeton anti-CSRF requis par Ged::upload() ?>
                    <input type="hidden" name="parent_id" value="<?= $data['current_folder']->id ?>">

                    <div class="dropzone-custom" id="drop-zone">
                        <div class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><polyline points="9 15 12 12 15 15" /><line x1="12" y1="12" x2="12" y2="21" /></svg>
                        </div>
                        <h3>Glissez-déposez vos fichiers ici</h3>
                        <p class="text-muted">ou cliquez pour sélectionner manuellement</p>
                        <input type="file" name="files[]" id="file-input" multiple class="d-none">
                        <div id="file-list-preview" class="mt-3 text-start small text-blue"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="btn-submit-upload" disabled>Importer</button>
                </div>
            </form>
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

<!-- Modal Renommer -->
<div class="modal modal-blur fade" id="modal-rename" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/ged/rename" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Renommer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="rename-id">
                    <input type="hidden" name="type" id="rename-type">
                    <input type="hidden" name="current_folder_id" value="<?= $data['current_folder']->id ?>">
                    <div class="mb-3">
                        <label class="form-label">Nouveau nom</label>
                        <input type="text" class="form-control" name="name" id="rename-name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Partager -->
<div class="modal modal-blur fade" id="modal-share" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/ged/share" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="share-title">Partager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="share-item-id">
                    <input type="hidden" name="type" id="share-type">
                    <input type="hidden" name="current_folder_id" value="<?= $data['current_folder']->id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Partager avec...</label>
                        <select class="form-select" name="user_ids[]" multiple size="5" required>
                            <?php foreach ($data['tenant_users'] as $user): ?>
                                <option value="<?= $user->id ?>"><?= $user->username ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs personnes.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permission</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="permission" value="read" class="form-selectgroup-input" checked>
                                <span class="form-selectgroup-label">Lecture</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="permission" value="download" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">Téléchargement</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="permission" value="edit" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">Édition</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Partager</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gérer les accès (Log) -->
<div class="modal modal-blur fade" id="modal-manage-access" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manage-access-title">Gérer les accès</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Permission</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody id="access-list-body">
                            <!-- Rempli en AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lien Externe -->
<div class="modal modal-blur fade" id="modal-external-link" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lien de dépôt client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Copiez ce lien et envoyez-le à votre client pour qu'il puisse déposer des documents :</p>
                <div class="input-group">
                    <input type="text" id="external-url-input" class="form-control" readonly>
                    <button class="btn btn-outline-primary" type="button" id="copy-link-btn">Copier</button>
                </div>
                <small class="text-muted mt-2 d-block">Ce lien est sécurisé et expirera automatiquement dans 7 jours.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Destination (Copier/Déplacer) -->
<div class="modal modal-blur fade" id="modal-destination" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-destination" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="destination-title">Choisir la destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="destination-item-id">
                    <input type="hidden" name="type" id="destination-type">
                    <input type="hidden" name="current_folder_id" value="<?= $data['current_folder']->id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Dossier de destination</label>
                        <select class="form-select" name="destination_id" id="destination-select" required>
                            <!-- Rempli en AJAX -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btn-destination-submit">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/ged/_scripts.php'; ?>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
