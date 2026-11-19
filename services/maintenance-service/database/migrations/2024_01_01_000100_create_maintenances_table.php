<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actif_id'); // reference logique vers asset-service
            $table->string('type'); // preventive, corrective
            $table->date('date_demande');
            $table->text('description');
            $table->decimal('cout', 15, 2)->nullable();
            $table->string('statut')->default('ouvert'); // ouvert, en_cours, clos
            $table->unsignedBigInteger('demande_par')->nullable();
            $table->unsignedBigInteger('clos_par')->nullable();
            $table->date('date_cloture')->nullable();
            $table->timestamps();

            $table->index(['actif_id', 'statut']);
            $table->index('date_demande');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
