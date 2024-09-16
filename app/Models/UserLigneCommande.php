<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLigneCommande extends Model
{
    use HasFactory;
    
    protected $fillable = [ 'user_id', 'ligne_commande_id'];
}
