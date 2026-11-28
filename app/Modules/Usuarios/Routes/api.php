<?php

use App\Modules\Usuarios\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Doc: admin gestiona; show/update también propio usuario (validar propiedad en controlador/policy)
    Route::middleware('role:admin')->group(function () {
        Route::get('usuarios/perfiles', [UserController::class, 'indexConPerfiles']);

        Route::apiResource('usuarios', UserController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['usuarios' => 'usuario']);
    });

    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('usuarios', UserController::class)
            ->only(['show', 'update'])
            ->parameters(['usuarios' => 'usuario']);
    });
});
