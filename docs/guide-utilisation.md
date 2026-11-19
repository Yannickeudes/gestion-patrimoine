# Guide d'utilisation — GPE

## 1. Accès

1. Ouvrir `http://localhost:8080` (ou l'URL de l'hébergeur)
2. Se connecter avec un compte fourni par l'administrateur

### Comptes de démonstration (seed)

| Email | Mot de passe | Rôle |
|---|---|---|
| admin@gpe.local | password | Administrateur |
| agent@gpe.local | password | Agent du patrimoine |
| responsable@gpe.local | password | Responsable |

> Changez ces mots de passe en production.

## 2. Tableau de bord

La page d'accueil affiche :

- **Cartes de statistiques** : nombre d'actifs, valeur totale du patrimoine,
  valeur d'acquisition, affectations en cours
- **Tableau des actifs** : recherche plein texte (désignation, n° série, marque)
  et filtre par statut

## 3. Gestion des actifs (API)

Toutes les routes API exigent le header `Authorization: Bearer <token>`.

### Créer un actif

```bash
curl -X POST http://localhost:8080/api/v1/assets/actifs \
  -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" \
  -d '{
    "numero_serie": "VEH-2026-100",
    "designation": "Toyota Corolla",
    "categorie_id": 2,
    "marque": "Toyota",
    "date_acquisition": "2026-01-15",
    "valeur_acquisition": 15000000,
    "etat": "neuf",
    "localisation_id": 1
  }'
```

### Filtrer les actifs

```
GET /api/v1/assets/actifs?statut=en_service&categorie_id=2&search=toyota
```

### Changer le statut

```
POST /api/v1/assets/actifs/5/statut
{ "statut": "maintenance", "motif": "Fuite d'huile" }
```

### Affecter un actif à un service

```
POST /api/v1/assets/affectations
{ "actif_id": 5, "service_destinataire": "DGA", "date_debut": "2026-11-19" }
```

Clôturer : `PATCH /api/v1/assets/affectations/{id}/cloturer`

### Évaluer un actif

```
POST /api/v1/assets/evaluations
{ "actif_id": 5, "date_eval": "2026-11-19", "valeur_estimee": 12000000, "etat": "bon" }
```

L'évaluation met à jour automatiquement `valeur_actuelle` et `etat` de l'actif.

## 4. Maintenances

```
POST /api/v1/maintenance/maintenances
{ "actif_id": 5, "type": "corrective", "date_demande": "2026-11-19",
  "description": "Révision moteur", "cout": 200000 }
```

Statuts : `ouvert → en_cours → clos`. `GET /api/v1/maintenance/stats` pour les statistiques.

## 5. Rapports

- `GET /api/v1/assets/rapports/patrimoine` — totaux par statut et par catégorie
- `GET /api/v1/assets/rapports/actifs-dormants` — actifs en service sans affectation ni évaluation depuis 12 mois

## 6. Administration des utilisateurs

```
POST /api/v1/auth/register        # 1er compte = admin ; ensuite admin only
GET/POST/PUT/DELETE /api/v1/auth/users   # CRUD (admin)
```

## 7. Dépannage

| Problème | Solution |
|---|---|
| `Token invalide` | Reconnectez-vous (les tokens expirent) |
| `Service d'authentification injoignable` | `docker compose ps` — vérifier auth-service |
| Tables absentes | `docker compose logs <service>` — l'entrypoint lance les migrations au démarrage |
| Port 8080 occupé | Modifier le mapping dans `docker-compose.yml` |
