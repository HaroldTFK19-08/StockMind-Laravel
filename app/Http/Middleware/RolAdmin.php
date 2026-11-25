<?php

namespace App\Http\Middleware;

class RolAdmin extends RolBase
{
    protected function roles(): array
    {
        // El catálogo no incluye "Administrador" (según lo indicado),
        // pero el filtro queda listo si ese rol llega a crearse.
        return ['Administrador'];
    }
}
