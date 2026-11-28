<?php

use App\Modules\Movimientos\Controllers\MovimientosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Trazabilidad de inventario: consulta para roles operativos, mutación administrativa
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('movimientos', [MovimientosController::class, 'index']);
        Route::get('movimientos/{movimiento}', [MovimientosController::class, 'show'])
            ->whereNumber('movimiento');
    });

    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::post('movimientos', [MovimientosController::class, 'store']);
        Route::put('movimientos/{movimiento}', [MovimientosController::class, 'update'])
            ->whereNumber('movimiento');
        Route::patch('movimientos/{movimiento}', [MovimientosController::class, 'update'])
            ->whereNumber('movimiento');
        Route::delete('movimientos/{movimiento}', [MovimientosController::class, 'destroy'])
            ->whereNumber('movimiento');
    });
});
