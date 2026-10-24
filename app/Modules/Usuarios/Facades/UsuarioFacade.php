<?php

namespace App\Modules\Usuarios\Facades;

use App\Modules\Usuarios\Models\User;
use App\Modules\Usuarios\Services\UsuarioService;

class UsuarioFacade
{
    public function __construct(
        private UsuarioService $usuarioService
    ) {}

    public function listar()
    {
        return $this->usuarioService->listar();
    }

    public function mostrar(int $id): User
    {
        return $this->usuarioService->mostrar($id);
    }

    public function crear(array $datos): User
    {
        return $this->usuarioService->crear($datos);
    }

    public function actualizar(int $id, array $datos): User
    {
        return $this->usuarioService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->usuarioService->eliminar($id);
    }
}
