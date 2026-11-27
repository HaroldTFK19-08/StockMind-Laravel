<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La migración original ya crea el enum con "Administrador" en bases nuevas.
     * Ésta sólo actualiza instalaciones existentes; en SQLite no hay nada que
     * hacer porque la columna se recreó con el valor incluido.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columna = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['roles', 'rol']
        );

        if (! $columna || str_contains($columna->COLUMN_TYPE, "'Administrador'")) {
            return;
        }

        DB::statement(
            "ALTER TABLE roles MODIFY rol ENUM('Aprendiz','Instructor','Cuenta-dante','Administrador') NOT NULL"
        );
    }

    public function down(): void
    {
        // No se revierte: dejaría a los usuarios administradores sin un rol válido.
    }
};
