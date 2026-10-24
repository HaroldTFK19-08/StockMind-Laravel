<?php

use App\Modules\Movimientos\Controllers\MovimientosController;
use Illuminate\Support\Facades\Route;

Route::apiResource('movimientos', MovimientosController::class)
    ->parameters(['movimientos' => 'movimiento']);
