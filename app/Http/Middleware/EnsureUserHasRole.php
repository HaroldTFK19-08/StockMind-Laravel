<?php

namespace App\Http\Middleware;

use App\Modules\Shared\Enums\EstadoUsuario;
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
        $roles = $this->normalizarRoles($roles);

        if (! $user instanceof User || $roles === []) {
            throw new AuthorizationException('No tienes permiso para acceder a este recurso.');
        }

        if ($user->estado !== EstadoUsuario::Activo->value) {
            throw new AuthorizationException('Tu cuenta no está activa.');
        }

        if (! $user->roles()->whereIn('rol', $roles)->exists()) {
            throw new AuthorizationException('No tienes permiso para acceder a este recurso.');
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function normalizarRoles(array $roles): array
    {
        $alias = [
            'admin' => 'Administrador',
            'administrador' => 'Administrador',
            'instructor' => 'Instructor',
            'aprendiz' => 'Aprendiz',
            'cuenta-dante' => 'Cuenta-dante',
            'cuentadante' => 'Cuenta-dante',
            'cuenta_dante' => 'Cuenta-dante',
        ];

        $normalizados = [];
        foreach ($roles as $rol) {
            $clave = strtolower(trim($rol));
            $normalizados[] = $alias[$clave] ?? trim($rol);
        }

        return array_values(array_unique(array_filter($normalizados)));
    }
}
