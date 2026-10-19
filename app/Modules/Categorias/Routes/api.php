<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Categorias\Controllers\CategoryController;

Route::apiResource('categorias', CategoryController::class);
