<?php

use App\Modules\Elementos\Controllers\ElementosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // DSMR-05/08/10/12, ISMR-04/15/22: consultas de inventario
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('elementos', [ElementosController::class, 'index']);
        Route::get('elementos/{elemento}', [ElementosController::class, 'show'])
            ->whereNumber('elemento');
        Route::get(
            'elementos/inventario-por-sede-almacen-categoria',
            [ElementosController::class, 'inventarioPorSedeAlmacenCategoria']
        );
        Route::get(
            'elementos/{codigo}/historial',
            [ElementosController::class, 'historial']
        );
        Route::get('elementos/pdf/ver', [ElementosController::class, 'pdfVer']);
        Route::get('elementos/pdf/descargar', [ElementosController::class, 'pdfDescargar']);
    });

    // DSMR-05/10/12, ISMR-17 (gestión operativa administrativa): mutaciones de inventario
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::post('elementos', [ElementosController::class, 'store']);
        Route::put('elementos/{elemento}', [ElementosController::class, 'update'])
            ->whereNumber('elemento');
        Route::patch('elementos/{elemento}', [ElementosController::class, 'update'])
            ->whereNumber('elemento');
        Route::delete('elementos/{elemento}', [ElementosController::class, 'destroy'])
            ->whereNumber('elemento');
    });
});
