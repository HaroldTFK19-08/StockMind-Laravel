<?php

use App\Modules\Elementos\Controllers\ElementosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        'elementos/inventario-por-sede-almacen-categoria',
        [ElementosController::class, 'inventarioPorSedeAlmacenCategoria']
    );
    Route::get(
        'elementos/{codigo}/historial',
        [ElementosController::class, 'historial']
    );
});

Route::apiResource('elementos', ElementosController::class)
    ->parameters(['elementos' => 'elemento']);
