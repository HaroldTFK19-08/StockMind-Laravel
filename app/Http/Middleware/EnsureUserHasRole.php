<?php

namespace App\Http\Middleware;

use App\Modules\Usuarios\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (
            ! $user instanceof User
            || $roles === []
            || ! $user->roles()->whereIn('rol', $roles)->exists()
        ) {
            throw new AuthorizationException('No tienes permiso para acceder a este recurso.');
        }

        return $next($request);
    }
}
