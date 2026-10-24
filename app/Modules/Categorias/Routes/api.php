<?php

use App\Modules\Categorias\Controllers\CategoriasController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categorias', CategoriasController::class)
    ->parameters(['categorias' => 'categoria']);
