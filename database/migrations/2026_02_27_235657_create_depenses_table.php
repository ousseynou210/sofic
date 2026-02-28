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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->date('date_depense');
            $table->string('categorie');
            $table->string('fournisseur')->nullable();
            $table->text('description');
            $table->decimal('montant', 12, 2);
            $table->enum('mode', ['ESPECES', 'WAVE', 'ORANGE_MONEY', 'VIREMENT', 'CHEQUE']);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('date_depense');
            $table->index('categorie');
            $table->index('mode');
            $table->index(['compte_id', 'date_depense']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
