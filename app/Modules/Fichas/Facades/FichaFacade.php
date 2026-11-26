<?php

namespace App\Modules\Fichas\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Fichas\Services\FichaService;

class FichaFacade extends CrudFacade
{
    public function __construct(FichaService $fichaService)
    {
        parent::__construct($fichaService);
    }

}
