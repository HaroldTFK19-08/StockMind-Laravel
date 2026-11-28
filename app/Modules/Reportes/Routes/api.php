<?php

use App\Modules\Reportes\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // ISMR-06/21, DSMR-08: reportes y evidencias
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('reportes', [ReportesController::class, 'index']);
        Route::get('reportes/{reporte}', [ReportesController::class, 'show'])
            ->whereNumber('reporte');
        Route::get('reportes/pdf/ver', [ReportesController::class, 'pdfListadoVer']);
        Route::get('reportes/pdf/descargar', [ReportesController::class, 'pdfListadoDescargar']);
        Route::post('reportes', [ReportesController::class, 'store']);
    });

    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::put('reportes/{reporte}', [ReportesController::class, 'update'])
            ->whereNumber('reporte');
        Route::patch('reportes/{reporte}', [ReportesController::class, 'update'])
            ->whereNumber('reporte');
        Route::delete('reportes/{reporte}', [ReportesController::class, 'destroy'])
            ->whereNumber('reporte');
    });
});
