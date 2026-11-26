<?php

namespace App\Modules\Roles\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Roles\Models\Rol;
use App\Modules\Roles\Services\RolService;

class RolFacade extends CrudFacade
{
    public function __construct(RolService $rolService)
    {
        parent::__construct($rolService);
    }

    /**
     * @return array<int, string>
     */
    public function filtrarParaAsignacion(array $roleIds): array
    {
        return $this->servicio->filtrarParaAsignacion($roleIds);
    }
}
