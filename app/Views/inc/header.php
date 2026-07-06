<?php
$is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
$user_display_name = $_SESSION['username'] ?? 'Utilisateur';

if ($is_employee && !empty($_SESSION['user_firstname']) && !empty($_SESSION['user_lastname'])) {
    $user_display_name = $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'];
}

$user_photo = $_SESSION['user_photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <?php require_once APPROOT . '/../templates/partials/csrf_js.php'; ?>
    <title><?= $data['title'] ?? SITENAME ?> | <?= SITENAME ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column">
    <div class="page">
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="<?= URLROOT ?>" class="text-decoration-none d-flex align-items-center">
                         <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC Logo" class="navbar-brand-image me-2" style="height: 40px;">
                         DWAC <span class="text-muted fw-light ms-1">ENGINE</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <!-- Notifications -->
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
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
                                                    <div class="col-auto"><span class="status-dot status-dot-animated bg-<?= $notif->type ?> d-block"></span></div>
                                                    <div class="col text-truncate">
                                                        <a href="<?= URLROOT . '/' . ($notif->link ?? '#') ?>" class="text-body d-block"><?= htmlspecialchars($notif->title) ?></a>
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

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" id="userDropdown" data-bs-toggle="dropdown" aria-label="Open user menu">
                            <?php if ($is_employee && !empty($user_photo)): ?>
                                <span class="avatar avatar-sm rounded-circle" style="background-image: url(<?= URLROOT . '/' . $user_photo ?>)"></span>
                            <?php else: ?>
                                <span class="avatar avatar-sm bg-blue-lt"><?= substr($user_display_name, 0, 1) ?></span>
                            <?php endif; ?>
                            <div class="d-none d-xl-block ps-2">
                                <div><?= htmlspecialchars($user_display_name) ?></div>
                                <div class="mt-1 small text-muted"><?= $is_employee ? 'Employé' : 'Gestionnaire' ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" aria-labelledby="userDropdown">
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
                </div>
            </div>
        </header>
        <div class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLROOT ?>/dashboard">Tableau de bord</a>
                            </li>
                            
                            <?php if ($is_employee): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLROOT ?>/timesheets">Timesheet</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['is_super_admin']): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/tenants">Entreprises</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/users">Utilisateurs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/auditlog">Audit</a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/expenses">Mes Dépenses</a>
                                </li>
                                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager')): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#navbar-supervisor" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-title">Superviseur</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/expenses">Dépenses</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/missions">Missions</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/missionOrders">Ordres de Mission</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/partners">Partenaires</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/timesheets/pending">Validations Timesheet</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/timesheets/reports">Rapports Performance</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/employees">Employés</a>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                                <?php if (!$is_employee): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-title">Configuration</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?= URLROOT ?>/settings/charges">Gestion des Charges</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/users">Utilisateurs</a>
                                        <a class="dropdown-item" href="<?= URLROOT ?>/auditlog">Journal d'audit</a>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/employees">Employés</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/departments">Affectations</a>
                                </li>
                                <?php endif; ?>
                                <?php if (!$is_employee || (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['superviseur', 'manager']))): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/attendance">Présence</a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= URLROOT ?>/ged">GED</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-wrapper">
