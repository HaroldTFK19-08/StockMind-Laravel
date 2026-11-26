<?php

namespace App\Modules\Ambientes\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Ambientes\Services\AmbienteService;

class AmbienteFacade extends CrudFacade
{
    public function __construct(AmbienteService $ambienteService)
    {
        parent::__construct($ambienteService);
    }

}
