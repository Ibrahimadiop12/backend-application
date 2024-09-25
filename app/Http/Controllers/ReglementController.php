<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Mail\InvoiceMail;
use App\Models\Reglement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ReglementController extends Controller
{
    public function storePayment(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'methode_paiement' => 'required|string',
            'type_paiement' => 'required|string',
            'montant' => 'required|numeric',
            'commande_id' => 'required|exists:commandes,id',  // Assurez-vous que la commande existe
        ]);

        // Retourner les erreurs de validation si présentes
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Récupération de l'utilisateur authentifié
        $user = Auth::user();
        if ($user === null) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 404);
        }

        // Récupérer la commande spécifique avec ses lignes de commande
        $commande = Commande::with('ligneCommandes.declaration')->find($request->input('commande_id'));

        if (!$commande) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        try {
            // Création du règlement
            $reglement = Reglement::create([
                'methode_paiement' => $request->input('methode_paiement'),
                'type_paiement' => $request->input('type_paiement'),
                'montant' => $request->input('montant'),
                'date_paiement' => now(),
                'statut' => 'validé', // Changez le statut à 'validé'
                'user_id' => $user->id,
                'commande_id' => $commande->id,  // Lier le règlement à la commande
            ]);

            // Mettre à jour l'état de la commande
            $commande->status = 'validée'; // Changez l'état de la commande
            $commande->save(); // Enregistrez la commande mise à jour

            // Générer le PDF pour la facture
            $pdf = app('dompdf.wrapper')->loadView('invoice', compact('reglement', 'commande'));
            $pdfOutput = $pdf->output();

            // Envoi de l'e-mail avec la facture en pièce jointe
            Mail::to($user->email)->send(new InvoiceMail($reglement, $pdfOutput));

            return response()->json([
                'message' => 'Paiement enregistré avec succès',
                'reglement' => $reglement,
                'lignes_commande' => $commande->ligneCommandes,  // Inclure les lignes de commande dans la réponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
