# Evolution – Gestion RH multi-tenant (PHP)

Application RH multi‑tenant pour gérer des tenants (entreprises) et leurs employés. Le **super‑admin** crée les tenants et leurs comptes admin. Chaque **tenant admin** gère les employés, départements, contrats, expériences, formations et congés. L’interface utilise **Tabler.io**.

## Fonctionnalités

### Super‑admin
- Authentification super‑admin.
- CRUD des tenants (nom, sigle, adresse, téléphone).
- Création automatique d’un compte admin tenant lors de la création d’un tenant.
- Accès au détail d’un tenant et liste de ses employés.

### Tenant admin
- Tableau de bord.
- Gestion des employés (création, modification, détails).
- Gestion des départements (hiérarchie, CRUD).
- Dossier employé avec onglets :
  - Informations personnelles
  - Contrats (type, dates, salaire, statut, fichier, commentaire)
  - Expériences
  - Formations
  - Congés
- Gestion des fichiers (photo, CV, pièces jointes).

## Stack
- **PHP** (PDO)
- **MySQL**
- **Tabler.io** (UI)

## Structure du projet

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) simplifiée :

```text
nom-projet/
├── app/                # Cœur de l'application
│   ├── Controllers/    # Logique de traitement des requêtes
│   ├── Core/           # Classes de base (Routage, Base Controller, DB)
│   ├── Models/         # Interaction avec la base de données
│   └── Views/          # Fichiers de rendu (HTML/PHP)
├── config/             # Configuration de l'application (DB, etc.)
├── database/sql/       # Scripts SQL de migration et initialisation
├── public/             # Point d'entrée public et ressources statiques
│   ├── assets/         # Images, CSS, JS
│   ├── uploads/        # Fichiers téléchargés par les utilisateurs
│   └── index.php       # Point d'entrée unique
├── scripts/            # Scripts utilitaires et de maintenance
├── src/                # Classes utilitaires supplémentaires (ex: DocxTemplate)
└── templates/          # Layouts globaux de l'application
```

### Détails des dossiers
- **app/Core** : Contient le moteur de l'application (`App.php` pour le routage, `Database.php` pour PDO).
- **app/Controllers** : Chaque fichier correspond à un module (ex: `Employees.php`, `Ged.php`).
- **app/Models** : Gère la logique métier et les requêtes SQL via PDO.
- **app/Views** : Organisé par module pour correspondre aux contrôleurs.
- **public/** : Seul ce dossier devrait être exposé sur le web. Il contient le `index.php` qui initialise l'application.
- **scripts/** : Utiles pour les migrations de données ou les corrections ponctuelles en ligne de commande.

## Installation (local)

1. Créez la base de données **evolution** et importez les scripts SQL dans l’ordre (le fichier est le fichier que je te transmet)
   

<!-- 2. Mettez à jour les identifiants MySQL dans [config/database.php](config/database.php).
3. Placez le dossier dans votre serveur web (XAMPP/WAMP/LAMP) ou utilisez le serveur intégré PHP.
4. Accédez à [index.php](index.php). -->

## Comptes par défaut

- **Super‑admin** :
  - username: `super_admin`
  - password: `password123`

- **Admin tenant** :
  - créé automatiquement lors de la création d’un tenant (username = `admin_{nom_du_tenant}`)
  - password: `password123`

> ⚠️ Les mots de passe sont stockés crypté À sécuriser pour la mise en production.

## Remarques importantes

- Les fichiers uploadés sont stockés dans [uploads/](uploads/). Assurez‑vous que le dossier est accessible en écriture.
- Le module **postes** est utilisé dans les contrats (sélection du poste). Vérifiez la présence de la table `postes` si vous l’ajoutez.
- Les relations multi‑tenant sont basées sur `tenant_id` avec suppression en cascade.

## Routes et Modules

L'application est découpée en modules gérés par des contrôleurs :

### Authentification
- `Auth/login` : Connexion des utilisateurs.

### Super-admin
- `Tenants/index` : Liste et gestion des entreprises (tenants).
- `Tenants/create` : Ajout d'une nouvelle entreprise.
- `Tenants/details` : Vue détaillée d'un tenant et de ses employés.

### Dashboard
- `Dashboard/index` : Tableau de bord principal (s'adapte au rôle de l'utilisateur).

### Gestion RH (Tenant Admin)
- `Employees/index` : Liste du personnel.
- `Employees/details` : Dossier complet d'un employé.
- `Departments/index` : Organigramme et gestion des services.
- `Timesheets/index` : Suivi du temps de travail.
- `Expenses/index` : Gestion des notes de frais.

### Gestion Électronique de Documents (GED)
- `Ged/index` : Explorateur de fichiers.
- `Externalged/deposit` : Espace de dépôt pour les partenaires externes.

## Sécurité (à prévoir)

- Hashage des mots de passe avec `password_hash()` / `password_verify()`.
- Validation/sanitation renforcée des entrées.
- Gestion fine des rôles et permissions.
- CSRF tokens sur les formulaires.

## Licence

Projet interne. À définir selon vos besoins.