<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Centros\Controllers\CentrosController;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // Administración de estructura institucional
    Route::apiResource('centros', CentrosController::class);
});
