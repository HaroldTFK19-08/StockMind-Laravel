<?php

namespace App\Modules\Movimientos\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Movimientos\Services\MovimientoService;

class MovimientoFacade extends CrudFacade
{
    public function __construct(MovimientoService $movimientoService)
    {
        parent::__construct($movimientoService);
    }

}
