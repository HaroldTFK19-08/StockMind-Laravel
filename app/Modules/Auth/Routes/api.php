<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    // Registro de usuarios
    Route::post('/register', [AuthController::class, 'register']);
    // Inicio de sesión
    Route::post('/login', [AuthController::class, 'login']);
    // Cierre de sesión
    Route::post('/logout', [AuthController::class, 'logout']);
});
