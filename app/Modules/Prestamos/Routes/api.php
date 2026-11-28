<?php

use App\Modules\Prestamos\Controllers\PrestamosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('prestamos', PrestamosController::class)
            ->only(['index', 'show', 'store', 'update'])
            ->parameters(['prestamos' => 'prestamo']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('prestamos', PrestamosController::class)
            ->only(['destroy'])
            ->parameters(['prestamos' => 'prestamo']);
    });
});
