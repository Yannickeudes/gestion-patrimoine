# Fiche de défense orale — GPE

> Préparation pour la restitution orale du brief « Gestion du Patrimoine de l'État ».
> Durée conseillée : 10–15 min de présentation + questions.

---

## 1. Pitch d'ouverture (30 sec)

> « J'ai conçu et déployé une plateforme de gestion du patrimoine de l'État :
> inventaire des actifs (bâtiments, véhicules, matériel informatique, mobilier),
> affectations, évaluations, maintenances et rapports. L'architecture est en
> micro-services Laravel, conteneurisée avec Docker, sur PostgreSQL — et la
> plateforme est hébergée gratuitement et accessible en ligne. »

**Montrer en direct** : https://gpe-assets.onrender.com/login
(code `admin@gpe.local` / `password`)

---

## 2. Déroulé de la présentation (5–8 min)

### a) Le besoin (1 min)
- L'État gère des milliers de biens sans vue consolidée → pertes, doublons, Maintenance imprévue
- Acteurs : admin système, agent du patrimoine, responsable/DGA
- Fonctionnalités : fiches actifs, catégories, localisations, affectations, évaluations, ordres de maintenance, rapports, actifs dormants

### b) L'architecture micro-services (2 min) — LE point clé

```
Utilisateur → asset-service (UI Blade + API)
                ├─ /api/v1/assets/*     → routes internes
                ├─ /api/v1/auth/*       → proxy → auth-service
                └─ /api/v1/maintenance/*→ proxy → maintenance-service

auth-service       → users, rôles, tokens Sanctum (base gpe_auth)
asset-service      → actifs, catégories, localisations, affectations,
                     évaluations, rapports (base gpe_assets)
maintenance-service→ ordres de maintenance (base gpe_maintenance)
```

**Argumentaire à réciter** :
- « Chaque service a **sa propre base PostgreSQL** — aucun partage de tables,
  les services ne communiquent que par API REST. C'est ce qui définit un
  micro-service par rapport à un monolithe modulaire. »
- « L'authentification est centralisée : les tokens Sanctum sont émis par
  auth-service, et chaque autre service **valide le token en appelant
  `GET /api/me`** — introspection, pas de session partagée. »
- « maintenance-service vérifie que l'actif existe réellement en appelant
  asset-service avant de créer un ordre — cohérence sans jointure SQL. »
- « Sur Render (gratuit), un 4e container gateway était impossible :
  j'ai intégré le routing `/api/v1/*` dans asset-service — même résultat,
  une seule URL publique. »

### c) Le code (1–2 min)
- Laravel 12, PHP 8.3, PostgreSQL 16
- ~30 endpoints REST, middleware `auth.service` + `role:`
- Vues Blade : login + dashboard (fetch vers `/api/v1/*`)
- Dockerfile par service, `docker-compose.yml` + gateway Nginx pour le local

### d) Les règles métier (1 min)
- Une seule affectation en cours par actif
- Suppression d'actif bloquée si affectation active
- Une évaluation met à jour `valeur_actuelle` et `etat`
- Maintenance close → non modifiable
- Actif dormant = en service sans mouvement depuis 12 mois

### e) Livrables annexes (30 sec)
- Maquette Figma (5 écrans), analyse besoin + UML (Mermaid),
  proposition financière (~765–920 k FCFA), fiche technique, guide utilisateur

---

## 3. Démo live (2–3 min) — script conseillé

1. `https://gpe-assets.onrender.com/login` → se connecter (admin / password)
2. Montrer le dashboard : 5 actifs, valeur totale **2 574 200 000 FCFA**
3. `GET /api/v1/assets/actifs` dans l'onglet réseau ou Postman
4. Montrer `docs/analyse-besoin.md` : diagrammes UML (cas d'utilisation, classes, déploiement)
5. Montrer le repo GitHub : structure `services/*`, `docker-compose.yml`, README

> Astuce : si la plateforme semble lente, c'est le cold start Render (~30–60 s).
> **Faites une première requête 5 min avant la démo** pour réveiller les services.

---

## 4. Questions probables + réponses

**« Pourquoi micro-services et pas un monolithe ? »**
> Découplage des équipes/déploiements, scalabilité indépendante, résilience.
> Chaque service peut être déployé ou mis à jour sans toucher les autres.
> Le coût : complexité réseau — justifiée ici par la séparation auth/patrimoine/maintenance.

**« Comment les services se connaissent-ils ? »**
> Ils ne se connaissent pas. auth-service émet des tokens ; asset et
> maintenance les valident par introspection HTTP. maintenance appelle
> asset-service pour vérifier l'existence d'un actif. Pas de registry,
> pas de base partagée — URLs en variables d'environnement.

**« Et si auth-service est down ? »**
> Les autres services refusent les requêtes (fail-closed) : pas de token
> validable = pas d'accès. C'est le comportement sécuritaire voulu.

**« Pourquoi PostgreSQL et pas MySQL ? »**
> Contrainte du brief + forces : JSONB, CTE, gestion fine des types enum,
> performances sur les requêtes d'agrégation des rapports.

**« Comment garantir l'intégrité entre services ? »**
> Validation à l'écriture : maintenance-service rejette un `actif_id`
> inexistant (appel HTTP à asset-service). Pas de transaction distribuée —
> chaque service est maître de ses données, la cohérence est vérifiée
> à la frontière.

**« C'est responsive / accessible ? »**
> Interface simple type back-office ; la maquette Figma couvre les écrans
> clés. Points d'amélioration identifiés : pagination côté UI, export PDF
> des rapports, notifications de maintenance.

**« Combien de temps / combien de code ? »**
> ~40 h prévues, 3 services Laravel complets, ~30 endpoints, migrations,
> seeders, tests manuels de bout en bout, CI GitHub Actions (lint PHP +
> composer validate), déploiement Render.

**« Que changeriez-vous en V2 ? »**
> Service reporting dédié, file d'attente pour les événements
> (maintenance → actif), WebSockets pour le temps réel, tests unitaires
> PHPUnit par service, CI de build Docker.

---

## 5. Fichiers à avoir sous la main

| Fichier | Pour montrer |
|---|---|
| `README.md` | Vue d'ensemble + URLs démo |
| `docs/analyse-besoin.md` | UML : cas d'utilisation, classes, déploiement |
| `docs/fiche-technique.md` | Stack, endpoints, variables d'env |
| `services/asset-service/routes/api.php` | Le routing `/api/v1/*` + alias |
| `services/asset-service/app/Http/Controllers/GatewayProxyController.php` | Le proxy inter-services |
| `services/asset-service/app/Http/Middleware/ServiceAuthMiddleware.php` | Validation du token |
| `docker-compose.yml` + `gateway/nginx.conf` | L'architecture locale complète |
| `render.yaml` | Le blueprint de déploiement |
