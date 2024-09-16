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
            $table->text('description',200);
            $table->string('tracabilite',50);
            $table->integer('quantite');
            $table->decimal('prix', 8, 2)->after('stock');
            $table->date('date_peremption');
            $table->enum('statut', ['publier','archiver'])->default('publier');
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
