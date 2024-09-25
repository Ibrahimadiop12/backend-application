<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .invoice {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px; /* Ajustez la taille du logo */
            margin-bottom: 10px;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items th, .items td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            text-align: left;
            margin-top: 20px;
        }
        .signature .client-signature {
            text-align: right;
        }
        .company-info, .client-info {
            margin-bottom: 20px;
        }
        .company-info {
            text-align: left;
        }
    </style>
</head>
<body>

<div class="invoice">
    <div class="header">
        <img src="{{ asset('path/to/logo.png') }}" alt="Logo de l'entreprise"> <!-- Chemin vers le logo -->
        <h1>Facture</h1>
        <p>Date: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>
    <div class="row">
        <div class="company-info">
            <h2>Informations de l'Entreprise</h2>
            <p>Nom de l'Entreprise:SEENTOOL</p>
            <p>Adresse de l'Entreprise:Parcelles Assainies Unité 1</p>
            <p>Téléphone: 221779816117</p>
            <p>Email: seentool2024@gmail.com</p>
        </div>

        <h2>Informations Client</h2>
        <div class="client-info">
            <p><strong>Nom du Client:</strong> {{ $commande->client->name }}</p>
            <p><strong>Email du Client:</strong> {{ $commande->client->email }}</p>
        </div>
    </div>



    <h2>Détails de la commande</h2>
    <table class="items">
        <tr>
            <th>Méthode de Paiement</th>
            <td>{{ $reglement->methode_paiement }}</td>
        </tr>
        <tr>
            <th>Type de Paiement</th>
            <td>{{ $reglement->type_paiement }}</td>
        </tr>
        <tr>
            <th>Numéro de Commande</th>
            <td>{{ $commande->numero_commande }}</td>
        </tr>
        <tr>
            <th>Date de Commande</th>
            <td>{{ \Carbon\Carbon::parse($commande->dateCommande)->format('d/m/Y') }}</td>
        </tr>

    </table>

    <h2>Détails des Lignes de Commande</h2>
    <table class="items">
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Total</th>
        </tr>
        @foreach ($commande->ligneCommandes as $ligne)
        <tr>
            <td>{{ $ligne->declaration->produit->nom }}</td>
            <td>{{ $ligne->quantite }}</td>
            <td>{{ number_format($ligne->prixUnitaire, 2, ',', ' ') }} XOF</td>
            <td>{{ number_format($ligne->prixUnitaire * $ligne->quantite, 2, ',', ' ') }} XOF</td>
        </tr>
        @endforeach
    </table>
    <div class="signature">
        <div class="company-signature">
            <p>Signature de l'entreprise :</p>
            <p>________________________</p>
        </div>
        <div class="client-signature">
            <p>Signature du client :</p>
            <p>________________________</p>
        </div>
    </div>

    <div class="footer">
        <p>Merci pour votre achat !</p>
    </div>
</div>

</body>
</html>
