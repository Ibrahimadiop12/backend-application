<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    use HasFactory;
    protected $fillable = ['methode_livraison', 'date_livraison', 'statut', 'frais_livraison'];

    public function commande()
    {
        return $this->hasOne(Commande::class); // Une expédition est liée à une commande
    }
}
