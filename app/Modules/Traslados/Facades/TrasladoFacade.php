<?php

namespace App\Modules\Traslados\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Traslados\Services\TrasladoService;

class TrasladoFacade extends CrudFacade
{
    public function __construct(TrasladoService $trasladoService)
    {
        parent::__construct($trasladoService);
    }

}
