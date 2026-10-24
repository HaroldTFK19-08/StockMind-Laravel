<?php

use App\Modules\Roles\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('roles', RolesController::class);
});
