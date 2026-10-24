<?php

use App\Modules\Prestamos\Controllers\PrestamosController;
use Illuminate\Support\Facades\Route;

Route::apiResource('prestamos', PrestamosController::class)
    ->parameters(['prestamos' => 'prestamo']);
