# Gestion du Patrimoine de l'État (GPE)

Plateforme de gestion du patrimoine de l'État — **architecture micro-services**,
**Laravel 12**, **PostgreSQL**, **Docker**.

Projet réalisé dans le cadre du brief Simplonline « P3 DEV WEB 1 ».

## Architecture

```
Navigateur ──> Nginx Gateway (:8080)
                ├── /api/v1/auth/*        ──> auth-service        (gpe_auth)
                ├── /api/v1/assets/*      ──> asset-service      (gpe_assets)
                ├── /api/v1/maintenance/* ──> maintenance-service(gpe_maintenance)
                └── /*                    ──> asset-service (interface Blade)
```

| Service | Responsabilité | Base |
|---|---|---|
| `auth-service` | Utilisateurs, rôles, tokens Sanctum | `gpe_auth` |
| `asset-service` | Actifs, catégories, localisations, affectations, évaluations, rapports + UI | `gpe_assets` |
| `maintenance-service` | Ordres de maintenance | `gpe_maintenance` |

Communication : API REST + validation de token inter-services
(`GET /api/me` sur auth-service).

## Démarrage rapide

```bash
cp .env.example .env
docker compose up -d --build
```

Puis ouvrir **http://localhost:8080**

Comptes de démonstration : `admin@gpe.local` / `password` (aussi `agent@`, `responsable@`).

## Structure du dépôt

```
gestion-patrimoine/
├── docker-compose.yml        # Orchestration (db, 3 services, gateway)
├── .env.example
├── gateway/nginx.conf        # Reverse proxy / routage API
├── postgres/init/            # Création des 3 bases au 1er démarrage
├── docs/
│   ├── analyse-besoin.md     # Besoin, UML (cas d'utilisation, classes, déploiement)
│   ├── proposition-financiere.md
│   ├── fiche-technique.md
│   └── guide-utilisation.md
├── maquette/maquettes.html   # Maquettes HTML de référence (5 écrans)
└── services/
    ├── auth-service/         # Laravel : auth, users, rôles
    ├── asset-service/        # Laravel : actifs + UI Blade
    └── maintenance-service/  # Laravel : maintenances
```

## API (extrait)

Toutes les routes `/api/v1/*` requièrent `Authorization: Bearer <token>`
(obtenu via `POST /api/v1/auth/login`).

- `auth` : `login`, `register`, `logout`, `me`, `users` (CRUD, admin)
- `assets` : `categories`, `localisations`, `actifs` (+ `/{id}/statut`),
  `affectations` (+ `/{id}/cloturer`), `evaluations`,
  `rapports/patrimoine`, `rapports/actifs-dormants`
- `maintenance` : `maintenances` (CRUD), `stats`

Documentation complète : **[docs/guide-utilisation.md](docs/guide-utilisation.md)**

## Déploiement (production)

Plateforme hébergée gratuitement sur **Render** :

| Service | URL |
|---|---|
| **URL unique (UI + API)** | https://gpe-assets.onrender.com |
| Auth (direct) | https://gpe-auth.onrender.com |
| Assets (direct) | https://gpe-assets.onrender.com |
| Maintenance (direct) | https://gpe-maintenance.onrender.com |

**URL unique** : `https://gpe-assets.onrender.com` sert l'UI (`/`, `/login`)
et expose toute l'API sous le préfixe `/api/v1/` :

- `/api/v1/auth/*` → auth-service (proxy interne)
- `/api/v1/assets/*` → asset-service (alias de routes)
- `/api/v1/maintenance/*` → maintenance-service (proxy interne)

Un gateway Nginx dédié (`gateway/`) existe aussi pour un déploiement
Docker Compose local ou un hébergeur sans limite de services.

Comptes seedés : `admin@gpe.local / password`, `agent@gpe.local / password`.

> ⚠️ Render free tier : les services se mettent en veille après inactivité
> (première requête = ~30-60 s de démarrage) et la base PostgreSQL expire
> après 30 jours — penser à la recréer/le sauvegarder pour une démo.

## Maquettes

Maquettes UI dans **Figma** : [GPE — Maquettes Gestion du Patrimoine de l'État](https://www.figma.com/design/3VWvxTbVhZfRh733g9QeJe)
(5 écrans : connexion, tableau de bord, liste des actifs, fiche actif, maintenances).
Une version HTML de référence est disponible dans `maquette/maquettes.html`.

## Règles métier implémentées

- Une seule affectation en cours par actif
- Suppression d'actif bloquée si affectation en cours
- Évaluation → met à jour `valeur_actuelle` et `etat`
- Maintenance close → non modifiable/supprimable
- Actif dormant = en service, sans affectation ni évaluation depuis 12 mois
- Traçabilité : `observe_par`, `eval_par`, `demande_par` sur chaque opération

## Développement local (sans Docker)

```bash
# Dans chaque dossier services/<nom>
composer install
cp .env.example .env
php artisan key:generate
# Configurer DB_* vers un PostgreSQL local avec les 3 bases créées
php artisan migrate --seed
php artisan serve
```

## Déploiement

Voir **[docs/fiche-technique.md](docs/fiche-technique.md)** section 8
(Render / Railway / Oracle Cloud Always Free).

## Auteur

Yannick-Eudes Cédric Koffi APETOH — Projet individuel, Simplonline.
