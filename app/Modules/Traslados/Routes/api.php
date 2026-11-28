<?php

use App\Modules\Traslados\Controllers\TrasladosController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::apiResource('traslados', TrasladosController::class)
            ->only(['index', 'show'])
            ->parameters(['traslados' => 'traslado']);
    });

    Route::middleware('role:Cuenta-dante')->group(function () {
        Route::apiResource('traslados', TrasladosController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['traslados' => 'traslado']);
    });
});
