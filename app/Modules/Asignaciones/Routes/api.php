<?php

use App\Modules\Asignaciones\Controllers\AsignacionesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Doc: index/show todos con alcance; mutaciones solo admin; pdf admin,instructor(suyas),cuentadante(sus ambientes)
    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('asignaciones', AsignacionesController::class)
            ->only(['index', 'show'])
            ->parameters(['asignaciones' => 'asignacion']);
    });

    // PDFs asignaciones: admin todo; cuentadante sus ambientes; instructor sus aprendices de ficha y sus asignaciones
    Route::middleware('role:admin,Instructor,Cuenta-dante')->group(function () {
        Route::get('asignaciones/pdf/ver', [AsignacionesController::class, 'pdfVer']);
        Route::get('asignaciones/pdf/descargar', [AsignacionesController::class, 'pdfDescargar']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('asignaciones', AsignacionesController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['asignaciones' => 'asignacion']);
    });
});
