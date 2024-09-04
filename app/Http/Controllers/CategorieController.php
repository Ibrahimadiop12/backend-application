<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    // Liste toutes les catégories
    public function index()
    {
        $categories = Categorie::all();
        return response()->json($categories, 200);
    }

    // Crée une nouvelle catégorie
    public function store(Request $request)
    {
        $data = $request->all();
        $burger = Categorie::create($data);
        return response()->json($burger, 201);
    }

    // Affiche une catégorie spécifique
    public function show($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        return response()->json($categorie, 200);
    }

    // Met à jour une catégorie spécifique
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomCategorie' => 'required|string|max:255',
        ]);

        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $categorie->update([
            'nomCategorie' => $request->nomCategorie
        ]);

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès!',
            'categorie' => $categorie
        ], 200);
    }

    // Supprime une catégorie spécifique
    public function destroy($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $categorie->delete();

        return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
    }
}
