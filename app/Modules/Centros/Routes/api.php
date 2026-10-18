<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Centros\Controllers\CentrosController;

Route::apiResource('centros', CentrosController::class);
