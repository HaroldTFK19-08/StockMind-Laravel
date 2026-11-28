<?php

use App\Modules\Almacenes\Controllers\AlmacenesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'rol:Administrador,Instructor,Aprendiz,Cuenta-dante'])->group(function () {
    // Administración de almacenes
    Route::apiResource('almacenes', AlmacenesController::class)
        ->parameters(['almacenes' => 'almacen']);
});
