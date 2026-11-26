<?php

use App\Modules\Reportes\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

// Sólo se expone el PDF general de reportes; no existe PDF por reporte.
Route::get('reportes/pdf/ver', [ReportesController::class, 'pdfListadoVer']);
Route::get('reportes/pdf/descargar', [ReportesController::class, 'pdfListadoDescargar']);

Route::apiResource('reportes', ReportesController::class)
    ->parameters(['reportes' => 'reporte']);
