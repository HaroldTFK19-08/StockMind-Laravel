<?php

use App\Modules\Elementos\Controllers\ElementosController;
use Illuminate\Support\Facades\Route;

Route::get('elementos/pdf/ver', [ElementosController::class, 'pdfVer']);
Route::get('elementos/pdf/descargar', [ElementosController::class, 'pdfDescargar']);

Route::apiResource('elementos', ElementosController::class)
    ->parameters(['elementos' => 'elemento']);
