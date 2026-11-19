<?php

namespace App\Http\Controllers;

use App\Models\Actif;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActifController extends Controller
{
    public function index(Request $request)
    {
        return Actif::with(['categorie:id,nom,type', 'localisation:id,region,commune'])
            ->when($request->statut, fn ($q) => $q->where('statut', $request->statut))
            ->when($request->etat, fn ($q) => $q->where('etat', $request->etat))
            ->when($request->categorie_id, fn ($q) => $q->where('categorie_id', $request->categorie_id))
            ->when($request->localisation_id, fn ($q) => $q->where('localisation_id', $request->localisation_id))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('designation', 'like', '%'.$request->search.'%')
                      ->orWhere('numero_serie', 'like', '%'.$request->search.'%')
                      ->orWhere('marque', 'like', '%'.$request->search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(min((int) $request->per_page, 100) ?: 20);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Par defaut : valeur actuelle = valeur d'acquisition
        $data['valeur_actuelle'] ??= $data['valeur_acquisition'] ?? null;
        $data['statut'] ??= 'en_service';

        return response()->json(
            Actif::create($data)->load('categorie', 'localisation'), 201
        );
    }

    public function show(Actif $actif)
    {
        return $actif->load(['categorie', 'localisation', 'affectations', 'evaluations']);
    }

    public function update(Request $request, Actif $actif)
    {
        $actif->update($this->validated($request, partial: true));

        return $actif->load('categorie', 'localisation');
    }

    public function destroy(Actif $actif)
    {
        if ($actif->affectations()->whereNull('date_fin')->exists()) {
            return response()->json(['message' => 'Impossible : une affectation est en cours.'], 422);
        }

        $actif->delete();

        return response()->json(null, 204);
    }

    /** POST /actifs/{actif}/statut  { "statut": "maintenance", "motif": "..." } */
    public function changeStatut(Request $request, Actif $actif)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in(Actif::STATUTS)],
            'motif' => ['nullable', 'string', 'max:1000'],
        ]);

        $ancien = $actif->statut;
        $actif->update(['statut' => $data['statut']]);

        return response()->json([
            'message' => "Statut change : {$ancien} -> {$data['statut']}",
            'motif' => $data['motif'] ?? null,
            'actif' => $actif->fresh(['categorie', 'localisation']),
        ]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'numero_serie' => [$req, 'string', 'max:100', Rule::unique('actifs', 'numero_serie')->ignore($request->route('actif'))],
            'designation' => [$req, 'string', 'max:255'],
            'categorie_id' => [$req, 'exists:categories,id'],
            'marque' => ['nullable', 'string', 'max:100'],
            'modele' => ['nullable', 'string', 'max:100'],
            'date_acquisition' => [$req, 'date'],
            'valeur_acquisition' => [$req, 'numeric', 'min:0'],
            'valeur_actuelle' => ['nullable', 'numeric', 'min:0'],
            'etat' => [$req, Rule::in(Actif::ETATS)],
            'statut' => ['nullable', Rule::in(Actif::STATUTS)],
            'localisation_id' => ['nullable', 'exists:localisations,id'],
            'responsable_id' => ['nullable', 'integer'], // id user cote auth-service
            'description' => ['nullable', 'string'],
        ]);
    }
}
