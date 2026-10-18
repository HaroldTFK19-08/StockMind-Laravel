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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprentice_id')->constrained('apprentices');
            $table->enum('tipo', ['daño', 'perdida', 'falla', 'otro']);
            $table->string('descripcion', 500);
            $table->dateTime('fecha_reporte')->useCurrent();
            $table->string('foto_probatorio');
            $table->enum('estado', ['pendiente', 'en_revision', 'resuelto'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
