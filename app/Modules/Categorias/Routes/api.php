<?php

use App\Modules\Categorias\Controllers\CategoriasController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('categorias', CategoriasController::class)
            ->only(['index', 'show'])
            ->parameters(['categorias' => 'categoria']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('categorias', CategoriasController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['categorias' => 'categoria']);
    });
});
