<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Modules\Shared\Enums\EstadoUsuario;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();
        // Filtro 1: ¿su cuenta está activa?
        if (!$usuario || $usuario->estado !== EstadoUsuario::Activo->value) {
            return response()->json(['message' => 'Tu cuenta no está activa.'], 403);
        }
        // Filtro 2: ¿tiene AL MENOS UNO de los roles pedidos?
        if (!$usuario->tieneRol(...$roles)) {
            return response()->json(['message' => 'No tienes permiso para esta acción.'], 403);
        }
        // Pasó los dos filtros: sigue al controlador
        return $next($request);
    }
}
