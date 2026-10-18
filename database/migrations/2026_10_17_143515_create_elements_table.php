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
        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('environment_id')->nullable()->constrained('environments');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->string('codigo_inventario', 30)->unique();
            $table->string('nombre', 150);
            $table->string('img_elemento');
            $table->string('descripcion', 500)->nullable();
            $table->string('serial', 60)->nullable()->unique();
            $table->string('marca', 60)->nullable();
            $table->string('modelo', 60)->nullable();
            $table->enum('estado', ['disponible', 'asignado', 'prestado', 'en traslado', 'en mantenimiento', 'dado de baja'])
                    ->default('disponible');
            $table->enum('condicion', ['bueno', 'regular', 'malo'])->default('bueno');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elements');
    }
};
