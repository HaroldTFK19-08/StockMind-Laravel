<?php

use App\Modules\Asignaciones\Controllers\AsignacionesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('asignaciones', AsignacionesController::class)
    ->parameters(['asignaciones' => 'asignacion']);
