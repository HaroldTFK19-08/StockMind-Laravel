<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolInstructor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        $usuario = $request->user();
        // ¿El usuario tiene el rol que pide la ruta?
        if ($usuario && $usuario->tieneRol($rol)) {
            return $next($request);
        }
        return response()->json(['message' => 'No puedes acceder a estas peticiones'], 403);
    }
}
