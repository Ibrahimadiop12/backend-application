<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Declaration;
use Illuminate\Http\Request;

class DeclarationController extends Controller
{
   // Fonction pour créer ou mettre à jour la déclaration d'un produit
   public function store(Request $request)
   {
       $request->validate([
           'description' => 'required|string',
           'tracabilite' => 'required|string',
           'stock' => 'required|integer',
           'prix' => 'required|numeric',
           'date_primature' => 'required|date',
           'produit_id' => 'required|exists:produits,id',
           'vendeur_id' => 'required|exists:users,id',
           'statut' => 'required|string',
       ]);

       // Trouver ou créer la déclaration
       $declaration = Declaration::where([
           ['produit_id', '=', $request->produit_id],
           ['vendeur_id', '=', $request->vendeur_id],
           ['date_primature', '=', $request->date_primature],
       ])->first();

       if ($declaration) {
           // Si la déclaration existe déjà, mettre à jour le stock
           $declaration->stock += $request->stock;
           $declaration->prix = $request->prix; // mettre à jour le prix au besoin
           $declaration->statut = $request->statut;
           $declaration->save();
       } else {
           // Sinon, créer une nouvelle déclaration
           $declaration = Declaration::create([
               'description' => $request->description,
               'tracabilite' => $request->tracabilite,
               'stock' => $request->stock,
               'prix' => $request->prix,
               'date_primature' => $request->date_primature,
               'produit_id' => $request->produit_id,
               'vendeur_id' => $request->vendeur_id,
               'statut' => $request->statut,
           ]);
       }

       return response()->json([
           'message' => 'Déclaration ajoutée ou mise à jour avec succès!',
           'declaration' => $declaration
       ], 201);
   }

   // Fonction pour afficher les déclarations d'un produit avec le stock total
   public function show($id)
   {
       $produit = Produit::with('declarations.vendeur')->findOrFail($id);

       return response()->json([
           'produit' => $produit,
           'stock_total' => $produit->declarations->sum('stock'),
           'declarations' => $produit->declarations
       ], 200);
   }

   // Fonction pour récupérer les déclarations d'un vendeur spécifique
   public function showByVendeur($vendeur_id)
   {
       $declarations = Declaration::where('vendeur_id', $vendeur_id)
           ->with('produit')
           ->get();

       return response()->json([
           'vendeur_id' => $vendeur_id,
           'declarations' => $declarations
       ], 200);
   }
}
