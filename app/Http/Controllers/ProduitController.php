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
 
     // Crée un nouveau produit
     public function store(Request $request)
     {
         $request->validate([
             'libelle' => 'required|string|max:255',
             'categorie_id' => 'required|exists:categories,id',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
 
         $imagePath = null;
         if ($request->hasFile('image')) {
             $image = $request->file('image');
             $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();
             $imagePath = $image->storeAs('public/images', $imageName);
         }
 
         $produit = Produit::create([
             'libelle' => $request->libelle,
             'image' => $imagePath ? basename($imagePath) : null,
             'categorie_id' => $request->categorie_id,
         ]);
 
         return response()->json([
             'message' => 'Produit créé avec succès!',
             'produit' => $produit
         ], 201);
     }
 
     // Affiche un produit spécifique
     public function show($id)
     {
         $produit = Produit::with('categorie')->find($id);
 
         if (!$produit) {
             return response()->json(['message' => 'Produit non trouvé'], 404);
         }
 
         return response()->json($produit, 200);
     }
 
     // Met à jour un produit spécifique
     public function update(Request $request, $id)
     {
         $request->validate([
             'libelle' => 'required|string|max:255',
             'categorie_id' => 'required|exists:categories,id',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
 
         $produit = Produit::find($id);
 
         if (!$produit) {
             return response()->json(['message' => 'Produit non trouvé'], 404);
         }
 
         $imagePath = $produit->image;
         if ($request->hasFile('image')) {
             // Supprimer l'ancienne image si elle existe
             if ($imagePath && Storage::exists('public/images/' . $imagePath)) {
                 Storage::delete('public/images/' . $imagePath);
             }
 
             $image = $request->file('image');
             $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();
             $imagePath = $image->storeAs('public/images', $imageName);
         }
 
         $produit->update([
             'libelle' => $request->libelle,
             'image' => $imagePath ? basename($imagePath) : $produit->image,
             'categorie_id' => $request->categorie_id,
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
}
