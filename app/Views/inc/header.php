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
    <meta name="theme-color" content="#0f2a47">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <?php require_once APPROOT . '/../templates/partials/csrf_js.php'; ?>
    <title><?= $data['title'] ?? SITENAME ?> | <?= SITENAME ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/public/assets/app.css?v=<?= defined('APPVERSION') ? APPVERSION : '1' ?>">
    <script>
        // Applique le thème enregistré AVANT le rendu (évite le flash clair->sombre).
        (function () {
            var t = localStorage.getItem('dwac-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
</head>
<?php
/* Détection de l'onglet actif (1er segment de l'URL) pour surligner la sidebar. */
$__url = strtolower(trim($_GET['url'] ?? '', '/'));
$__seg = explode('/', $__url)[0] ?? '';
if (!function_exists('nav_active')) {
    function nav_active(string $seg, string $current): string {
        return $seg === $current ? ' active' : '';
    }
}
?>
<body class="d-flex flex-column">
    <div class="page">
        <!-- ==================== BARRE HORIZONTALE ==================== -->
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Afficher la navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark pe-0 pe-md-3 m-0">
                    <a href="<?= URLROOT ?>" class="text-decoration-none d-flex align-items-center">
                        <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC Logo" class="navbar-brand-image me-2" style="height: 34px;">
                        <span class="fw-bold">DWAC <span class="text-muted fw-light">ENGINE</span></span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <?php require APPROOT . '/Views/inc/user_nav.php'; ?>
                </div>
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="navbar-nav">
                        <li class="nav-item<?= nav_active('dashboard', $__seg) ?>">
                            <a class="nav-link" href="<?= URLROOT ?>/dashboard">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg></span>
                                <span class="nav-link-title">Tableau de bord</span>
                            </a>
                        </li>

                        <?php if ($is_employee): ?>
                        <li class="nav-item<?= nav_active('timesheets', $__seg) ?>">
                            <a class="nav-link" href="<?= URLROOT ?>/timesheets">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg></span>
                                <span class="nav-link-title">Timesheet</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['is_super_admin']): ?>
                            <li class="nav-item<?= nav_active('tenants', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/tenants">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg></span>
                                    <span class="nav-link-title">Entreprises</span>
                                </a>
                            </li>
                            <li class="nav-item<?= nav_active('users', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/users">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg></span>
                                    <span class="nav-link-title">Utilisateurs</span>
                                </a>
                            </li>
                            <li class="nav-item<?= nav_active('auditlog', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/auditlog">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 9l1 0" /><path d="M9 13l6 0" /><path d="M9 17l6 0" /></svg></span>
                                    <span class="nav-link-title">Audit</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <?php if (false): // Menu « Mes Dépenses » (déclaration) masqué à la demande ?>
                            <li class="nav-item<?= nav_active('expenses', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/expenses">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h6l5 5v13a2 2 0 0 1 -2 2h-9a2 2 0 0 1 -2 -2z" /><path d="M9 7l4 0" /><path d="M9 11l6 0" /><path d="M9 15l4 0" /></svg></span>
                                    <span class="nav-link-title">Mes Dépenses</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager')): ?>
                            <li class="nav-item dropdown<?= nav_active('supervisor', $__seg) ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-supervisor" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4c.96 0 1.84 .338 2.53 .901" /><path d="M15 19l2 2l4 -4" /></svg></span>
                                    <span class="nav-link-title">Superviseur</span>
                                </a>
                                <div class="dropdown-menu">
                                    <?php if (false): // Menu « Dépenses » (validation) masqué à la demande ?>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/expenses">Dépenses</a>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/missions">Missions</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/supervisor/missionOrders">Ordres de Mission</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/timesheets/pending">Validations Timesheet</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/timesheets/reports">Rapports Performance</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/employees">Employés</a>
                                </div>
                            </li>
                            <?php endif; ?>

                            <?php if (!$is_employee): ?>
                            <li class="nav-item dropdown<?= nav_active('settings', $__seg) ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><circle cx="12" cy="12" r="3" /></svg></span>
                                    <span class="nav-link-title">Configuration</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?= URLROOT ?>/settings/charges">Gestion des Charges</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/users">Utilisateurs</a>
                                    <a class="dropdown-item" href="<?= URLROOT ?>/auditlog">Journal d'audit</a>
                                </div>
                            </li>
                            <li class="nav-item<?= nav_active('employees', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/employees">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg></span>
                                    <span class="nav-link-title">Employés</span>
                                </a>
                            </li>
                            <li class="nav-item<?= nav_active('departments', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/departments">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="15" y="15" width="6" height="6" rx="1" /><rect x="3" y="15" width="6" height="6" rx="1" /><rect x="9" y="3" width="6" height="6" rx="1" /><path d="M6 15v-1a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v1" /><path d="M12 9l0 3" /></svg></span>
                                    <span class="nav-link-title">Affectations</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (!$is_employee || (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['superviseur', 'manager']))): ?>
                            <li class="nav-item<?= nav_active('attendance', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/attendance">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M15 19l2 2l4 -4" /></svg></span>
                                    <span class="nav-link-title">Présence</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item<?= nav_active('ged', $__seg) ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/ged">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg></span>
                                    <span class="nav-link-title">GED</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </header>
        <div class="page-wrapper">
