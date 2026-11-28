<?php

use App\Modules\Sedes\Controllers\SedesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // Administración de sedes
    Route::apiResource('sedes', SedesController::class)
        ->parameters(['sedes' => 'sede']);
});
