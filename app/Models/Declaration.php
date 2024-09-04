<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Declaration extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'tracabilite',
        'stock',
        'prix',
        'date_primature',
        'statut',
        'produit_id',
        'vendeur_id'
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }

    public static function declareProduct($data)
    {
        // Ici, nous permettons de conserver les déclarations par vendeur
        $declaration = self::updateOrCreate(
            [
                'produit_id' => $data['produit_id'],
                'vendeur_id' => $data['vendeur_id'],
                'date_primature' => $data['date_primature'],
            ],
            $data
        );

        return $declaration;
    }
}
