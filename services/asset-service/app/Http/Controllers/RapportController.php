<?php

namespace App\Http\Controllers;

use App\Models\Actif;
use App\Models\Affectation;
use App\Models\Categorie;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    /** GET /rapports/patrimoine : statistiques globales du patrimoine */
    public function patrimoine()
    {
        $parStatut = Actif::select('statut', DB::raw('count(*) as total'), DB::raw('sum(valeur_actuelle) as valeur'))
            ->groupBy('statut')->get();

        $parCategorie = Categorie::withCount('actifs')
            ->withSum('actifs', 'valeur_actuelle')
            ->get();

        return response()->json([
            'total_actifs' => Actif::count(),
            'valeur_totale' => (float) Actif::sum('valeur_actuelle'),
            'valeur_acquisition_totale' => (float) Actif::sum('valeur_acquisition'),
            'par_statut' => $parStatut,
            'par_categorie' => $parCategorie,
            'affectations_en_cours' => Affectation::whereNull('date_fin')->count(),
        ]);
    }

    /** GET /rapports/actifs-dormants : en service mais sans affectation ni evaluation recente */
    public function actifsDormants()
    {
        $limite = now()->subMonths(12);

        $actifs = Actif::with('categorie:id,nom')
            ->where('statut', 'en_service')
            ->whereDoesntHave('affectations', fn ($q) => $q->whereNull('date_fin'))
            ->whereDoesntHave('evaluations', fn ($q) => $q->where('date_eval', '>=', $limite))
            ->orderBy('date_acquisition')
            ->get(['id', 'numero_serie', 'designation', 'date_acquisition', 'valeur_actuelle', 'categorie_id']);

        return response()->json([
            'count' => $actifs->count(),
            'actifs' => $actifs,
        ]);
    }
}
