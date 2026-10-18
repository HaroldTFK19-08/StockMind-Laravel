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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('element_id')->constrained('elements');
            $table->foreignId('apprentice_id')->constrained('apprentices');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->dateTime('fecha_asignacion')->useCurrent();
            $table->date('fecha_devolucion_esperada');
            $table->dateTime('fecha_devolucion_real')->nullable();
            $table->enum('estado', ['activa', 'devuelta', 'vencida'])->default('activa');
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
