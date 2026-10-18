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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambiente_origen_id')->constrained('environments');
            $table->foreignId('ambiente_destino_id')->constrained('environments');
            $table->foreignId('danteaccount_id')->constrained('danteaccounts');
            $table->string('motivo', 255);
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_traslado')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'completado', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
