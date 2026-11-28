<?php

use App\Modules\Usuarios\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // DSMR-07/ASMR-11: gestión administrativa de usuarios/roles
    Route::get('usuarios/perfiles', [UserController::class, 'indexConPerfiles']);

    Route::apiResource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'usuario']);
});
