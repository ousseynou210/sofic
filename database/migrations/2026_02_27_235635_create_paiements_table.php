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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->foreignId('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->date('date_paiement');
            $table->decimal('montant', 12, 2);
            $table->enum('mode', ['ESPECES', 'WAVE', 'ORANGE_MONEY', 'VIREMENT', 'CHEQUE']);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('date_paiement');
            $table->index('mode');
            $table->index(['facture_id', 'date_paiement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
