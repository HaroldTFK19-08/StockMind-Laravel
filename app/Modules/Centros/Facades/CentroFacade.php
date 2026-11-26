<?php

namespace App\Modules\Centros\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Centros\Services\CentroService;

class CentroFacade extends CrudFacade
{
    public function __construct(CentroService $centroService)
    {
        parent::__construct($centroService);
    }

}
