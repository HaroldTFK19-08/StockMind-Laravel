<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Categorias\Controllers\CategoriasController;

Route::apiResource('categorias', CategoriasController::class);
