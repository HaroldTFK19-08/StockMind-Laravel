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
        Schema::create('trainingcenters', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('foto_centro');
            $table->string('foto_centro_public_id')->nullable();
            $table->string('regional')->default('Cauca');
            $table->string('ciudad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainingcenters');
    }
};
