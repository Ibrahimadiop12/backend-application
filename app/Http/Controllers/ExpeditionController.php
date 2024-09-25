<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpeditionController extends Controller
{
    public function store(Request $request)
{
    // Validation des données d'entrée si nécessaire
    $request->validate([
        'commande_id' => 'required|exists:commandes,id', // Vérifie que l'ID de commande existe
        // Ajoutez d'autres validations si nécessaire
    ]);

    // Création de l'expédition avec méthode de livraison par défaut
    $expedition = Expedition::create([
        'methode_livraison' => 'expresse', // Méthode de livraison par défaut
        'date_livraison' => Carbon::now(), // Date d'aujourd'hui
        'statut' => 'expédié', // Statut par défaut
        'frais_livraison' => 2000, // Frais de livraison par défaut
        'commande_id' => $request->commande_id, // ID de la commande
    ]);

    // Retourner une réponse appropriée
    return response()->json([
        'message' => 'Expédition créée avec succès.',
        'data' => $expedition
    ], 201);
}
}
