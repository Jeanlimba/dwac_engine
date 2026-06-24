# Sécurité & feuille de route technique — DWAC Engine

Ce document récapitule le durcissement effectué, les **actions manuelles à
réaliser** (côté serveur / ops) et la suite de la professionnalisation.

## ✅ Mesures appliquées

### Phase 0 — Sécurité d'urgence
- **Upload / anti-RCE** : `public/uploads/.htaccess` désactive l'exécution de
  tout script (PHP/CGI…) et refuse l'accès aux extensions exécutables ;
  `validate_upload()` (liste blanche d'extensions, contrôle du MIME réel,
  nom physique aléatoire, taille max) appliqué aux uploads GED et au dépôt
  externe. Vérifié : un `.php` déposé renvoie **403** et n'est pas exécuté.
- **CSRF** : jeton par session, vérification globale dans le front controller
  (`App`) pour toute requête POST routée (sauf dépôt externe public), +
  `csrf_check_or_die()` sur les endpoints AJAX d'écriture. Côté client :
  meta + patch `fetch`/XHR + injection auto du champ caché
  (`templates/partials/csrf_js.php`). Échec → **403**.
- **Erreurs** : `display_errors` coupé hors environnement local (les erreurs
  sont journalisées). Détection d'environnement centralisée dans
  `is_local_host()` (gère le port) — évite de basculer sur la base de
  production par erreur.
- **Divers** : assainissement de l'en-tête `Content-Disposition`
  (anti-injection) ; `mkdir` en `0755` ; `.env.example` ajouté.

### Phase 1 — Fondations
- **BaseController** : helpers d'autorisation centralisés
  (`requireLogin`, `requireSuperAdmin`, `requireTenantAdmin`,
  `denySuperAdmin`, `requireRole`…) ; les 13 contrôleurs ne dupliquent plus
  la logique de garde.
- **Routeur durci** : seules les méthodes publiques **déclarées sur le
  contrôleur** sont appelables via l'URL (les helpers hérités et méthodes
  magiques ne le sont plus).
- **Nettoyage** : suppression des layouts legacy inutilisés.

### Phase 2 — Isolation tenant / IDOR
- **Timesheets** : `validate()`/`reject()` filtrent par `tenant_id` (un
  superviseur ne peut plus valider la feuille d'un autre tenant).
- **Supervisor** : `processExpense()` et `getExpenseDetail()` vérifient que la
  dépense appartient au tenant courant.
- **GED** : contrôle de propriété (`ownedFolderOrDeny`/`ownedFileOrDeny`) sur
  `rename/delete/move/copy/share/revokeShare/getShares`. Les actions GED sont
  désormais **réservées au propriétaire** de l'item (un utilisateur avec un
  simple partage ne peut plus modifier/supprimer l'item d'autrui — comportement
  voulu). Vérifié en runtime (dossier propre → 200, dossier d'autrui → 403).
- **Actions destructives en POST** : toutes les actions d'état/suppression
  passent désormais par des formulaires POST protégés CSRF (au lieu de liens
  GET) — GED `delete`/`revokeShare`, `Users::delete`/`toggle`,
  `Employees::delete`, `Tenants::delete`, `Expenses::delete`. Garde
  `requirePost()` côté contrôleur. Vérifié : GET → 403/405, POST sans jeton →
  403, POST avec jeton → action effective.

## ⚠️ Actions manuelles REQUISES (ops / hors code)

1. **Régénérer le mot de passe MySQL de production** (`ONLINE_DB_PASS` a été
   exposé en clair) puis le mettre à jour dans le `.env` du serveur.
2. **Régénérer l'identifiant SSH** qui figurait en clair dans le `.env`.
3. **Définir le docroot du site sur `public/`** (vhost Apache / cPanel) afin
   de ne pas exposer `.env`, `config/`, `scripts/`, `src/` si le rewrite venait
   à être désactivé.
4. **Confirmer `display_errors = Off`** dans le `php.ini` de production
   (défense en profondeur, en plus du code).
5. **Forcer le changement du mot de passe par défaut** au premier login
   (`DEFAULT_PASSWORD`).

## 🗺️ Reste à faire (professionnalisation, à planifier)

> Non urgent : l'application est fonctionnelle et sécurisée. Ces chantiers
> sont à mener de façon délibérée, par étapes et avec tests.

- **Composer + autoloading PSR-4** + namespaces (remplace les `require_once`
  manuels). Gros refactor (~30 fichiers) — prévoir une branche dédiée + tests.
- **`.env` robuste** via `vlucas/phpdotenv` (dépend de Composer).
- **`Notifications::markAllRead/markAsRead`** restent en GET mais sans enjeu
  (marquer ses propres notifications comme lues) — à convertir si on veut être
  exhaustif.
- **Logging** structuré (monolog) + page d'erreur générique en prod.
- **En-têtes de sécurité** : `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy` sont en place (dans `public/index.php`). Reste la **CSP** —
  bloquée par le JS inline (`onclick`, `<script>` inline) ; à faire après avoir
  externalisé/assaini ces scripts.
- **Pagination** sur les listes + audit des index SQL (`tenant_id`,
  `employee_id`, `username`).
- **Tests** (PHPUnit) sur l'auth, l'isolation tenant et la validation d'upload.

## 🌐 Déploiement en hébergement mutualisé

L'application est compatible mutualisé (PHP + MySQL, sans Composer ni build),
mais quelques points sont spécifiques à ce type d'hébergement.

### Compatible d'office
- Pile PHP/MySQL/PDO standard, **aucune dépendance à installer** (pas de
  `vendor/`).
- Routage par `.htaccess`/mod_rewrite (Apache et LiteSpeed le supportent).
- HTTPS derrière proxy géré (`X-Forwarded-Proto`), sessions fichier,
  `display_errors` coupé en prod.
- `mkdir` en `0755` (compatible suEXEC).

### Points à vérifier / risques propres au mutualisé
1. **Document root = `public/`** (le plus important). Sinon, si mod_rewrite est
   désactivé ou `AllowOverride` limité, `.env`/`config/`/`scripts/` peuvent
   devenir téléchargeables. Pointer le domaine sur le sous-dossier `public/`.
2. **`.htaccess` des uploads selon le SAPI** : en mod_php, `php_flag engine off`
   agit ; en PHP-FPM/CGI il est ignoré (protégé par `<IfModule mod_php.c>`, donc
   pas de 500) et la protection repose alors sur `<FilesMatch> Require all
   denied`. **À tester** : un `.php` déposé dans `public/uploads/` doit renvoyer
   **403** (ni exécution, ni 500 sur tout le dossier).
3. **PHP 8.x** avec les extensions **`pdo_mysql`** et **`fileinfo`** (la
   validation d'upload utilise `finfo`).
4. **`public/uploads/` inscriptible** par l'utilisateur web (le contenu est
   gitignoré : à créer/chmoder au déploiement).
5. **Hôte MySQL** : sur beaucoup de cPanel la base est en `localhost`. Un
   fallback automatique vers `localhost` existe si `ONLINE_DB_HOST` échoue, mais
   autant renseigner directement la bonne valeur dans le `.env`.

### Checklist de mise en production
1. Document root du domaine → `public/`.
2. PHP 8.x + extensions `fileinfo` / `pdo_mysql`.
3. `.env` de prod renseigné — et **secrets régénérés** (cf. actions requises).
4. `public/uploads/` créé et inscriptible.
5. Tests post-déploiement : connexion OK, upload légitime OK, `.php` dans
   `public/uploads/` → **403**, une suppression (utilisateur/dépense) via le
   bouton fonctionne (POST + CSRF).

## Note dev

`router.php` (racine) et `.claude/launch.json` sont des aides de **test local**
(serveur PHP intégré) ; ils sont ignorés par git et ne sont pas déployés.
