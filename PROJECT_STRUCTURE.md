# Architecture Technique du Projet - TRESORganisee2

Ce document décrit la structure et le fonctionnement de l'application pour faciliter son déploiement ou son intégration dans un autre projet.

## 1. Modèle d'Architecture (MVC Personnalisé)
L'application repose sur un système **Modèle-Vue-Contrôleur** (MVC) en PHP pur, structuré pour être léger et modulaire.

### Dossier Racine (`/`)
- `.env` : Configuration des variables d'environnement (Base de données, Version, Nom du site).
- `composer.json` : Gestion des dépendances PHP (ex: PhpSpreadsheet).
- `.htaccess` : Redirection de toutes les requêtes vers le dossier `public/`.

### Cœur du Système (`app/Core/`)
- `App.php` : Le routeur principal. Il analyse l'URL (format : `domaine.com/controleur/methode/paramètres`) et charge la classe correspondante.
- `Controller.php` : Classe parente de tous les contrôleurs. Elle fournit les méthodes `model()` pour instancier les modèles et `view()` pour charger les fichiers de vue.
- `Database.php` : Wrapper PDO pour une interaction sécurisée avec MySQL (requêtes préparées, binding de paramètres).

### Logique Métier (`app/Controllers/` & `app/Models/`)
- **Contrôleurs** : Gèrent la logique de navigation et les actions utilisateurs (ex: `Executions.php`, `Users.php`, `Controls.php`).
- **Modèles** : Représentent les tables de la base de données (ex: `Operation.php`, `BudgetLine.php`, `User.php`) et contiennent les requêtes SQL.

### Interface et Affichage (`app/Views/` & `include/`)
- **Views** : Fichiers PHP contenant le HTML, organisés par dossier de contrôleur (ex: `views/users/login.php`).
- **Includes** : Éléments d'interface réutilisables (`header.php`, `footer.php`, `sidebar.php`).

### Point d'Entrée Public (`public/`)
- `index.php` : Seul fichier accessible publiquement. Il initialise la configuration et lance l'application.
- `assets/` : Ressources statiques (CSS, JS, Images, PDF modèles).
- `uploads/` : Dossier de stockage des justificatifs et documents téléchargés.

## 2. Configuration & Déploiement
1. **Base de données** : Importer les scripts dans `sql/` (commencer par `database_file.sql` puis les `updates_vX.sql` dans l'ordre).
2. **Environnement** : Créer/Modifier le fichier `.env` avec les accès DB réels.
3. **Serveur Web** : Pointer le `DocumentRoot` vers le dossier `public/` ou s'assurer que le `.htaccess` à la racine est bien interprété (module `mod_rewrite` activé sur Apache).

## 3. Dépendances Principales
- **PhpSpreadsheet** : Génération et lecture de fichiers Excel (FACE form).
- **SessionHelper** : Gestion de l'authentification et des messages flash (notifications temporaires).
