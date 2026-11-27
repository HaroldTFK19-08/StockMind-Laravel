<?php

use App\Modules\Usuarios\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('usuarios/perfiles', [UserController::class, 'indexConPerfiles']);

    Route::apiResource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'usuario']);
});
