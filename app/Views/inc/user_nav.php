<?php
/**
 * Cluster de navigation utilisateur : bascule thème + notifications + menu compte.
 * Réutilisé à deux endroits (header desktop et barre mobile de la sidebar) pour
 * éviter la duplication. Variables attendues dans le scope appelant :
 *   $data, $is_employee, $user_photo, $user_display_name.
 */
?>
<!-- Bascule thème clair / sombre -->
<div class="nav-item d-flex align-items-center me-2">
    <button type="button" class="theme-toggle nav-link px-0" onclick="toggleTheme()" aria-label="Basculer le thème clair / sombre" title="Basculer le thème">
        <svg class="icon icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
        <svg class="icon icon-sun" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="4" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
    </button>
</div>
<!-- Notifications -->
<div class="nav-item dropdown d-flex me-3">
    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Afficher les notifications">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
        <?php if(!empty($data['notifications'])): ?>
            <span class="badge bg-red"><?= count($data['notifications']) ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="min-width: 300px;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Notifications</h3>
                <?php if(!empty($data['notifications'])): ?>
                    <a href="<?= URLROOT ?>/notifications/markAllRead" class="small">Tout marquer lu</a>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush list-group-hoverable">
                <?php if(!empty($data['notifications'])): ?>
                    <?php foreach($data['notifications'] as $notif): ?>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto"><span class="status-dot status-dot-animated bg-<?= e($notif->type) ?> d-block"></span></div>
                                <div class="col text-truncate">
                                    <a href="<?= e(URLROOT . '/' . ($notif->link ?? '#')) ?>" class="text-body d-block"><?= e($notif->title) ?></a>
                                    <div class="d-block text-muted text-truncate mt-n1">
                                        <?= htmlspecialchars($notif->message) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center text-muted py-3">
                        Aucune nouvelle notification
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Menu compte -->
<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Ouvrir le menu utilisateur">
        <?php if ($is_employee && !empty($user_photo)): ?>
            <span class="avatar avatar-sm rounded-circle" style="background-image: url(<?= e(URLROOT . '/' . $user_photo) ?>)"></span>
        <?php else: ?>
            <span class="avatar avatar-sm bg-blue-lt"><?= substr($user_display_name, 0, 1) ?></span>
        <?php endif; ?>
        <div class="d-none d-xl-block ps-2">
            <div><?= htmlspecialchars($user_display_name) ?></div>
            <div class="mt-1 small text-muted"><?= $is_employee ? 'Employé' : 'Gestionnaire' ?></div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <?php if ($is_employee): ?>
            <a href="<?= URLROOT ?>/employees/details/<?= $_SESSION['employee_id'] ?>" class="dropdown-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                Mon profil
            </a>
            <div class="dropdown-divider"></div>
        <?php endif; ?>
        <a href="<?= URLROOT ?>/auth/logout" class="dropdown-item text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M7 12h14l-3 -3m0 6l3 -3" /></svg>
            Déconnexion
        </a>
    </div>
</div>
