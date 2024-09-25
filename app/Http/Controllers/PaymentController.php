<?php



namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Http\Request;
use App\Models\Reglement;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        // Valider les entrées
        $request->validate([
            'totalAmount' => 'required|numeric',
            'email' => 'required|email',
            'name' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'xof',
                    'product_data' => [
                        'name' => 'Total de la commande',
                    ],
                    'unit_amount' => $request->totalAmount, // Montant en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost:4200/panier?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:4200/panier?payment_status=cancelled',
            'customer_email' => $request->email,
            'metadata' => [
                'customer_name' => $request->name // Ajouter le nom dans les métadonnées
            ]
        ]);

        return response()->json(['sessionId' => $session->id]);
    }

    // Méthode pour un paiement réussi
    public function paymentSuccess(Request $request)
    {
        // Récupérer l'ID de la session de paiement
        $sessionId = $request->query('session_id');

        // Optionnel : Logique pour enregistrer les informations du paiement
        // ...

        // Rediriger vers l'URL Angular avec le session_id
        return redirect()->away('http://localhost:4200/panier?session_id=' . $sessionId);
    }

    // Méthode pour un paiement annulé
    public function paymentCancel(Request $request)
    {
        // Rediriger vers Angular avec un message d'annulation
        return redirect()->away('http://localhost:4200/panier?payment_status=cancelled');
    }
}
