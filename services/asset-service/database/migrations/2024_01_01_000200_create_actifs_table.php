<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actifs', function (Blueprint $table) {
            $table->id();
            $table->string('numero_serie', 100)->unique();
            $table->string('designation');
            $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete();
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->date('date_acquisition');
            $table->decimal('valeur_acquisition', 15, 2);
            $table->decimal('valeur_actuelle', 15, 2)->nullable();
            $table->string('etat')->default('bon'); // neuf, bon, use, hors_service
            $table->string('statut')->default('en_service'); // en_service, maintenance, reforme, cession, sorti
            $table->foreignId('localisation_id')->nullable()->constrained('localisations')->nullOnDelete();
            $table->unsignedBigInteger('responsable_id')->nullable(); // id user cote auth-service
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('etat');
            $table->index('categorie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actifs');
    }
};
