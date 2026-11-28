<?php

use App\Modules\Fichas\Controllers\FichasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // Administración de fichas/grupos
    Route::apiResource('fichas', FichasController::class)
        ->parameters(['fichas' => 'ficha']);
});
