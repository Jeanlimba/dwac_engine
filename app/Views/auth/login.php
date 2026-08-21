<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f2a47">
    <title>Connexion | <?= SITENAME ?></title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/assets/dwac.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/public/assets/app.css?v=<?= defined('APPVERSION') ? APPVERSION : '1' ?>">
</head>
<body class="auth-body">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-card__head">
                <span class="auth-brandmark">
                    <img src="<?= URLROOT ?>/public/assets/dwac.png" alt="DWAC">
                </span>
                <div class="eyebrow">Espace sécurisé</div>
                <h1>DWAC ENGINE</h1>
                <p>Gestion RH &amp; Exécution</p>
            </div>

            <div class="auth-card__body">
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="alert alert-danger" role="alert"><?= e($_SESSION['login_error']); unset($_SESSION['login_error']); ?></div>
                <?php endif; ?>

                <form action="<?= URLROOT ?>/auth" method="post" autocomplete="off" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nom d'utilisateur</label>
                        <input type="text" name="username" class="form-control" placeholder="votre@email.com" autocomplete="off" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group input-group-flat">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Votre mot de passe" autocomplete="off" required>
                            <span class="input-group-text">
                                <a href="#" class="link-secondary" onclick="togglePassword(event)" title="Afficher / masquer le mot de passe" aria-label="Afficher / masquer le mot de passe">
                                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="2" /><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /></svg>
                                </a>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>

            <div class="auth-card__foot">
                &copy; <?= date('Y') ?> <?= SITENAME ?> &middot; Connexion sécurisée
            </div>
        </div>
    </div>

    <script>
        function togglePassword(e) {
            e.preventDefault();
            var input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
