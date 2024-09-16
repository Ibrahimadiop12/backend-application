<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory;
    protected $fillable = [
        'numero_commande',
        'montant_total',
        'date',
        'status',
        'client_id',
        'expedition_id',
        'reglement_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commande) {
            // Générer un numéro de commande unique avant de sauvegarder
            $commande->numero_commande = 'CMD-' . Str::upper(Str::random(8));
        });
    }

    public function client()
    {
        return $this->belongsTo(User::class);
    }

    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    public function reglement()
    {
        return $this->belongsTo(Reglement::class);
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommande::class);
    }
}
