<?php

use App\Modules\Reportes\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::get('reportes/{reporte}/pdf/descargar', [ReportesController::class, 'pdfDescargar']);
Route::get('reportes/{reporte}/pdf/ver', [ReportesController::class, 'pdfVer']);

Route::apiResource('reportes', ReportesController::class)
    ->parameters(['reportes' => 'reporte']);
