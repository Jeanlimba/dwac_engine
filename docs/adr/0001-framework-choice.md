# ADR-0001 : Framework applicatif — garder le micro-framework maison ou migrer vers Laravel

**Statut :** Proposé
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

## Décision (proposée)

**Migrer vers Laravel, de façon progressive (pattern « strangler »), et réaliser
cette migration AVANT de construire la paie.** Ne pas réécrire en big-bang : porter
module par module, construire les **nouveaux** modules (paie, RBAC, etc.)
directement en Laravel, et retirer l'ancien code au fur et à mesure.

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

## Analyse des trade-offs

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

## Stratégie de migration progressive (strangler)

1. **Socle** : nouveau projet Laravel pointant sur **la même base** ; modèles Eloquent
   mappés sur les tables existantes (sans changer le schéma au départ) ; auth + layout
   + multi-tenant (global scope sur `tenant_id`).
2. **Migrations** : générer les migrations à partir du schéma actuel (référence), puis
   tout nouveau changement passe par des migrations Laravel.
3. **Nouveaux modules en Laravel natif** : paie, RBAC fin, etc.
4. **Portage incrémental** des modules existants (employés, dépenses, présence, GED…),
   en retirant l'ancien code module par module.
5. **Cohabitation** : pendant la transition, router par module (Laravel sur les
   routes migrées, legacy sur le reste) ou cutover propre par module.
6. **Tests** dès le socle (surtout avant la paie).

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

1. [ ] Valider/infirmer cette décision (Jean).
2. [ ] Vérifier la compatibilité Laravel de l'hébergement actuel (PHP 8.x, SSH/Composer, docroot, cron) **ou** décider d'un petit VPS.
3. [ ] PoC Laravel : socle + auth + 1 module simple (ex. Départements) branché sur la base existante, pour mesurer l'effort réel.
4. [ ] Décider de l'ordre de portage des modules et des jalons.
5. [ ] Mettre en place les tests dès le socle (préalable non négociable à la paie).
6. [ ] Ne **pas** investir davantage dans un RBAC/outillage maison lourd d'ici la décision.

---
*Voir `ERP_ROADMAP.md` (feuille de route ERP) et `SECURITY.md` (état sécurité).*
