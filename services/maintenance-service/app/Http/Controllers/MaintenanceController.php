<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Support\AssetClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        return Maintenance::query()
            ->when($request->actif_id, fn ($q) => $q->where('actif_id', $request->actif_id))
            ->when($request->statut, fn ($q) => $q->where('statut', $request->statut))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->orderByDesc('date_demande')
            ->paginate(min((int) $request->per_page, 100) ?: 20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'actif_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(Maintenance::TYPES)],
            'date_demande' => ['required', 'date'],
            'description' => ['required', 'string'],
            'cout' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Verification inter-services : l'actif doit exister dans asset-service
        if (! AssetClient::actifExiste($data['actif_id'], $request->bearerToken())) {
            return response()->json(['message' => 'Actif introuvable dans le service assets.'], 422);
        }

        $user = $request->attributes->get('auth_user');
        $data['demande_par'] = $user['id'] ?? null;
        $data['statut'] = 'ouvert';

        return response()->json(Maintenance::create($data), 201);
    }

    public function show(Maintenance $maintenance)
    {
        return $maintenance;
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $data = $request->validate([
            'type' => ['sometimes', Rule::in(Maintenance::TYPES)],
            'description' => ['sometimes', 'string'],
            'cout' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['sometimes', Rule::in(Maintenance::STATUTS)],
        ]);

        if (($data['statut'] ?? null) === 'clos' && $maintenance->statut !== 'clos') {
            $data['date_cloture'] = now()->toDateString();
            $data['clos_par'] = $request->attributes->get('auth_user')['id'] ?? null;
        }

        $maintenance->update($data);

        return $maintenance;
    }

    public function destroy(Maintenance $maintenance)
    {
        if ($maintenance->statut === 'clos') {
            return response()->json(['message' => 'Impossible de supprimer une maintenance close.'], 422);
        }

        $maintenance->delete();

        return response()->json(null, 204);
    }

    /** GET /stats : statistiques de maintenance */
    public function stats()
    {
        return response()->json([
            'total' => Maintenance::count(),
            'par_statut' => Maintenance::select('statut', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('statut')->get(),
            'par_type' => Maintenance::select('type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('type')->get(),
            'cout_total' => (float) Maintenance::where('statut', 'clos')->sum('cout'),
        ]);
    }
}
