<?php

use App\Models\Expedition;
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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->unique();
            $table->decimal('montant_total', 10, 2);
            $table->timestamp('dateCommande')->useCurrent();
            $table->enum('status', ['en cours', 'validée', 'expédiée', 'payée', 'annulée'])->default('en cours');
            $table->foreignId('client_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
