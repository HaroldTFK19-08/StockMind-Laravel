<?php

use App\Modules\Consultas\Controllers\ConsultasController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('consultas')->group(function () {
    Route::get('instructores', [ConsultasController::class, 'instructores']);
    Route::get('cuentas-dante', [ConsultasController::class, 'cuentasDante']);
    Route::get('reportes', [ConsultasController::class, 'reportes']);
    Route::get('mis-solicitudes', [ConsultasController::class, 'misSolicitudes']);
    Route::get('solicitudes-ficha', [ConsultasController::class, 'solicitudesFicha']);
});
