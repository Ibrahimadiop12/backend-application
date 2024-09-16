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
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->enum('methode_paiement', ['carte_bancaire', 'mobile_money', 'paypal', 'virement_bancaire']);
            $table->enum('type_paiement', ['plein', 'partiel']);
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->enum('statut', ['en_attente', 'validé', 'échoué'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglements');
    }
};
