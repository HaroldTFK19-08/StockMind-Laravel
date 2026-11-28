<?php

namespace App\Modules\Roles\Services;

use App\Modules\Roles\Models\Rol;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;

class RolService
{
    public function __construct(
        private CacheService $cache
    ) {}

    public function listar()
    {
        return Rol::latest()->paginate(10);
    }

    public function mostrar(int $id): Rol
    {
        return Rol::findOrFail($id);
    }

    /**
     * Valida qué combinaciones se pueden asignar a un usuario.
     * Un instructor puede recibir permisos de cuenta-dante; las demás
     * combinaciones no están permitidas.
     *
     * @return array<int, string> Los roles válidos, ordenados.
     */
    public function filtrarParaAsignacion(array $roleIds): array
    {
        $ids = array_values(array_unique($roleIds));
        $roles = Rol::query()
            ->whereIn('id', $ids)
            ->pluck('rol')
            ->all();

        if (count($roles) !== count($ids)) {
            throw new ReglasNegocioExceptions(
                'Uno de los roles seleccionados no existe.',
                422
            );
        }

        sort($roles, SORT_STRING);

        $permitidas = [
            ['Aprendiz'],
            ['Cuenta-dante'],
            ['Instructor'],
            ['Cuenta-dante', 'Instructor'],
        ];

        if (! in_array($roles, $permitidas, true)) {
            throw new ReglasNegocioExceptions(
                'Combinación de roles no permitida. Solo se permite Instructor + Cuenta-dante.',
                422
            );
        }

        return $roles;
    }

    public function crear(array $datos): Rol
    {
        $rol = Rol::create($datos);
        $this->cache->invalidar('roles');

        return $rol;
    }

    public function actualizar(int $id, array $datos): Rol
    {
        $rol = $this->mostrar($id);
        $rol->update($datos);
        $this->cache->invalidar('roles');

        return $rol->refresh();
    }

    public function eliminar(int $id): void
    {
        $rol = $this->mostrar($id);

        if ($rol->users()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el rol porque tiene usuarios asociados.'
            );
        }

        $rol->delete();
        $this->cache->invalidar('roles');
    }
}
