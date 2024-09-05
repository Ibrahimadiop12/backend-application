<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    protected $fillable = ['libelle', 'image', 'categorie_id','statut'];

    // public function declarations()
    // {
    //     return $this->hasMany(Declaration::class);
    // }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}
