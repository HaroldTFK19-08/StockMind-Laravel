<?php

use App\Modules\Reportes\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // PDFs reportes: general por rol/alcance (sin PDF por reporte individual)
    Route::middleware('role:admin,Instructor,Cuenta-dante')->group(function () {
        Route::get('reportes/pdf/ver', [ReportesController::class, 'pdfVer']);
        Route::get('reportes/pdf/descargar', [ReportesController::class, 'pdfDescargar']);
    });

    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::apiResource('reportes', ReportesController::class)
            ->only(['index', 'show', 'store'])
            ->parameters(['reportes' => 'reporte']);
    });

    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::apiResource('reportes', ReportesController::class)
            ->only(['update'])
            ->parameters(['reportes' => 'reporte']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('reportes', ReportesController::class)
            ->only(['destroy'])
            ->parameters(['reportes' => 'reporte']);
    });
});
