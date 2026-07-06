# ADR-0001 : Framework applicatif — garder le micro-framework maison ou migrer vers Laravel

**Statut :** Accepté — **Django (Python)**, sur VPS
**Date :** 2026-07-06
**Décideurs :** Jean LIMBA (propriétaire produit / dev principal)

## Contexte

`evolution` est un SIRH multi-tenant fonctionnel, développé sur un **micro-framework
PHP maison** : routage par `App.php`, wrapper PDO manuel, **pas d'ORM**, **pas de
système de migrations** (scripts SQL épars), autoload maison (+ un peu de Composer
récemment pour la lib ZKTeco), auth par session, RBAC grossier (4 rôles), **pas de
tests automatisés**. ~30 tables, sécurité durcie, hébergé en **mutualisé (cPanel)**,
avec un collecteur de présence local.

**Ambition** : en faire un **ERP RH complet**, dont un module **Paie** (bulletins,
cotisations INSS/CNSS, IPR — contexte RDC). La paie implique calculs monétaires
précis, génération PDF, exports, et une forte exigence de **tests** et de
**traçabilité**.

**Forces en présence :**
- Équipe **très réduite** → la vélocité et le « batteries included » comptent.
- App **déjà en service** → réécrire ce qui marche est un risque en soi.
- **Mutualisé** → contrainte réelle sur les processus longs (workers de queue).
- Le plus gros investissement futur (paie + modules restants) est **devant** nous.

## Décision (retenue)

**Réécrire progressivement l'application en Django (Python), sur un VPS.**

Les **deux conditions** qui justifient Django sont réunies : (1) **Python est le
langage préféré** pour la maintenance long terme, et (2) le **passage sur VPS est
accepté**. C'est un **choix assumé de réécriture** (pas un portage) : le code PHP
n'est pas réutilisable, mais **le schéma de base et la logique métier se
transposent**, et **Django admin** accélère fortement le back-office ERP. L'app PHP
actuelle **reste en service** jusqu'à ce que la version Django atteigne la parité,
module par module.

> *Note : à contexte différent (préférence PHP ou maintien du mutualisé), la
> recommandation aurait été Laravel — voir l'analyse ci-dessous, conservée comme
> trace de décision.*

## Options considérées

### Option A — Garder le framework maison et l'étoffer

| Dimension | Évaluation |
|-----------|------------|
| Complexité | Élevée à terme (réimplémenter ORM, migrations, validation, policies, tests à la main) |
| Coût | Faible à court terme, **élevé et croissant** à long terme |
| Scalabilité (produit) | Faible — chaque brique standard est à réinventer |
| Familiarité équipe | Maximale (code écrit maison) |
| Risque migration | Nul (on ne migre pas) |
| Aptitude à la paie | **Faible** — construire une paie fiable sans ORM/tests/outillage est risqué |

**Pour :** zéro migration ; maîtrise totale ; aucun changement d'hébergement.
**Contre :** on réécrit à la main ce qu'un framework offre gratuitement ; la paie
et les modules RH deviennent lents et risqués à livrer ; dette technique croissante ;
pas de tests → dangereux pour la paie.

### Option B — Migrer vers Laravel (progressif)

| Dimension | Évaluation |
|-----------|------------|
| Complexité | Moyenne (migration progressive) ; faible ensuite pour construire |
| Coût | **Investissement initial** (migration) puis **fort gain** de vélocité |
| Scalabilité (produit) | Élevée — Eloquent, migrations, policies, queues, tests, packages |
| Familiarité équipe | À acquérir (courbe d'apprentissage Laravel) |
| Risque migration | Moyen (maîtrisé par l'approche progressive) |
| Aptitude à la paie | **Élevée** — ORM, calculs testables, PDF/Excel via packages, scheduler |

**Pour :** migrations, ORM (Eloquent), validation, **policies/gates** (RBAC natif),
**tests** (PHPUnit/Pest intégrés), packages matures (PDF, Excel, permissions
`spatie/laravel-permission`, multi-tenant), Blade, auth clé-en-main. Idéal pour la
paie et les modules restants.
**Contre :** coût de migration ; courbe d'apprentissage ; frictions possibles en
**mutualisé** (voir Conséquences) ; on fait tourner deux stacks pendant la transition.

### Option C — Symfony

Très solide, mais plus verbeux et à courbe plus raide pour une petite équipe.
Laravel offre un meilleur ratio vélocité/effort ici. **Écartée** (au profit de B).

### Option D — Django (Python)

| Dimension | Évaluation |
|-----------|------------|
| Complexité | **Réécriture complète dans un autre langage** (pas de portage progressif) |
| Coût | Le plus élevé (tout le code PHP est à refaire ; seule la DB/logique se transpose) |
| Scalabilité (produit) | Élevée — ORM Django, migrations, **Django admin** (back-office auto = accélérateur ERP), DRF |
| Familiarité équipe | **Inconnue** — dépend de la maîtrise Python vs PHP |
| Risque migration | **Élevé** (réécriture + changement d'écosystème) |
| Hébergement | **Mal supporté en mutualisé cPanel** → impose quasi un **VPS** |
| Aptitude à la paie | Élevée (Python + `Decimal`, tests, PDF/Excel) |

**Pour :** ORM et migrations excellents ; **Django admin** (interface d'admin
générée = gros gain pour un ERP interne CRUD) ; langage Python si l'équipe y est
plus productive ; écosystème data mature.
**Contre :** ce n'est **pas une migration mais une réécriture** (langage différent →
aucun code PHP réutilisable, pas de cohabitation « strangler » simple) ; **impose
un VPS** (Django tourne mal/pas en mutualisé) ; courbe si l'équipe est surtout PHP ;
risque et coût maximaux.

## Analyse des trade-offs

- **Laravel vs Django : ce n'est pas symétrique.** L'app existe déjà **en PHP**.
  Laravel = **portage progressif dans le même langage** (réutilise la base MySQL
  telle quelle, cohabitation possible, tourne sur l'hébergement actuel). Django =
  **réécriture dans un autre langage** (aucun code réutilisable, pas de cohabitation
  simple) **+ quasi-obligation de VPS**. À périmètre égal, Django coûte et risque
  nettement plus. Django ne se justifie que si **Python est clairement le langage
  préféré/maîtrisé pour la maintenance à long terme** et qu'on accepte le passage
  VPS — auquel cas **Django admin** est un vrai accélérateur pour un back-office ERP.
- **Le point de bascule, c'est la paie.** Tant qu'on restait sur des modules CRUD,
  le framework maison tenait. La paie (règles, argent, tests, PDF, déclarations)
  change l'équation : la faire à la main est lent et risqué ; Laravel la rend
  réaliste pour une petite équipe.
- **Coût de migration vs coût de non-migration.** Migrer coûte maintenant ; ne pas
  migrer coûte à *chaque* module futur (tout est à réinventer) et ajoute du risque
  sur la paie. Sur l'horizon « ERP complet », B gagne.
- **Taille encore raisonnable.** ~30 tables / ~15 modules : c'est encore
  **portable progressivement** sans exploser l'effort. Plus on attend, plus c'est
  cher.
- **Ce qui est déjà fait n'est pas perdu.** Le schéma DB, la logique métier, l'audit,
  les correctifs sécurité **se transposent**. On migre la plomberie, pas le métier.

## Conséquences

**Ce qui devient plus facile :**
- Construire la **paie** et les modules RH (recrutement, évaluations, absences) vite et avec tests.
- **Migrations** de schéma versionnées, **RBAC** fin (policies), **validation**, **exports** PDF/Excel.
- Recrutement/maintenance (Laravel = compétence courante).

**Ce qui devient plus difficile / à surveiller :**
- **Hébergement mutualisé** : Laravel y tourne (docroot=`public`, PHP 8.x, Composer,
  `storage/` inscriptible), mais les **workers de queue** longue durée n'y vivent pas
  bien → utiliser le **scheduler via cron** (dispo en cPanel) et une queue `database`
  déclenchée par cron, **ou** prévoir un **petit VPS** quand la paie/les volumes
  grandiront. À trancher (voir action items).
- **Deux stacks en parallèle** pendant la transition (routage à cloisonner).
- **Courbe d'apprentissage** Laravel (impact sur le planning initial).

**À revisiter :**
- Le choix d'hébergement (mutualisé vs VPS) au moment d'attaquer la paie.
- La stratégie multi-tenant (global scope Eloquent sur `tenant_id`, ou package dédié).

## Stratégie de réécriture (Django) — retenue

1. **VPS + socle** : provisionner un VPS (Python 3.x, Nginx + Gunicorn, base de
   données, Certbot/HTTPS). Créer le projet Django et ses apps par domaine.
2. **Reprise de la base** : pointer Django sur la base existante et générer les
   modèles avec `inspectdb` (bootstrap), puis reprendre la main via les migrations
   Django. *(Option à évaluer : passer MySQL → PostgreSQL, souvent recommandé avec
   Django ; sinon garder MySQL.)*
3. **Django admin** : l'activer immédiatement → back-office CRUD instantané sur les
   entités reprises (gros gain rapide pour un ERP interne).
4. **Multi-tenant** : filtrage par `tenant_id` (middleware + querysets/managers
   scopés), ou package dédié.
5. **Nouveaux modules en Django natif** : paie, RBAC (groupes/permissions Django), etc.
6. **Portage incrémental** des modules existants ; l'app PHP **reste en ligne
   jusqu'à parité** par module, puis bascule.
7. **Présence** : réécrire le collecteur en **Python** (lib `pyzk` pour ZKTeco) et
   exposer l'ingestion via **Django REST Framework** (remplace l'API PHP `/ingest`).
8. **Tests** (pytest / tests Django) dès le socle — non négociable avant la paie.

## Estimation d'effort (ordre de grandeur, équipe très réduite)

> Estimations grossières, à affiner ; fortement dépendantes de la familiarité Laravel.

- **Socle** (projet, DB, auth, tenancy, layout, CI/tests) : ~2–4 semaines.
- **Par module porté** : ~quelques jours à ~2 semaines selon complexité.
- **Paie** (construite nativement) : chantier majeur en soi, **quel que soit** le
  framework — mais nettement plus sûr et rapide sur Laravel + tests.
- **Total migration du périmètre actuel** : plusieurs mois en temps partiel,
  étalés (l'app reste en service pendant ce temps).

## Risques

- **Réécriture d'une app qui marche** → mitigé par l'approche progressive + tests.
- **Mutualisé insuffisant** pour la paie/queues → prévoir un VPS le moment venu.
- **Courbe Laravel** si l'équipe ne la connaît pas → prévoir montée en compétence.
- **Transition qui traîne** (deux stacks trop longtemps) → cadrer un ordre de portage
  et des jalons.
- **Paie = risque légal** (indépendant du framework) → expert paie local + tests.

## Action items

1. [x] Décision prise : **Django (Python) sur VPS**.
2. [ ] Choisir/provisionner le **VPS** (specs, OS, base de données, Nginx+Gunicorn, HTTPS).
3. [ ] Trancher **MySQL vs PostgreSQL** pour la cible Django.
4. [x] **PoC Django** (local, 2026-07-06) : Django 6 + PyMySQL branché sur la base
   `evolution` ; `inspectdb` a généré **les 33 modèles** correspondant aux 33 tables
   **sans aucun avertissement** (schéma propre, Django-compatible). La reprise du
   schéma existant est donc quasi immédiate. *(PoC dans `C:\laragon\www\evolution_dj`,
   hors dépôt PHP ; aucune écriture faite dans la base.)*
   **Démo admin validée** : `django check` sans erreur sur les 33 modèles ;
   Django admin affiche les vraies données (`/admin/core/employees/` → 200, 3
   employés réels), les tables système Django isolées dans une SQLite jetable via
   un routeur → **base `evolution` non modifiée** (aucune table `django_*`/`auth_*`).
5. [ ] Définir l'**ordre de portage** des modules et les jalons (l'app PHP reste en service jusqu'à parité).
6. [ ] Mettre en place les **tests** dès le socle (préalable non négociable à la paie).
7. [ ] Réécrire le **collecteur de présence** en Python (`pyzk`) + ingestion via DRF.
8. [ ] **Ne plus investir** dans l'app PHP au-delà des correctifs indispensables (le gros reste en service, pas de nouveaux gros modules côté PHP).

---
*Voir `ERP_ROADMAP.md` (feuille de route ERP) et `SECURITY.md` (état sécurité).*
