<?php

use App\Modules\Consultas\Controllers\ConsultasController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('consultas')->group(function () {
    // DSMR-05/08, ISMR-22: consultas administrativas/operativas
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('instructores', [ConsultasController::class, 'instructores']);
        Route::get('cuentas-dante', [ConsultasController::class, 'cuentasDante']);
        Route::get('reportes', [ConsultasController::class, 'reportes']);
    });

    // ASMR-36/38, ISMR-16: solicitudes propias / por ficha
    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('mis-solicitudes', [ConsultasController::class, 'misSolicitudes']);
    });

    Route::middleware('rol:Administrador,Instructor,Aprendiz,Cuenta-dante')->group(function () {
        Route::get('solicitudes-ficha', [ConsultasController::class, 'solicitudesFicha']);
    });
});
