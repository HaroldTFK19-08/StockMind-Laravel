<?php

namespace App\Modules\Usuarios\Facades;

use App\Modules\Shared\Facades\CrudFacade;
use App\Modules\Usuarios\Services\UsuarioService;

class UsuarioFacade extends CrudFacade
{
    public function __construct(UsuarioService $usuarioService)
    {
        parent::__construct($usuarioService);
    }

    public function listarConPerfiles()
    {
        return $this->servicio->listarConPerfiles();
    }
}
