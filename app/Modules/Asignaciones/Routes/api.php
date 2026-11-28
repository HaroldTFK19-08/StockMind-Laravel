<?php

use App\Modules\Asignaciones\Controllers\AsignacionesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('asignaciones', AsignacionesController::class)
            ->only(['index', 'show'])
            ->parameters(['asignaciones' => 'asignacion']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('asignaciones', AsignacionesController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['asignaciones' => 'asignacion']);
    });
});
