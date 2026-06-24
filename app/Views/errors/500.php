<?php
/**
 * Page d'erreur 500 — AUTONOME (volontairement sans header/footer ni accès DB),
 * pour rester affichable même quand l'application est en panne.
 */
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur — <?= defined('SITENAME') ? htmlspecialchars(SITENAME) : 'DWAC Engine' ?></title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f6f7f9; color:#1a2533; margin:0; }
        .wrap { min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:24px; }
        .code { font-size:64px; font-weight:700; color:#206bc4; line-height:1; }
        h1 { font-size:20px; margin:16px 0 8px; }
        p { color:#69727d; max-width:420px; margin:0 0 24px; }
        a { display:inline-block; padding:8px 16px; background:#206bc4; color:#fff; border-radius:6px; text-decoration:none; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code">500</div>
        <h1>Une erreur interne est survenue</h1>
        <p>Un problème technique est survenu. L'incident a été enregistré. Veuillez réessayer dans un instant.</p>
        <a href="<?= defined('URLROOT') ? htmlspecialchars(URLROOT) : '/' ?>">Retour à l'accueil</a>
    </div>
</body>
</html>
