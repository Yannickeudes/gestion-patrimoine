<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index()
    {
        return Categorie::withCount('actifs')->orderBy('nom')->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
            'type' => ['required', Rule::in(Categorie::TYPES)],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(Categorie::create($data), 201);
    }

    public function show(Categorie $categorie)
    {
        return $categorie->loadCount('actifs');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'nom')->ignore($categorie->id)],
            'type' => ['sometimes', Rule::in(Categorie::TYPES)],
            'description' => ['nullable', 'string'],
        ]);

        $categorie->update($data);

        return $categorie;
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->actifs()->exists()) {
            return response()->json(['message' => 'Impossible : la categorie contient des actifs.'], 422);
        }

        $categorie->delete();

        return response()->json(null, 204);
    }
}
