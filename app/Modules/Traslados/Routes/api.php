<?php

use App\Modules\Traslados\Controllers\TrasladosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Traslados: operación administrativa/cuenta-dante
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('traslados', [TrasladosController::class, 'index']);
        Route::get('traslados/{traslado}', [TrasladosController::class, 'show'])
            ->whereNumber('traslado');
        Route::post('traslados', [TrasladosController::class, 'store']);
        Route::put('traslados/{traslado}', [TrasladosController::class, 'update'])
            ->whereNumber('traslado');
        Route::patch('traslados/{traslado}', [TrasladosController::class, 'update'])
            ->whereNumber('traslado');
        Route::delete('traslados/{traslado}', [TrasladosController::class, 'destroy'])
            ->whereNumber('traslado');
    });
});
