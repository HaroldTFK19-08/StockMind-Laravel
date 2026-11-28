<?php

use App\Modules\Consultas\Controllers\ConsultasController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('consultas')->group(function () {
    // Doc Permisos: cuentas-dante/instructores admin,cuentadante; mis-solicitudes instructor,cuentadante,aprendiz;
    // solicitudes-ficha admin,instructor,cuentadante,aprendiz(vocero - validar en servicio); reportes admin,instructor,cuentadante
    Route::middleware('role:admin,Cuenta-dante')->group(function () {
        Route::get('instructores', [ConsultasController::class, 'instructores']);
        Route::get('cuentas-dante', [ConsultasController::class, 'cuentasDante']);
    });

    Route::middleware('role:Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::get('mis-solicitudes', [ConsultasController::class, 'misSolicitudes']);
    });

    Route::middleware('role:admin,Instructor,Cuenta-dante,Aprendiz')->group(function () {
        Route::get('solicitudes-ficha', [ConsultasController::class, 'solicitudesFicha']);
    });

    Route::middleware('role:admin,Instructor,Cuenta-dante')->group(function () {
        Route::get('reportes', [ConsultasController::class, 'reportes']);
    });
});
