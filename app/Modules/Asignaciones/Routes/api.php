<?php

use App\Modules\Asignaciones\Controllers\AsignacionesController;
use Illuminate\Support\Facades\Route;

Route::get('asignaciones/pdf/ver', [AsignacionesController::class, 'pdfVer']);
Route::get('asignaciones/pdf/descargar', [AsignacionesController::class, 'pdfDescargar']);

Route::apiResource('asignaciones', AsignacionesController::class)
    ->parameters(['asignaciones' => 'asignacion']);
