<?php

use App\Modules\Fichas\Controllers\FichasController;
use Illuminate\Support\Facades\Route;

Route::apiResource('fichas', FichasController::class)
    ->parameters(['fichas' => 'ficha']);
