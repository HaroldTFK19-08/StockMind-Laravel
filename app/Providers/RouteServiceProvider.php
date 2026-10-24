<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        $this->routes(function () {
            // =====================
            // RUTAS GENERALES API
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('routes/api.php'));
            // =====================
            // CENTROS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Centros/Routes/api.php'));
            // =====================
            // ROLES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Roles/Routes/api.php'));
            // =====================
            // ALMACENES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Almacenes/Routes/api.php'));
            // =====================
            // AMBIENTES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Ambientes/Routes/api.php'));
            // =====================
            // ASIGNACIONES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Asignaciones/Routes/api.php'));
            // =====================
            // CATEGORIAS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Categorias/Routes/api.php'));
            // =====================
            // ELEMENTOS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Elementos/Routes/api.php'));
            // =====================
            // FICHAS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Fichas/Routes/api.php'));
            // =====================
            // MOVIMIENTOS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Movimientos/Routes/api.php'));
            // =====================
            // PRESTAMOS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Prestamos/Routes/api.php'));
            // =====================
            // REPORTES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Reportes/Routes/api.php'));
            // =====================
            // SEDES
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Sedes/Routes/api.php'));
            // =====================
            // TRASLADOS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Traslados/Routes/api.php'));
            // =====================
            // USUARIOS
            // =====================
            Route::middleware('api')
                ->prefix('v1')
                ->group(base_path('app/Modules/Usuarios/Routes/api.php'));
        });
    }
}
