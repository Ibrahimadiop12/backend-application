<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    use HasFactory;

    protected $fillable = [ 'quantite', 'prixUnitaire','declaration_id','commande_id'];

     //Relation avec le model commande
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

     // Relation avec le modèle Declaration
     public function declaration()
     {
         return $this->belongsTo(Declaration::class);
     }
}
