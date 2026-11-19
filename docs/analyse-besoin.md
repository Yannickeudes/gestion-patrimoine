# Analyse du besoin — Gestion du Patrimoine de l'État (GPE)

## 1. Contexte

L'État béninois gère un patrimoine immobilier, mobilier et roulant dispersé sur
l'ensemble du territoire. Aujourd'hui, l'inventaire est tenu de façon
manuelle (registres papier, tableurs), ce qui entraîne :

- Perte de traçabilité des biens (vols, détournements non détectés)
- Impossibilité de connaître la valeur réelle du patrimoine
- Difficulté à suivre les maintenances et leurs coûts
- Aucune vision consolidée pour la direction

## 2. Objectifs

- Centraliser l'inventaire de tous les biens de l'État
- Suivre le cycle de vie de chaque actif (acquisition → service → réforme → sortie)
- Gérer les affectations aux services et les évaluations périodiques
- Fournir des rapports et tableaux de bord à la direction
- Garantir la traçabilité (audit) des opérations

## 3. Acteurs et cas d'utilisation

| Acteur | Rôle |
|---|---|
| Administrateur | Gestion des comptes et des rôles |
| Agent du patrimoine | Saisie et mise à jour des fiches actifs, affectations, évaluations |
| Responsable | Consultation des rapports, validation des sorties de patrimoine |
| Système | Notifications, calculs de valeur, journalisation |

### Diagramme de cas d'utilisation (UML)

```mermaid
graph LR
    Admin((Administrateur))
    Agent((Agent patrimoine))
    Resp((Responsable))

    Admin --> UC1[Gerer les utilisateurs]
    Agent --> UC2[Enregistrer un actif]
    Agent --> UC3[Modifier un actif]
    Agent --> UC4[Changer le statut d'un actif]
    Agent --> UC5[Creer une affectation]
    Agent --> UC6[Cloturer une affectation]
    Agent --> UC7[Enregistrer une evaluation]
    Agent --> UC8[Declarer une maintenance]
    Resp --> UC9[Consulter le tableau de bord]
    Resp --> UC10[Generer les rapports]
    Resp --> UC11[Identifier les actifs dormants]
    UC5 -.->|include| UC12[Verifier l'actif]
    UC8 -.->|include| UC12
```

## 4. Règles métier

- **RB1** — Un actif ne peut avoir qu'**une seule affectation en cours** (`date_fin` NULL).
- **RB2** — Un actif ne peut être supprimé s'il a une affectation en cours.
- **RB3** — Le changement de statut suit le cycle : `en_service → maintenance → (en_service | reforme | cession) → sorti`.
- **RB4** — Chaque évaluation peut mettre à jour `valeur_actuelle` et `etat` de l'actif.
- **RB5** — Une maintenance `clos` ne peut plus être modifiée ni supprimée.
- **RB6** — Seul l'administrateur crée des comptes (le premier compte créé devient admin).
- **RB7** — Un "actif dormant" = `en_service` + aucune affectation en cours + aucune évaluation depuis 12 mois.
- **RB8** — Toute création/modification est attribuée à l'utilisateur connecté (`observe_par`, `eval_par`, `demande_par`).

## 5. Diagramme de classes (UML)

```mermaid
classDiagram
    class Categorie {
        +id
        +nom
        +type
        +description
    }
    class Localisation {
        +id
        +region
        +commune
        +adresse
        +batiment
    }
    class Actif {
        +id
        +numero_serie
        +designation
        +marque
        +modele
        +date_acquisition
        +valeur_acquisition
        +valeur_actuelle
        +etat
        +statut
    }
    class Affectation {
        +id
        +service_destinataire
        +date_debut
        +date_fin
    }
    class Evaluation {
        +id
        +date_eval
        +valeur_estimee
        +etat
        +observation
    }
    class Maintenance {
        +id
        +actif_id
        +type
        +date_demande
        +cout
        +statut
    }
    class User {
        +id
        +name
        +email
        +role
    }

    Categorie "1" --> "*" Actif : categorise
    Localisation "1" --> "*" Actif : localise
    Actif "1" --> "*" Affectation : fait l'objet de
    Actif "1" --> "*" Evaluation : evalue
    Actif "1" --> "*" Maintenance : subit
    User "1" --> "*" Affectation : observe
    User "1" --> "*" Evaluation : realise
```

> Note : `Maintenance` et `User` vivent dans des services distincts ; les liens
> sont des **références logiques** (IDs), pas des clés étrangères.

## 6. Diagramme de déploiement (architecture micro-services)

```mermaid
graph TB
    Client((Navigateur))
    subgraph Docker["Docker Compose"]
        GW[Nginx Gateway :8080]
        AUTH[auth-service :8000<br/>Laravel + Sanctum]
        AST[asset-service :8000<br/>Laravel]
        MAINT[maintenance-service :8000<br/>Laravel]
        DB[(PostgreSQL :5432<br/>gpe_auth / gpe_assets / gpe_maintenance)]
    end
    Client -->|HTTP| GW
    GW --> AUTH
    GW --> AST
    GW --> MAINT
    AUTH --> DB
    AST --> DB
    MAINT --> DB
    AST -.->|GET /api/me| AUTH
    MAINT -.->|GET /api/me + /api/actifs/{id}| AUTH & AST
```
