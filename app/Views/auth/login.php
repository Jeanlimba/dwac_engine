<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | <?= SITENAME ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column bg-white">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC Logo" height="80" class="avatar avatar-xl border mb-2"><br>

                <a href="." class="navbar-brand navbar-brand-autodark">
                   <h1 class="font-weight-bold">DWAC ENGINE</h1>
                </a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Connectez-vous à votre compte</h2>
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                    <?php endif; ?>
                    <form action="<?= URLROOT ?>/auth" method="post" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" placeholder="votre@email.com" autocomplete="off" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="Votre mot de passe" autocomplete="off" required>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
