<?php

use App\Modules\Fichas\Controllers\FichasController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('fichas', FichasController::class)
            ->only(['index', 'show'])
            ->parameters(['fichas' => 'ficha']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('fichas', FichasController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['fichas' => 'ficha']);
    });
});
