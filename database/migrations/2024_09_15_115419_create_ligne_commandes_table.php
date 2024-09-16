<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ligne_commandes', function (Blueprint $table) {
            $table->id();
            $table->integer('quantite'); // Quantité de produit
            $table->decimal('prixUnitaire', 8, 2); // Prix unitaire
            $table->foreignId('declaration_id')->constrained('declarations');
            $table->foreignId('commande_id')->constrained('commandes'); // Lien avec la commande // Référence à la déclaration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_commandes');
    }
};
