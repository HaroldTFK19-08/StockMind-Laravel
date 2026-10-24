<?php

namespace App\Modules\Roles\Services;

use App\Modules\Roles\Models\Rol;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

class RolService
{
    public function listar()
    {
        return Rol::latest()->paginate(10);
    }

    public function mostrar(int $id): Rol
    {
        return Rol::findOrFail($id);
    }

    public function crear(array $datos): Rol
    {
        return Rol::create($datos);
    }

    public function actualizar(int $id, array $datos): Rol
    {
        $rol = $this->mostrar($id);
        $rol->update($datos);

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
    }
}
