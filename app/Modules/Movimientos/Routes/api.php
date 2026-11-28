<?php

use App\Modules\Movimientos\Controllers\MovimientosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Doc: movimientos historial; index/show admin,cuentadante(sus ambientes); POST manual cuentadante; nadie edita/borra
    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::apiResource('movimientos', MovimientosController::class)
            ->only(['index', 'show'])
            ->parameters(['movimientos' => 'movimiento']);
    });

    Route::middleware('role:Cuenta-dante')->group(function () {
        Route::apiResource('movimientos', MovimientosController::class)
            ->only(['store'])
            ->parameters(['movimientos' => 'movimiento']);
    });
});
