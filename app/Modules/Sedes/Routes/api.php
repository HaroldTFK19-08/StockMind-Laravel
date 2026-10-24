<?php

use App\Modules\Sedes\Controllers\SedesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('sedes', SedesController::class)
    ->parameters(['sedes' => 'sede']);
