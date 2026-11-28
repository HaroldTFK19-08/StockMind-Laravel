<?php

use App\Modules\Ambientes\Controllers\AmbientesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('ambientes', AmbientesController::class)
            ->only(['index', 'show'])
            ->parameters(['ambientes' => 'ambiente']);
    });

    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::apiResource('ambientes', AmbientesController::class)
            ->only(['update'])
            ->parameters(['ambientes' => 'ambiente']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('ambientes', AmbientesController::class)
            ->only(['store', 'destroy'])
            ->parameters(['ambientes' => 'ambiente']);
    });
});
