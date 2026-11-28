<?php

use App\Modules\Elementos\Controllers\ElementosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante')->group(function () {
        Route::get(
            'elementos/inventario-por-sede-almacen-categoria',
            [ElementosController::class, 'inventarioPorSedeAlmacenCategoria']
        );
        Route::get(
            'elementos/{codigo}/historial',
            [ElementosController::class, 'historial']
        );
    });

    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('elementos', ElementosController::class)
            ->only(['index', 'show'])
            ->parameters(['elementos' => 'elemento']);
    });

    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::apiResource('elementos', ElementosController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['elementos' => 'elemento']);
    });
});
