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
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->string('tracabilite');
            $table->integer('stock');
            $table->decimal('prix', 8, 2)->after('stock');
            $table->date('date_primature');
            $table->enum('statut', ['publie', 'en attente','annule'])->default('en attente');
            $table->foreignId('produit_id')->constrained();
            $table->foreignId('vendeur_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('declarations');
    }
};
