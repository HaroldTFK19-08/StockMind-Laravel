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
        Schema::create('motions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->nullable()->unique()->constrained('transfers'); // 1:1 con traslados
            $table->enum('tipo', ['traslado', 'Ingreso', 'Retiro', 'Baja']);
            $table->string('motivo', 255);
            $table->dateTime('fecha_movimiento')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motions');
    }
};
