<?php

namespace App\Http\Controllers;

use App\Models\Localisation;
use Illuminate\Http\Request;

class LocalisationController extends Controller
{
    public function index(Request $request)
    {
        return Localisation::withCount('actifs')
            ->when($request->region, fn ($q) => $q->where('region', 'like', '%'.$request->region.'%'))
            ->orderBy('region')->orderBy('commune')
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'region' => ['required', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'batiment' => ['nullable', 'string', 'max:255'],
            'etage' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(Localisation::create($data), 201);
    }

    public function show(Localisation $localisation)
    {
        return $localisation->loadCount('actifs');
    }

    public function update(Request $request, Localisation $localisation)
    {
        $data = $request->validate([
            'region' => ['sometimes', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'batiment' => ['nullable', 'string', 'max:255'],
            'etage' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $localisation->update($data);

        return $localisation;
    }

    public function destroy(Localisation $localisation)
    {
        if ($localisation->actifs()->exists()) {
            return response()->json(['message' => 'Impossible : la localisation contient des actifs.'], 422);
        }

        $localisation->delete();

        return response()->json(null, 204);
    }
}
