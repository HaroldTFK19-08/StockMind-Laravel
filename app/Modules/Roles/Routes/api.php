<?php

use App\Modules\Roles\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // DSMR-07: asignación/gestión de roles
    Route::apiResource('roles', RolesController::class);
});
