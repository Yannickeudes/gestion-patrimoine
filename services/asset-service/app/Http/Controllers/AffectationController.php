<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    public function index(Request $request)
    {
        return Affectation::with('actif:id,numero_serie,designation,statut')
            ->when($request->actif_id, fn ($q) => $q->where('actif_id', $request->actif_id))
            ->when($request->en_cours === '1', fn ($q) => $q->whereNull('date_fin'))
            ->orderByDesc('date_debut')
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'actif_id' => ['required', 'exists:actifs,id'],
            'service_destinataire' => ['required', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        // Une seule affectation en cours par actif
        $enCours = Affectation::where('actif_id', $data['actif_id'])->whereNull('date_fin')->exists();
        if ($enCours) {
            return response()->json(['message' => 'Cet actif a deja une affectation en cours.'], 422);
        }

        $data['observe_par'] = $request->attributes->get('auth_user')['id'] ?? null;

        return response()->json(Affectation::create($data)->load('actif'), 201);
    }

    public function show(Affectation $affectation)
    {
        return $affectation->load('actif');
    }

    /** Cloturer une affectation : PATCH /affectations/{id}/cloturer */
    public function cloturer(Request $request, Affectation $affectation)
    {
        if ($affectation->date_fin) {
            return response()->json(['message' => 'Affectation deja cloturee.'], 422);
        }

        $data = $request->validate(['date_fin' => ['nullable', 'date']]);
        $affectation->update(['date_fin' => $data['date_fin'] ?? now()->toDateString()]);

        return $affectation->load('actif');
    }

    public function destroy(Affectation $affectation)
    {
        $affectation->delete();

        return response()->json(null, 204);
    }
}
