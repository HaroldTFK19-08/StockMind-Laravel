<?php

use App\Modules\Traslados\Controllers\TrasladosController;
use Illuminate\Support\Facades\Route;

Route::apiResource('traslados', TrasladosController::class)
    ->parameters(['traslados' => 'traslado']);
