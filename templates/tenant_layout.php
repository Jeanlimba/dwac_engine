<?php
// Génération d'un jeton CSRF pour sécuriser les formulaires
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_employee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
$user_display_name = $is_employee ? 'Employé' : 'Admin Tenant';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? $user_display_name ?> | <?= SITENAME ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
        .nav-link.active { font-weight: 600; color: #206bc4 !important; }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="page">
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="/" class="text-decoration-none d-flex align-items-center">
                        <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC Logo" class="navbar-brand-image me-2" style="height: 40px;">
                        DWAC <span class="text-muted fw-light ms-1">ENGINE</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-blue-lt"><?= substr($user_display_name, 0, 1) ?></span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?= $user_display_name ?></div>
                                <div class="mt-1 small text-muted">Connecté</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <?php if ($is_employee): ?>
                                <a href="tenant_employee_details?id=<?= $_SESSION['employee_id'] ?>" class="dropdown-item">Mon Profil</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= URLROOT ?>/auth/logout" class="dropdown-item text-danger">Déconnexion</a>
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
                            <li class="nav-item <?= ($title === 'Tableau de bord') ? 'active' : '' ?>">
                                <a class="nav-link" href="tenant_dashboard">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="13" r="2" /><line x1="13.45" y1="11.55" x2="15.5" y2="9.5" /><path d="M6.4 20a9 9 0 1 1 11.2 0z" /></svg>
                                    </span>
                                    <span class="nav-link-title">Tableau de bord</span>
                                </a>
                            </li>
                            <?php if (!$is_employee): ?>
                            <li class="nav-item <?= ($title === 'Gestion des Employés' || $title === 'Employés') ? 'active' : '' ?>">
                                <a class="nav-link" href="tenant_employees">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                    </span>
                                    <span class="nav-link-title">Employés</span>
                                </a>
                            </li>
                            <li class="nav-item <?= ($title === 'Départements') ? 'active' : '' ?>">
                                <a class="nav-link" href="tenant_departments">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-hierarchy-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 3h4v4h-4z" /><path d="M3 17h4v4h-4z" /><path d="M17 17h4v4h-4z" /><path d="M7 17l5 -4l5 4" /><line x1="12" y1="7" x2="12" y2="13" /></svg>
                                    </span>
                                    <span class="nav-link-title">Départements</span>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item <?= ($title === 'Mon Profil') ? 'active' : '' ?>">
                                <a class="nav-link" href="tenant_employee_details?id=<?= $_SESSION['employee_id'] ?>">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><circle cx="12" cy="10" r="3" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                    </span>
                                    <span class="nav-link-title">Mon Profil</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item <?= ($title === 'GED') ? 'active' : '' ?>">
                                <a class="nav-link" href="<?= URLROOT ?>/ged">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                                    </span>
                                    <span class="nav-link-title">GED</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-wrapper">
            <?= $content ?? '' ?>
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    Copyright &copy; <?= date('Y') ?>
                                    <a href="." class="link-secondary"><?= SITENAME ?></a>.
                                    Tous droits réservés.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script>
    // Logic for Toast and Modals (already implemented but kept for reference)
    function showToast(title, message, bgClass) {
        const toastContainer = document.querySelector('.toast-container');
        const toastHTML = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="toast-header">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fermer"></button>
            </div>
            <div class="toast-body ${bgClass} text-white">
                ${message}
            </div>
        </div>`;
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const newToast = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(newToast);
        toast.show();
    }
    // ... REST OF JS ...
    </script>
</body>
</html>
