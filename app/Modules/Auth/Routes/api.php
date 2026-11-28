<?php

use App\Modules\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Rutas públicas: no necesitan token
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/verificar-codigo', [AuthController::class, 'verificarCodigo'])->middleware('throttle:10,1');
    // Rutas protegidas: cualquier usuario autenticado y activo
    Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
