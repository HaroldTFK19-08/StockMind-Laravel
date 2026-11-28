<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Centros\Controllers\CentrosController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('centros', CentrosController::class)
            ->only(['index', 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('centros', CentrosController::class)
            ->only(['store', 'update', 'destroy']);
    });
});
