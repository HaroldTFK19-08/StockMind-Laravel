<?php

use App\Modules\Notificaciones\Controllers\NotificacionesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])
    ->prefix('notificaciones')
    ->group(function () {
        // ASMR-34/37: notificaciones para usuarios autenticados
        Route::get('contador', [NotificacionesController::class, 'contador']);
        Route::patch('leer-todas', [NotificacionesController::class, 'marcarTodasLeidas']);
        Route::patch('{notificacion}/leida', [NotificacionesController::class, 'marcarLeida'])
            ->whereNumber('notificacion');
        Route::get('/', [NotificacionesController::class, 'index']);
    });
