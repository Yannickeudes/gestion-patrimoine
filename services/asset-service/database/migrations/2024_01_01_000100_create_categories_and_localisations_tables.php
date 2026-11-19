<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('type'); // immobilier, vehicule, informatique, mobilier, autre
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('localisations', function (Blueprint $table) {
            $table->id();
            $table->string('region');
            $table->string('commune')->nullable();
            $table->string('adresse')->nullable();
            $table->string('batiment')->nullable();
            $table->string('etage')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localisations');
        Schema::dropIfExists('categories');
    }
};
