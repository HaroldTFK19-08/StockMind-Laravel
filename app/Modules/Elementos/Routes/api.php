<?php

use App\Modules\Elementos\Controllers\ElementosController;
use Illuminate\Support\Facades\Route;

Route::apiResource('elementos', ElementosController::class)
    ->parameters(['elementos' => 'elemento']);
