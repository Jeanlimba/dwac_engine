# Plan d'implémentation de la Gestion Électronique des Documents (GED) - SUIVI

## 1. Fonctionnalités Proposées

### Gestion des Dossiers & Fichiers
- [x] **Hiérarchie infinie** : Structure parent/enfant opérationnelle.
- [x] **Espace Personnel Auto** : Création automatique du dossier racine à la première connexion.
- [x] **Explorateur intuitif** : Navigation avec fil d'ariane (breadcrumbs) terminée.
- [x] **Importation de fichiers** : Upload multiple fonctionnel.
- [ ] **Actions de base** : 
    - [ ] Renommer les dossiers et fichiers.
    - [ ] Supprimer (avec confirmation).
    - [ ] Déplacer (changer de dossier parent).
- [ ] **Remplacement** : Mettre à jour un fichier en conservant ses métadonnées.

### Visualisation & Aperçu
- [x] **Lecteur PDF intégré** : Visualisation dans une modale Tabler.
- [x] **Aperçu Images** : Support JPG, PNG, WEBP, GIF.
- [x] **Gestion des types** : Icônes distinctes pour dossiers et fichiers.

### Partage & Collaboratif
- [ ] **Partage de dossiers** : Partage avec d'autres employés du tenant.
- [ ] **Permissions granulaires** : (Read, Download, Edit).
- [ ] **Héritage des droits** : Propagation automatique aux sous-éléments.

---

## 2. Plan d'implémentation Technique

### Étape 1 : Base de données (Terminée)
- [x] Table `ged_folders`
- [x] Table `ged_files`
- [x] Table `ged_shares`
- *Fichier source : `database/sql/create_ged_tables.sql`*

### Étape 2 : Modèles (En cours)
- [x] `GedFolder.php` : Arborescence et breadcrumbs.
- [x] `GedFile.php` : Upload et gestion des fichiers.
- [ ] `GedShare.php` : Gestion des droits d'accès.

### Étape 3 : Contrôleur & UI (En cours)
- [x] `Ged.php` : Index, folder, upload, createFolder.
- [x] Intégration Layout : Menu GED ajouté (Tenant + SuperAdmin).
- [x] Vue Explorateur : `app/Views/ged/index.php`.
- [ ] Modales de partage et renommage.

---

## 3. Historique des scripts SQL
- `database/sql/create_ged_tables.sql` : Création de la structure initiale.
