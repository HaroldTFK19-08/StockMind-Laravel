<?php

use App\Modules\Ambientes\Controllers\AmbientesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('ambientes', AmbientesController::class)
    ->parameters(['ambientes' => 'ambiente']);
