# Plan de réécriture Django — décisions & exécution

> Complément opérationnel de l'ADR-0001 (décision : Django/Python sur VPS).
> Décisions prises et plan d'exécution du build.

## Décisions arrêtées

- **Base de données cible : MySQL (conservée).** Les données sont déjà en MySQL,
  le PoC Django fonctionne dessus (33 modèles, 0 souci) → **pas de migration de
  données, risque minimal**. On ne change que le framework. *PostgreSQL = option
  future éventuelle, pas maintenant.*
- **Hébergement : VPS** (le mutualisé ne convient pas à Django).
- **Approche : réécriture progressive**, l'app PHP reste en service jusqu'à parité
  par module. **Django admin** = back-office immédiat sur les données reprises.

## VPS — specs recommandées (démarrage)

- **2 vCPU / 4 Go RAM / 80 Go SSD** (confortable pour Django + MySQL + paie ;
  on peut démarrer plus petit mais 4 Go évite les soucis avec MySQL + Gunicorn).
- **OS** : Ubuntu LTS (22.04 ou 24.04).
- **Stack** : Nginx (reverse proxy) + Gunicorn (WSGI) + MySQL 8 + systemd + Certbot
  (HTTPS) + `ufw` (pare-feu) + **cron** (tâches planifiées : remplace les queues).
- **Sauvegardes automatisées** de la base (dump quotidien hors serveur) —
  **non négociable** (données RH/paie).
- Fournisseur : au choix (OVH, Hetzner, DigitalOcean, Contabo…).

## Architecture Django (apps par domaine)

Découpage en apps, mappées sur les modules actuels :

| App Django | Reprend | Notes |
|---|---|---|
| `accounts` | `users` + rôles | Auth Django + groupes/permissions (RBAC natif) |
| `tenants` | `tenants` | Multi-tenant (voir ci-dessous) |
| `org` | `departments`, `postes`, `provinces`, `nationalities` | Données de référence |
| `employees` | `employees`, `employee_contracts/experiences/trainings` | Dossier employé |
| `leave` | `leave_requests`, `leave_types` | + soldes/acquisition (upgrade fonctionnel) |
| `timesheets` | `timesheets` | + validation |
| `attendance` | `pointages` | + **API DRF** d'ingestion (remplace `/ingest` PHP) |
| `expenses` | `expenses`, `charges` | + workflow d'approbation |
| `missions` | `missions`, `mission_*`, `budget_*`, `partners`, `budgetary_units` | Module le plus complexe |
| `documents` | `ged_*` | Gestion de fichiers |
| `audit` | `action_logs` | Journal d'audit |
| `payroll` | *(nouveau)* | **En dernier**, avec expert paie + tests |

**Multi-tenant** : middleware qui fixe le tenant courant (session) + managers/
querysets filtrés par `tenant_id` (modèle de base + manager custom). L'admin :
super-admin voit tout, admin de tenant voit son périmètre.

## Ordre de portage (du plus simple à la paie)

1. **Socle** : projet + `inspectdb` + `accounts` + `tenants` + `org` + admin +
   multi-tenant + tests + CI. → back-office utilisable très vite.
2. **`employees`** (dossier employé — cœur).
3. **`attendance`** + ingestion **DRF** + collecteur Python (`pyzk`). *(bien cerné,
   autonome)*
4. **`leave`** (avec soldes — vraie montée fonctionnelle).
5. **`expenses`** (workflow).
6. **`timesheets`**.
7. **`missions`/budgets** (le plus lourd de l'existant).
8. **`documents`** (GED — fichiers).
9. **`payroll`** *(nouveau)* — bulletins, INSS/CNSS, IPR, PDF, exports ; **expert
   paie + tests** obligatoires.
10. **Analytics RH** + self-service consolidé.

*(À chaque étape : l'app PHP reste en ligne ; bascule module par module.)*

## Points techniques clés

- **Reprise de schéma** : `inspectdb` (déjà validé) → repartir des modèles générés,
  puis reprendre la main via les migrations Django (`managed=True` progressivement).
- **Présence** : collecteur local réécrit en Python (`pyzk`) → POST signé vers un
  endpoint **DRF** ; même principe qu'aujourd'hui (HTTPS, HMAC), pas de MySQL distant.
- **Tests** dès le socle (pytest/django) — préalable non négociable à la paie.
- **Sécurité** : reprendre les acquis (CSRF natif Django, permissions/policies,
  audit, en-têtes) — cf. `SECURITY.md`.

## Prochaines actions concrètes

1. [ ] Provisionner le VPS (specs ci-dessus) + accès SSH.
2. [ ] Installer la stack (Nginx, Gunicorn, MySQL 8, Certbot, ufw, cron).
3. [ ] Créer le projet Django (apps ci-dessus) sur MySQL, reprendre le schéma.
4. [ ] Mettre en place multi-tenant + admin + tests + CI.
5. [ ] Porter les modules dans l'ordre ci-dessus (paie en dernier).

> Voir aussi : `docs/adr/0001-framework-choice.md`, `ERP_ROADMAP.md`, `SECURITY.md`.
