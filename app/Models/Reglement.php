<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reglement extends Model
{
    use HasFactory;
    protected $fillable = ['methode_paiement', 'type_paiement', 'montant', 'date_paiement', 'statut','user_id','commande_id'];

    // Casts pour les énumérations

    public function commande()
    {
        return $this->hasOne(Commande::class); // Un règlement est lié à une commande
    }


}
