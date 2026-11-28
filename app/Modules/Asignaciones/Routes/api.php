<?php

use App\Modules\Asignaciones\Controllers\AsignacionesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // ASMR-13/36/38, ISMR-16/18/20: consultas de asignaciones/solicitudes
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('asignaciones', [AsignacionesController::class, 'index']);
        Route::get('asignaciones/{asignacion}', [AsignacionesController::class, 'show'])
            ->whereNumber('asignacion');
        Route::get('asignaciones/pdf/ver', [AsignacionesController::class, 'pdfVer']);
        Route::get('asignaciones/pdf/descargar', [AsignacionesController::class, 'pdfDescargar']);
    });

    // Gestión de asignaciones: administrativo/instructor
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::post('asignaciones', [AsignacionesController::class, 'store']);
        Route::put('asignaciones/{asignacion}', [AsignacionesController::class, 'update'])
            ->whereNumber('asignacion');
        Route::patch('asignaciones/{asignacion}', [AsignacionesController::class, 'update'])
            ->whereNumber('asignacion');
        Route::delete('asignaciones/{asignacion}', [AsignacionesController::class, 'destroy'])
            ->whereNumber('asignacion');
    });
});
