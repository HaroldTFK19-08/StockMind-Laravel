<?php

use App\Modules\Sedes\Controllers\SedesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('sedes', SedesController::class)
            ->only(['index', 'show'])
            ->parameters(['sedes' => 'sede']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('sedes', SedesController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['sedes' => 'sede']);
    });
});
