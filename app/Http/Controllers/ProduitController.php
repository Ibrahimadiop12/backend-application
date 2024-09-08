<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
     // Liste tous les produits
     public function index()
     {
         $produits = Produit::with('categorie')->get();
         return response()->json($produits, 200);
     }
      ///produits vendeur

      public function indexV()
      {
          // Récupérer uniquement les produits dont le statut est 'publié'
          $produits = Produit::with('categorie')
                      ->where('statut', 'publier')
                      ->get();
      
          return response()->json($produits, 200);
      }
      
     // Crée un nouveau produit
     public function store(Request $request)
{
    // Validation des données
    $request->validate([
        'nom' => 'required|string|max:255',
        'libelle' => 'required|string|max:255',
        'categorie_id' => 'required|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'statut' => 'sometimes|in:publier,archiver', // Le statutest optionnel, avec des valeurs prédéfinies
    ]);

    // Gestion de l'image si elle est présente
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('public/images/produits', $filename);
            $imagePath = '/storage/images/produits/' . $filename;
        }

    // Création du produit avec le statut par défaut "publie" s'il n'est pas fourni
    $produit = Produit::create([
        'nom' => $request->nom,
        'libelle' => $request->libelle,
        'image' => $imagePath,
        'categorie_id' => $request->categorie_id,
        'statut' => $request->input('statut', 'publier'), // Valeur par défaut "publie"
    ]);

    return response()->json([
        'message' => 'Produit créé avec succès!',
        'produit' => $produit
    ], 201);
}



     // Met à jour un produit spécifique
     public function update(Request $request, $id)
     {
         // Validation des données
         $request->validate([
             'nom' => 'required|string|max:255',
             'libelle' => 'required|string|max:255',
             'categorie_id' => 'required|exists:categories,id',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
             'statut' => 'sometimes|in:publier,archiver', // Validation pour le statut, avec valeurs prédéfinies
         ]);

         // Recherche du produit
         $produit = Produit::find($id);

         // Vérification si le produit existe
         if (!$produit) {
             return response()->json(['message' => 'Produit non trouvé'], 404);
         }

         // Gestion de l'image si une nouvelle est envoyée
         $imagePath = $produit->image;
         if ($request->hasFile('image')) {
             // Suppression de l'ancienne image si elle existe
             if ($imagePath && Storage::exists('public/images/' . $imagePath)) {
                 Storage::delete('public/images/' . $imagePath);
             }

             // Stockage de la nouvelle image
             $image = $request->file('image');
             $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();
             $imagePath = $image->storeAs('public/images', $imageName);
         }

         // Mise à jour du produit
         $produit->update([
             'nom' => $request->nom,
             'libelle' => $request->libelle,
             'image' => $imagePath ? basename($imagePath) : $produit->image,
             'categorie_id' => $request->categorie_id,
             'statut' => $request->input('statut', $produit->statut), // Mise à jour du statut ou conservation de l'ancien
         ]);

         return response()->json([
             'message' => 'Produit mis à jour avec succès!',
             'produit' => $produit
         ], 200);
     }


     // Supprime un produit spécifique
     public function destroy($id)
     {
         $produit = Produit::find($id);

         if (!$produit) {
             return response()->json(['message' => 'Produit non trouvé'], 404);
         }

         // Supprimer l'image associée si elle existe
         if ($produit->image && Storage::exists('public/images/' . $produit->image)) {
             Storage::delete('public/images/' . $produit->image);
         }

         $produit->delete();

         return response()->json(['message' => 'Produit supprimé avec succès'], 200);
     }

     // Récupère l'URL de l'image
     public function image($filename)
     {
         $path = storage_path('app/public/images/' . $filename);

         if (!file_exists($path)) {
             abort(404);
         }

         return response()->file($path);
     }

     // Archive un produit spécifique
    public function archiver($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $produit->statut = 'archiver';
        $produit->save();

        return response()->json([
            'message' => 'Produit archivé avec succès!',
            'produit' => $produit
        ], 200);
    }

     // Publie un produit spécifique
     public function publier($id)
     {
         $produit = Produit::find($id);
 
         if (!$produit) {
             return response()->json(['message' => 'Produit non trouvé'], 404);
         }
 
         $produit->statut = 'publier';
         $produit->save();
 
         return response()->json([
             'message' => 'Produit publié avec succès!',
             'produit' => $produit
         ], 200);
     }
 

}
