<?php

use App\Modules\Almacenes\Controllers\AlmacenesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Doc: GET almacenes admin,instructor,cuentadante; mutaciones solo admin
    Route::middleware('role:admin,Instructor,Cuenta-dante')->group(function () {
        Route::apiResource('almacenes', AlmacenesController::class)
            ->only(['index', 'show'])
            ->parameters(['almacenes' => 'almacen']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('almacenes', AlmacenesController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['almacenes' => 'almacen']);
    });
});
