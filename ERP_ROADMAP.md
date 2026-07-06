# De « evolution » à un véritable ERP RH — Analyse & feuille de route

> Analyse de l'état actuel et du chemin pour transformer l'application en un
> SIRH / ERP RH complet. Périmètre : francophone, contexte RDC probable
> (IPR, INSS/CNSS), multi-tenant, hébergé en mutualisé + collecteur de présence local.

---

## 1. Résumé exécutif

L'application **n'est pas un point de départ vierge** : elle couvre déjà, sur
~30 tables, une bonne partie du cœur d'un SIRH — dossier employé, contrats,
congés, départements/postes, missions & ordres de mission avec budgets, notes
de frais avec circuit de validation, feuilles de temps, présence biométrique,
GED, notifications. La sécurité a été durcie (RCE, CSRF, contrôle d'accès,
isolation tenant).

Pour devenir un **vrai ERP RH**, il manque surtout :
1. **La paie** (le plus gros manque et la plus forte valeur) — bulletins,
   cotisations sociales locales, IPR, net/brut, déclarations.
2. Une **gestion fine des droits** (RBAC granulaire) et un **moteur de
   workflow/validation** généralisé.
3. Des modules RH standards absents : **recrutement (ATS)**, **évaluations/
   performance**, **gestion des absences complète** (soldes, acquisition),
   **onboarding/offboarding**.
4. Le **reporting/analytics RH** (effectifs, turnover, absentéisme, masse
   salariale) et un **self-service** employé/manager consolidé.
5. Des **fondations techniques** de niveau ERP : audit, migrations, tests,
   permissions, exports, API, et un cadre applicatif plus robuste.

**Recommandation structurante** : avant de construire la paie (gros module,
règles complexes, enjeux légaux), **évaluer sérieusement une migration vers un
framework (Laravel)**. Le micro-framework maison (pas d'ORM, pas de migrations,
tout manuel) deviendra un frein à cette échelle.

---

## 2. Inventaire de l'existant (par domaine)

| Domaine | Couvert par | État |
|---|---|---|
| Dossier employé | `employees`, `employee_contracts`, `employee_experiences`, `employee_trainings` | ✅ solide |
| Organisation | `departments`, `postes`, `provinces`, `nationalities` | ✅ base |
| Congés / absences | `leave_requests`, `leave_types` | ⚠️ partiel (pas de soldes/acquisition) |
| Temps de travail | `timesheets` (+ validation superviseur/manager) | ✅ bon |
| Présence biométrique | `pointages` (ZKTeco, collecteur + API) | ✅ intégré |
| Missions & déplacements | `missions`, `mission_team`, `mission_orders`, `mission_budget_*`, `budget_templates`, `budgetary_units` | ✅ avancé |
| Notes de frais | `expenses`, `charges` (+ workflow superviseur→manager) | ✅ bon |
| GED | `ged_folders`, `ged_files`, `ged_shares`, `ged_external_links` | ✅ bon |
| Multi-tenant | `tenants`, `users` (super-admin / admin tenant / rôles) | ✅ |
| Notifications | `notifications` | ⚠️ basique |
| Audit | `action_logs` **(présente mais vide — non exploitée)** | ❌ à activer |

---

## 3. Analyse d'écart vs un ERP RH complet

| Module ERP RH | État actuel | Ce qu'il manque |
|---|---|---|
| **Paie (payroll)** | ❌ absent (salaire stocké au contrat) | Moteur de bulletins, rubriques, cotisations (INSS/CNSS), IPR, net/brut, historique, export bancaire |
| **Recrutement / ATS** | ❌ absent | Offres, candidats, candidatures, entretiens, pipeline |
| **Onboarding / Offboarding** | ❌ absent | Check-lists, tâches, workflows d'arrivée/départ |
| **Gestion des absences** | ⚠️ partiel | Types (existe), **soldes & acquisition**, calendrier, workflow d'approbation, justificatifs |
| **Évaluation / performance** | ❌ (hors note timesheet) | Campagnes d'entretien, objectifs/OKR, compétences, notation |
| **Formation / L&D** | ⚠️ partiel | Catalogue, plans de formation, demandes, budget formation |
| **Rémunération & avantages** | ⚠️ partiel | Grilles salariales, primes, avantages, historique |
| **Reporting / Analytics RH** | ⚠️ tableaux de bord basiques | Effectifs, turnover, absentéisme, masse salariale, pyramide des âges, exports |
| **Self-service employé (ESS)** | ⚠️ partiel | Demandes de congé, bulletins, attestations, mise à jour infos |
| **Self-service manager (MSS)** | ⚠️ partiel | Vue équipe consolidée, validations centralisées |
| **Conformité / déclaratif** | ❌ | Déclarations sociales/fiscales, registres légaux, contrats types |

---

## 4. Grands chantiers fonctionnels (priorisés)

### 🥇 P1 — Paie (le différenciateur d'un ERP RH)
Le plus gros manque et la plus forte valeur. Nécessite :
- Rubriques de paie paramétrables (gains/retenues), grilles.
- Cotisations sociales locales (**INSS/CNSS**), **IPR** (barème), net/brut/net à payer.
- Bulletin de paie (PDF), historique par employé et par période.
- Lien avec présence/congés (absences → paie) et contrats (salaire de base).
- Export (banque, comptabilité), journaux de paie.
- **Enjeu légal fort** → à cadrer avec un expert paie local.

### 🥈 P2 — Socle transverse (préalable à tout le reste)
- **RBAC granulaire** : permissions par action/module (au-delà des 4 rôles actuels).
- **Moteur de workflow/validation** réutilisable (congés, paie, notes de frais, missions partagent le même moteur).
- **Journal d'audit** : activer `action_logs` (qui, quoi, quand) — indispensable en RH/paie.

### 🥉 P3 — Modules RH standards
- **Gestion des absences complète** (soldes, acquisition, calendrier, workflow).
- **Évaluations / performance** (campagnes, objectifs, compétences).
- **Recrutement (ATS)** + **onboarding/offboarding**.
- **Formation** (catalogue, plans, demandes).

### P4 — Pilotage & expérience
- **Analytics RH** (effectifs, turnover, absentéisme, masse salariale) + exports Excel/PDF.
- **Self-service** employé/manager consolidé.

---

## 5. Fondations techniques à muscler

| Chantier | Pourquoi | Priorité |
|---|---|---|
| **Composer + PSR-4 + namespaces** | Base d'un code maintenable ; requis pour libs (paie, PDF, Excel) | Haute |
| **Système de migrations** (ex. Phinx) | Remplacer les `.sql`/scripts épars ; déploiements fiables | Haute |
| **Journal d'audit** (`action_logs`) | Traçabilité RH/paie | Haute |
| **RBAC / permissions** | Contrôle d'accès fin | Haute |
| **Tests automatisés** (PHPUnit) | Non-régression sur des règles critiques (paie !) | Haute |
| **Moteur de workflow** | Mutualiser les circuits de validation | Moyenne |
| **Exports** (PDF/Excel) | Bulletins, rapports, déclarations | Moyenne |
| **API REST** (déjà amorcée : `/ingest`) | Intégrations (compta, banque, présence) | Moyenne |
| **Multi-devise / i18n** | Contexte multi-pays éventuel | Moyenne |
| **Logging structuré + supervision** | Exploitation | Moyenne |
| **Sauvegardes automatisées + PRA** | Données RH critiques | Haute |
| **Pagination + index SQL** | Tenue en charge | Moyenne |

---

## 6. Décision structurante : framework maison ou migration ?

Le micro-framework maison a bien servi jusqu'ici, mais un ERP RH (surtout la
paie) implique : ORM, migrations, validation, files d'attente, permissions,
tests, exports, i18n — que le framework actuel n'offre pas et qu'il faudrait
réécrire à la main.

**Recommandation** : traiter cela comme un point de bascule.
- **Court terme** : continuer à durcir/modulariser l'existant (Composer, audit,
  RBAC, migrations) — utile quel que soit le choix.
- **Avant la paie** : évaluer une **migration vers Laravel** (ou Symfony).
  Construire la paie sur un framework mature est bien plus sûr et rapide que sur
  le socle actuel. Migration progressive (module par module) recommandée plutôt
  que big-bang.

*(Alternative : rester en maison et internaliser ORM/migrations/permissions —
faisable mais coûteux et risqué à cette ambition.)*

---

## 7. Feuille de route par phases

- **Phase A — Fondations** : Composer/PSR-4, migrations, activation `action_logs`,
  RBAC granulaire, tests de base. *(préalable à tout)*
- **Phase B — Décision framework** : PoC Laravel + stratégie de migration
  progressive (ou décision de rester maison, assumée).
- **Phase C — Absences & workflow** : gestion des congés complète sur un moteur
  de workflow réutilisable.
- **Phase D — Paie** : le gros morceau, avec expert paie local + tests + exports.
- **Phase E — Modules RH** : évaluations, recrutement/ATS, onboarding, formation.
- **Phase F — Pilotage** : analytics RH, self-service consolidé, exports.

*(Les phases A/B conditionnent la vélocité et la sûreté de tout le reste.)*

---

## 8. Risques & conformité

- **Paie = risque légal/financier** : barèmes IPR, taux INSS/CNSS, règles
  d'ancienneté/congés — à valider avec un expert local ; erreurs = litiges.
- **Protection des données personnelles** (données RH sensibles, biométrie de
  présence) : politique de rétention, accès restreint, journal d'audit, chiffrement.
- **Sauvegardes / continuité** : la donnée RH/paie est critique → sauvegardes
  testées + procédure de restauration.
- **Multi-tenant + paie** : bien cloisonner les données de paie par tenant.
- **Montée en charge** : le mutualisé peut devenir limitant avec la paie et les
  volumes ; prévoir un plan d'hébergement.

---

## 9. Prochaine étape recommandée

Commencer par la **Phase A (fondations)** — en particulier **Composer/PSR-4 +
migrations + activation du journal d'audit + RBAC** — car ces briques accélèrent
et sécurisent *tous* les modules suivants, et sont utiles que l'on migre ou non
vers un framework. Puis trancher la question du framework **avant** d'attaquer la
paie.

> Les chantiers de sécurité déjà réalisés sont décrits dans `SECURITY.md`.
