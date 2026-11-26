<?php

namespace App\Modules\Sedes\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Sedes\Services\SedeService;

class SedeFacade extends CrudFacade
{
    public function __construct(SedeService $sedeService)
    {
        parent::__construct($sedeService);
    }

}
