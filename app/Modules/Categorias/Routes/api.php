<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Categorias\Controllers\CategoriasController;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // ISMR-17: categorías de productos, gestión administrativa
    Route::apiResource('categorias', CategoriasController::class);
});
