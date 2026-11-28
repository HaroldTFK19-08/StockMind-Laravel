<?php

use App\Modules\Ambientes\Controllers\AmbientesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // ISMR-04/15/22: consulta de ambientes/disponibilidad para operación
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('ambientes', [AmbientesController::class, 'index']);
        Route::get('ambientes/{ambiente}', [AmbientesController::class, 'show']);
    });

    // Administración de ambientes
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::post('ambientes', [AmbientesController::class, 'store']);
        Route::put('ambientes/{ambiente}', [AmbientesController::class, 'update']);
        Route::patch('ambientes/{ambiente}', [AmbientesController::class, 'update']);
        Route::delete('ambientes/{ambiente}', [AmbientesController::class, 'destroy']);
    });
});
