<?php

use App\Modules\Reportes\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('reportes', ReportesController::class)
    ->parameters(['reportes' => 'reporte']);
