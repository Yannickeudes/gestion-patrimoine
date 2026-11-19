<?php

namespace Database\Seeders;

use App\Models\Actif;
use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Evaluation;
use App\Models\Localisation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['nom' => 'Batiment administratif', 'type' => 'immobilier'],
            ['nom' => 'Vehicule de service', 'type' => 'vehicule'],
            ['nom' => 'Ordinateur portable', 'type' => 'informatique'],
            ['nom' => 'Bureau et chaise', 'type' => 'mobilier'],
            ['nom' => 'Equipement divers', 'type' => 'autre'],
        ])->map(fn ($c) => Categorie::firstOrCreate(['nom' => $c['nom']], $c));

        $loc = Localisation::firstOrCreate(
            ['region' => 'Littoral', 'commune' => 'Cotonou', 'batiment' => 'Ministere des Finances'],
            ['adresse' => 'Rue 31, Cadjehoun']
        );
        Localisation::firstOrCreate(
            ['region' => 'Atlantique', 'commune' => 'Porto-Novo'],
            ['adresse' => 'Quartier Ouando']
        );

        $actifs = [
            ['numero_serie' => 'VEH-2023-001', 'designation' => 'Toyota Land Cruiser', 'categorie_id' => $categories[1]->id, 'marque' => 'Toyota', 'modele' => 'Land Cruiser', 'date_acquisition' => '2023-03-15', 'valeur_acquisition' => 45000000, 'etat' => 'bon', 'statut' => 'en_service', 'localisation_id' => $loc->id],
            ['numero_serie' => 'INFO-2024-012', 'designation' => 'PC Portable Dell Latitude', 'categorie_id' => $categories[2]->id, 'marque' => 'Dell', 'modele' => 'Latitude 5540', 'date_acquisition' => '2024-01-10', 'valeur_acquisition' => 850000, 'etat' => 'neuf', 'statut' => 'en_service', 'localisation_id' => $loc->id],
            ['numero_serie' => 'MOB-2022-045', 'designation' => 'Bureau direction + chaise', 'categorie_id' => $categories[3]->id, 'date_acquisition' => '2022-06-01', 'valeur_acquisition' => 350000, 'etat' => 'use', 'statut' => 'en_service', 'localisation_id' => $loc->id],
            ['numero_serie' => 'IMM-2019-002', 'designation' => 'Batiment administratif bloc B', 'categorie_id' => $categories[0]->id, 'date_acquisition' => '2019-11-20', 'valeur_acquisition' => 2500000000, 'etat' => 'bon', 'statut' => 'en_service', 'localisation_id' => $loc->id],
            ['numero_serie' => 'VEH-2020-007', 'designation' => 'Toyota Hilux pick-up', 'categorie_id' => $categories[1]->id, 'marque' => 'Toyota', 'modele' => 'Hilux', 'date_acquisition' => '2020-08-05', 'valeur_acquisition' => 28000000, 'etat' => 'use', 'statut' => 'maintenance', 'localisation_id' => $loc->id],
        ];

        $ids = [];
        foreach ($actifs as $a) {
            $a['valeur_actuelle'] = $a['valeur_acquisition'];
            $ids[] = Actif::firstOrCreate(['numero_serie' => $a['numero_serie']], $a)->id;
        }

        Affectation::firstOrCreate(
            ['actif_id' => $ids[0], 'service_destinataire' => 'Direction Generale'],
            ['date_debut' => '2023-04-01', 'observe_par' => 1]
        );
        Affectation::firstOrCreate(
            ['actif_id' => $ids[1], 'service_destinataire' => 'Service Informatique'],
            ['date_debut' => '2024-01-15', 'observe_par' => 1]
        );

        Evaluation::firstOrCreate(
            ['actif_id' => $ids[3], 'date_eval' => now()->subMonths(6)->toDateString()],
            ['valeur_estimee' => 2400000000, 'etat' => 'bon', 'observation' => 'Evaluation annuelle', 'eval_par' => 1]
        );
    }
}
