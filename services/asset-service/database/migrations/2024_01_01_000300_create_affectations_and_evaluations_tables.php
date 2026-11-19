<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actif_id')->constrained('actifs')->cascadeOnDelete();
            $table->string('service_destinataire');
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // NULL = affectation en cours
            $table->unsignedBigInteger('observe_par')->nullable(); // id user cote auth-service
            $table->timestamps();

            $table->index(['actif_id', 'date_fin']);
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actif_id')->constrained('actifs')->cascadeOnDelete();
            $table->date('date_eval');
            $table->decimal('valeur_estimee', 15, 2)->nullable();
            $table->string('etat')->nullable();
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('eval_par')->nullable(); // id user cote auth-service
            $table->timestamps();

            $table->index(['actif_id', 'date_eval']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('affectations');
    }
};
