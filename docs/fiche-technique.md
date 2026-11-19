# Fiche technique — GPE

## 1. Stack technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 12 (PHP 8.3) — un service par domaine métier |
| Auth | Laravel Sanctum (tokens Bearer) |
| Base de données | PostgreSQL 16 (une base par service) |
| Conteneurisation | Docker + Docker Compose |
| Passerelle | Nginx (reverse proxy + routage) |
| Frontend | Blade + Bootstrap 5 (CDN), appels API en fetch() |
| Modélisation | UML (Mermaid) — voir `docs/analyse-besoin.md` |
| CI/CD | GitHub Actions (optionnel, non inclus) |

## 2. Architecture micro-services

```
Navigateur ──> Nginx Gateway (:8080)
                ├── /api/v1/auth/*       ──> auth-service       (gpe_auth)
                ├── /api/v1/assets/*     ──> asset-service     (gpe_assets)
                ├── /api/v1/maintenance/*──> maintenance-service(gpe_maintenance)
                └── /*                   ──> asset-service (UI Blade)
```

### Communication inter-services

- **Auth** : chaque service valide le token Bearer en appelant
  `GET {AUTH_SERVICE_URL}/api/me` (middleware `ServiceAuthMiddleware`).
- **Actifs** : maintenance-service vérifie l'existence d'un actif via
  `GET {ASSET_SERVICE_URL}/api/actifs/{id}` (`App\Support\AssetClient`).
- **Règle d'or** : pas de clé étrangère inter-bases ; les références sont des
  IDs logiques (`actif_id`, `responsable_id`, `observe_par`…).

## 3. Services et endpoints

### auth-service

| Méthode | Route | Accès | Description |
|---|---|---|---|
| POST | `/api/login` | public | Connexion → token Sanctum |
| POST | `/api/register` | public* | Création compte (*1er = admin, sinon admin requis) |
| POST | `/api/logout` | auth | Révocation du token |
| GET | `/api/me` | auth | Profil utilisateur courant |
| * | `/api/users` | admin | CRUD utilisateurs |

### asset-service

| Méthode | Route | Description |
|---|---|---|
| * | `/api/categories` | CRUD catégories |
| * | `/api/localisations` | CRUD localisations |
| * | `/api/actifs` | CRUD actifs (filtres : statut, etat, categorie_id, localisation_id, search) |
| POST | `/api/actifs/{id}/statut` | Changement de statut |
| * | `/api/affectations` | CRUD + `PATCH /{id}/cloturer` |
| * | `/api/evaluations` | CRUD évaluation (met à jour valeur/état de l'actif) |
| GET | `/api/rapports/patrimoine` | Statistiques globales |
| GET | `/api/rapports/actifs-dormants` | Actifs dormants (RB7) |

### maintenance-service

| Méthode | Route | Description |
|---|---|---|
| * | `/api/maintenances` | CRUD ordres de maintenance (actif_id validé via asset-service) |
| GET | `/api/stats` | Statistiques maintenance |

## 4. Modèle de données (PostgreSQL)

- **gpe_auth** : `users` (name, email, password, role), `personal_access_tokens`
- **gpe_assets** : `categories`, `localisations`, `actifs`, `affectations`, `evaluations`
- **gpe_maintenance** : `maintenances`

Voir migrations dans `services/*/database/migrations/`.

## 5. Variables d'environnement

Fichier `.env` à la racine (copier depuis `.env.example`) :

```env
POSTGRES_USER=gpe
POSTGRES_PASSWORD=secret
APP_ENV=local
APP_DEBUG=true
# Les services héritent DB_HOST=db et leur DB_DATABASE via docker-compose
```

Chaque service lit `DB_*` et `AUTH_SERVICE_URL` / `ASSET_SERVICE_URL` depuis
l'environnement injecté par docker-compose.

## 6. Lancement

```bash
docker compose up -d --build
# UI : http://localhost:8080  (login : admin@gpe.local / password)
```

Le script `docker-entrypoint.sh` de chaque service :
1. Génère `.env` et `APP_KEY` si absents
2. Attend que PostgreSQL soit prêt
3. Exécute `php artisan migrate --force` puis `db:seed --force`
4. Démarre Apache sur le port 8000

## 7. Sécurité

- Mots de passe hashés (bcrypt), tokens Sanctum avec expiration configurable
- Validation des entrées via Form Request / `validate()` sur chaque endpoint
- Autorisation par rôle (`role:admin`) sur la gestion des utilisateurs
- Pas de secrets dans le dépôt (`.env` ignoré par git)

## 8. Hébergement gratuit possible

- **Render** : Docker natif, PostgreSQL managé (free tier avec expiration)
- **Railway** : similar, credit mensuel gratuit
- Alternative : VM **Oracle Cloud Always Free** + Docker

Pour Render : créer un `render.yaml` blueprint ou déployer chaque service comme
"Web Service" avec build Docker, puis un service PostgreSQL par base.
