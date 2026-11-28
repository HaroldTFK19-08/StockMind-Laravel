<?php

use App\Modules\Prestamos\Controllers\PrestamosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // ISMR-19/20: gestión/consulta de solicitudes y préstamos
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('prestamos', [PrestamosController::class, 'index']);
        Route::get('prestamos/{prestamo}', [PrestamosController::class, 'show'])
            ->whereNumber('prestamo');
    });

    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::post('prestamos', [PrestamosController::class, 'store']);
        Route::put('prestamos/{prestamo}', [PrestamosController::class, 'update'])
            ->whereNumber('prestamo');
        Route::patch('prestamos/{prestamo}', [PrestamosController::class, 'update'])
            ->whereNumber('prestamo');
        Route::delete('prestamos/{prestamo}', [PrestamosController::class, 'destroy'])
            ->whereNumber('prestamo');
    });
});
