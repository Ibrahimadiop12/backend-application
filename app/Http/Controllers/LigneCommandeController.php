<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Declaration;
use Illuminate\Http\Request;
use App\Models\LigneCommande;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LigneCommandeController extends Controller
{
    ///Méthode pour afficher les ligne de commande de chaque utilisteur
    // Méthode pour récupérer les lignes de commande de l'utilisateur connecté
 // Méthode pour récupérer les lignes de commande de l'utilisateur connecté avec les produits
public function getLignesCommandes()
{
    // Récupérer l'utilisateur connecté
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Récupérer toutes les commandes du client
    $commandes = Commande::where('client_id', $user->id)->get();

    if ($commandes->isEmpty()) {
        return response()->json(['message' => 'Aucune commande trouvée.'], 404);
    }

    // Récupérer toutes les lignes de commande pour les commandes du client avec les déclarations et les produits
    $lignesCommandes = LigneCommande::whereIn('commande_id', $commandes->pluck('id'))
        ->with(['declaration.produit']) // Charger les déclarations et les produits associés
        ->get();

    if ($lignesCommandes->isEmpty()) {
        return response()->json(['message' => 'Aucune ligne de commande trouvée.'], 404);
    }

    return response()->json($lignesCommandes, 200);
}



 public function compterLignesParUtilisateur()
 {
     // Récupère l'utilisateur actuellement connecté
     $utilisateurId = auth()->user()->id;
 
     // Compte les lignes de commande pour l'utilisateur connecté
     $nombreLignes = DB::table('ligne_commandes')
         ->join('commandes', 'ligne_commandes.commande_id', '=', 'commandes.id')
         ->where('commandes.client_id', $utilisateurId) // Filtrer par utilisateur connecté
         ->count(); // Compte le nombre de lignes de commande
 
     // Retourner le nombre de lignes sous forme de réponse JSON
     return response()->json(['nombre_lignes' => $nombreLignes]);
 }
    
    ///Méthode pour enregistrer une ligne de commande 
    public function store(Request $request)
{
     // Récupérer l'utilisateur connecté
     $user = Auth::user();

     if (!$user) {
         return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
     }
 
     // Valider la requête
     $request->validate([
         'declaration_id' => 'required|exists:declarations,id',
     ]);
 
     $declarationId = $request->input('declaration_id');
 
     // Récupérer les lignes de commande de la session pour l'utilisateur connecté
     $ligneCommandes = session()->get('ligne_commandes_' . $user->id, []);
 
     if (isset($ligneCommandes[$declarationId])) {
         // Si une ligne de commande existe déjà, incrémenter la quantité
         $ligneCommandes[$declarationId]['quantite'] += 1;
     } else {
         // Sinon, créer une nouvelle ligne de commande
         $declaration = Declaration::find($declarationId);
 
         $ligneCommandes[$declarationId] = [
             'declaration_id' => $declarationId,
             'prixUnitaire' => $declaration->prix,
             'quantite' => 1,
         ];
     }
 
     // Stocker les lignes de commande dans la session pour l'utilisateur connecté
     session()->put('ligne_commandes_' . $user->id, $ligneCommandes);
 
     return response()->json(['message' => 'Ligne de commande ajoutée avec succès.'], 200);
}


}
