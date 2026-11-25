<?php

namespace App\Http\Middleware;

use App\Modules\Shared\Services\Autorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class RolBase
{
    public function __construct(
        private readonly Autorizacion $autorizacion
    ) {}

    /**
     * Roles que autorizan el acceso.
     */
    abstract protected function roles(): array;

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (
            ! $usuario
            || ! $this->autorizacion->estaActivo($usuario)
            || ! $this->autorizacion->tieneRol($usuario, $this->roles())
        ) {
            return response()->json([
                'message' => 'No tienes permiso para acceder a estas funcionalidades.',
            ], 403);
        }

        return $next($request);
    }
}
