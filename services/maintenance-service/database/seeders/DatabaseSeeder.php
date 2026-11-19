<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Maintenance::firstOrCreate(
            ['actif_id' => 5, 'type' => 'corrective', 'date_demande' => now()->subDays(10)->toDateString()],
            ['description' => 'Changement plaquettes de frein - Toyota Hilux', 'cout' => 150000, 'statut' => 'en_cours', 'demande_par' => 1]
        );
        Maintenance::firstOrCreate(
            ['actif_id' => 2, 'type' => 'preventive', 'date_demande' => now()->subDays(30)->toDateString()],
            ['description' => 'Maintenance trimestrielle PC portable', 'cout' => 25000, 'statut' => 'clos', 'demande_par' => 1, 'clos_par' => 1, 'date_cloture' => now()->subDays(20)->toDateString()]
        );
    }
}
