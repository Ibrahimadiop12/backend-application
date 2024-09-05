<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'nomCategorie' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image est nullable
            'statut' => 'sometimes|string|in:publie,archive', // Permet de définir le statut ou prendre la valeur par défaut
        ]);
    
        // Traitement de l'upload de l'image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('public/images/categories', $filename);
            $imagePath = '/storage/images/categories/' . $filename;
        }
    
        // Définir le statut par défaut à "publie" si non fourni
        $statut = $request->input('statut', 'publie');
    
        // Créer la catégorie avec les données validées
        $categorie = Categorie::create([
            'nomCategorie' => $validatedData['nomCategorie'],
            'image' => $imagePath, // Peut être null si aucune image n'est fournie
            'statut' => $statut,
        ]);
    
        return response()->json([
            'message' => 'Catégorie créée avec succès!',
            'categorie' => $categorie
        ], 201);
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
        // Validation des données
        $request->validate([
            'nomCategorie' => 'required|string|max:255',
            'statut' => 'sometimes|in:publie,archive', // Validation pour le statut
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation de l'image
        ]);
    
        // Recherche de la catégorie
        $categorie = Categorie::find($id);
    
        // Vérification si la catégorie existe
        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }
    
        // Gestion de l'image
        $imagePath = $categorie->image;
        if ($request->hasFile('image')) {
            // Suppression de l'ancienne image si elle existe
            if ($imagePath && Storage::exists('public/images/' . $imagePath)) {
                Storage::delete('public/images/' . $imagePath);
            }
    
            // Sauvegarde de la nouvelle image
            $image = $request->file('image');
            $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/images', $imageName);
        }
    
        // Mise à jour de la catégorie
        $categorie->update([
            'nomCategorie' => $request->nomCategorie,
            'statut' => $request->input('statut', $categorie->statut), // Mise à jour du statut ou conservation de l'ancien
            'image' => $imagePath ? basename($imagePath) : $categorie->image, // Mise à jour de l'image ou conservation de l'ancienne
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
         // Supprimer l'image associée si elle existe
         if ($categorie->image && Storage::exists('public/images/' . $categorie->image)) {
            Storage::delete('public/images/' . $categorie->image);
        }
        $categorie->delete();

        return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
    }
}
