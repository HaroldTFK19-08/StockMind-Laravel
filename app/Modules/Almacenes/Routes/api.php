<?php

use App\Modules\Almacenes\Controllers\AlmacenesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('almacenes', AlmacenesController::class)
    ->parameters(['almacenes' => 'almacen']);
