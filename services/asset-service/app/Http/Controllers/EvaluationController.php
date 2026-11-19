<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        return Evaluation::with('actif:id,numero_serie,designation')
            ->when($request->actif_id, fn ($q) => $q->where('actif_id', $request->actif_id))
            ->orderByDesc('date_eval')
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'actif_id' => ['required', 'exists:actifs,id'],
            'date_eval' => ['required', 'date'],
            'valeur_estimee' => ['nullable', 'numeric', 'min:0'],
            'etat' => ['nullable', Rule::in(\App\Models\Actif::ETATS)],
            'observation' => ['nullable', 'string'],
        ]);

        $data['eval_par'] = $request->attributes->get('auth_user')['id'] ?? null;

        $evaluation = Evaluation::create($data);

        // Met a jour la valeur actuelle de l'actif si fournie
        if (isset($data['valeur_estimee'])) {
            $evaluation->actif->update(['valeur_actuelle' => $data['valeur_estimee']]);
        }
        if (isset($data['etat'])) {
            $evaluation->actif->update(['etat' => $data['etat']]);
        }

        return response()->json($evaluation->load('actif'), 201);
    }

    public function show(Evaluation $evaluation)
    {
        return $evaluation->load('actif');
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();

        return response()->json(null, 204);
    }
}
