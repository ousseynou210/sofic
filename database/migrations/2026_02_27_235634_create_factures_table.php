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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('commercial_id')->nullable()->constrained('commerciaux')->nullOnDelete();
            $table->foreignId('point_vente_id')->nullable()->constrained('points_vente')->nullOnDelete();
            $table->enum('statut', ['BROUILLON', 'ENVOYEE', 'PARTIELLE', 'PAYEE', 'ANNULEE'])->default('BROUILLON');
            $table->decimal('total_facture', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date_emission');
            $table->index('date_echeance');
            $table->index('statut');
            $table->index(['client_id', 'date_emission']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
