<?php

namespace App\Http\Middleware;

use App\Modules\Shared\Services\Autorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function __construct(
        private Autorizacion $autorizacionService
    ) {}

    // Uso: ->middleware('rol:Administrador,Cuenta-dante')
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user(); // ya es un modelo User (lo entrega Sanctum)

        if (!$usuario || !$this->autorizacionService->estaActivo($usuario)) {
            return response()->json(['message' => 'Tu cuenta no está activa.'], 403);
        }

        if (!$this->autorizacionService->tieneRol($usuario, $roles)) {
            return response()->json(['message' => 'No tienes permiso para esta acción.'], 403);
        }

        return $next($request);
    }
}
