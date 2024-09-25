<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Declaration;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LigneCommande;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{

    // Lister toutes les commandes d'un client
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $commandes = Commande::where('client_id', $user->id)->with('lignesCommandes.declaration')->get();

        return response()->json($commandes, 200);
    }

    public function store(Request $request)
{
    // Vérifier si l'utilisateur est authentifié
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Validation des données de la requête
    $request->validate([
        'declaration_id' => 'required|exists:declarations,id',
    ]);

    // Récupérer l'ID de la déclaration depuis la requête
    $declarationId = $request->input('declaration_id');
    $declaration = Declaration::find($declarationId);

    // Récupérer ou créer une commande pour l'utilisateur
    $commande = Commande::firstOrCreate(
        ['client_id' => $user->id, 'status' => 'en cours'],
        [
            'numero_commande' => 'CMD-' . strtoupper(Str::random(8)),
            'montant_total' => 0, // À mettre à jour après ajout de toutes les lignes
            'dateCommande' => now() // Date et heure actuelles
        ]
    );

    // Chercher la ligne de commande existante ou en créer une nouvelle
    $ligneCommande = LigneCommande::where('commande_id', $commande->id)
                                   ->where('declaration_id', $declarationId)
                                   ->first();

    if ($ligneCommande) {
        // Incrémenter la quantité si la ligne de commande existe déjà
        $ligneCommande->increment('quantite');
    } else {
        // Créer une nouvelle ligne de commande avec une quantité initiale de 1
        $ligneCommande = LigneCommande::create([
            'quantite' => 1,
            'prixUnitaire' => $declaration->prix,
            'declaration_id' => $declarationId,
            'commande_id' => $commande->id
        ]);
    }

    $montantTotal = LigneCommande::where('commande_id', $commande->id)
                             ->sum(DB::raw('quantite * "prixUnitaire"'));


    // Mettre à jour le montant total de la commande
    $commande->update(['montant_total' => $montantTotal]);

    return response()->json(['message' => 'Ligne de commande ajoutée avec succès.'], 200);
}

///Cette fonction permet d'incrémenter la quantité
public function incrementerQuantite(Request $request)
{
    // Vérifier si l'utilisateur est authentifié
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Validation des données de la requête
    $request->validate([
        'ligne_commande_id' => 'required|exists:ligne_commandes,id',
    ]);

    // Récupérer la ligne de commande
    $ligneCommande = LigneCommande::find($request->input('ligne_commande_id'));

    if (!$ligneCommande) {
        return response()->json(['message' => 'Ligne de commande introuvable.'], 404);
    }

    // Incrémenter la quantité
    $ligneCommande->increment('quantite');

    // Mettre à jour le montant total de la commande associée
    $this->mettreAJourMontantTotal($ligneCommande->commande_id);

    return response()->json(['message' => 'Quantité incrémentée avec succès.', 'quantite' => $ligneCommande->quantite], 200);
}

    ///Cette fonction permet de décrémenter la quantité
    public function decrementerQuantite(Request $request)
{
    // Vérifier si l'utilisateur est authentifié
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Validation des données de la requête
    $request->validate([
        'ligne_commande_id' => 'required|exists:ligne_commandes,id',
    ]);

    // Récupérer la ligne de commande
    $ligneCommande = LigneCommande::find($request->input('ligne_commande_id'));

    if (!$ligneCommande) {
        return response()->json(['message' => 'Ligne de commande introuvable.'], 404);
    }

    // Vérifier que la quantité est supérieure à 1 avant de décrémenter
    if ($ligneCommande->quantite > 1) {
        $ligneCommande->decrement('quantite');
    } else {
        return response()->json(['message' => 'La quantité ne peut pas être inférieure à 1.'], 400);
    }

    // Mettre à jour le montant total de la commande associée
    $this->mettreAJourMontantTotal($ligneCommande->commande_id);

    return response()->json(['message' => 'Quantité décrémentée avec succès.', 'quantite' => $ligneCommande->quantite], 200);
}
///Cette fonction permet de mettre à jour le montont total

private function mettreAJourMontantTotal($commandeId)
{
    // Recalculer le montant total de la commande
    $montantTotal = LigneCommande::where('commande_id', $commandeId)
                                 ->sum(DB::raw('quantite * "prixUnitaire"'));

    // Mettre à jour le montant total de la commande
    $commande = Commande::find($commandeId);
    $commande->update(['montant_total' => $montantTotal]);
}

public function supprimerLigneCommande(Request $request)
{
    // Vérifier si l'utilisateur est authentifié
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Validation des données de la requête
    $request->validate([
        'ligne_commande_id' => 'required|exists:ligne_commandes,id',
    ]);

    // Récupérer la ligne de commande
    $ligneCommande = LigneCommande::find($request->input('ligne_commande_id'));

    if (!$ligneCommande) {
        return response()->json(['message' => 'Ligne de commande introuvable.'], 404);
    }

    // Supprimer la ligne de commande
    $ligneCommande->delete();

    // Mettre à jour le montant total de la commande associée
    $this->mettreAJourMontantTotal($ligneCommande->commande_id);

    return response()->json(['message' => 'Ligne de commande supprimée avec succès.'], 200);
}

// CommandeController.php
public function viderPanier($userId)
{
    // Supprimer les lignes de commande associées à l'utilisateur
    LigneCommande::where('user_id', $userId)->delete();

    // Retourner une réponse
    return response()->json(['message' => 'Le panier a été vidé avec succès.'], 200);
}

public function show($id)
{
    // Récupérer la commande avec ses lignes de commande
    $commande = Commande::with('ligneCommandes')->find($id);

    if (!$commande) {
        return response()->json(['message' => 'Commande non trouvée'], 404);
    }

    return response()->json($commande);
}


}
