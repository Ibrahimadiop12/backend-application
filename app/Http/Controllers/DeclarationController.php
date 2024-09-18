<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Declaration;
use Illuminate\Http\Request;

class DeclarationController extends Controller
{
    public function index()
    {
        // Récupérer toutes les déclarations avec les relations nécessaires
        $declarations = Declaration::with(['produit.categorie', 'vendeur'])->get();
    
        return response()->json([
            'declarations' => $declarations
        ], 200);
    }
    


   // Fonction pour créer ou mettre à jour la déclaration d'un produit
   public function store(Request $request)
   {
       $request->validate([
           'description' => 'required|string',
           'tracabilite' => 'required|string',
           'quantite' => 'required|integer',
           'prix' => 'required|numeric',
           'date_peremption' => 'required|date',
           'produit_id' => 'required|exists:produits,id',
           'vendeur_id' => 'required|exists:users,id',
           'statut' => 'sometimes|in:publier,archiver',
       ]);

       // Trouver ou créer la déclaration
       $declaration = Declaration::where([
           ['produit_id', '=', $request->produit_id],
           ['vendeur_id', '=', $request->vendeur_id],
           ['date_peremption', '=', $request->date_peremption],
       ])->first();

       if ($declaration) {
           // Si la déclaration existe déjà, mettre à jour le stock
           $declaration->quantite += $request->quantite;
           $declaration->prix = $request->prix; // mettre à jour le prix au besoin
           $declaration->statut = $request->statut;
           $declaration->save();
       } else {
           // Sinon, créer une nouvelle déclaration
           $declaration = Declaration::create([
               'description' => $request->description,
               'tracabilite' => $request->tracabilite,
               'quantite' => $request->quantite,
               'prix' => $request->prix,
               'date_peremption' => $request->date_peremption,
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
    // Récupérer le produit avec les déclarations, le vendeur et la catégorie associée
    $produit = Produit::with(['declarations.vendeur', 'categorie'])->findOrFail($id);

    return response()->json([
        'produit' => $produit,
        'quantite_total' => $produit->declarations->sum('quantite'),
        'declarations' => $produit->declarations
    ], 200);
}


   // Fonction pour récupérer les déclarations d'un vendeur spécifique
   public function showByVendeur($vendeur_id)
   {
       $declarations = Declaration::where('vendeur_id', $vendeur_id)
           ->with('produit.categorie')
           ->get();

       return response()->json([
           'vendeur_id' => $vendeur_id,
           'declarations' => $declarations
       ], 200);
   }

   // DeclarationController.php

   public function updateStatut(Request $request, $vendeur_id, $id)
   {
       // Valider les données d'entrée
       $request->validate([
           'statut' => 'required|in:publier,archiver',
       ]);

       // Trouver la déclaration
       $declaration = Declaration::where([
           ['id', '=', $id],
           ['vendeur_id', '=', $vendeur_id],
       ])->firstOrFail();

       // Mettre à jour le statut
       $declaration->statut = $request->statut;
       $declaration->save();

       return response()->json([
           'message' => 'Statut mis à jour avec succès!',
           'declaration' => $declaration
       ], 200);
   }


}
