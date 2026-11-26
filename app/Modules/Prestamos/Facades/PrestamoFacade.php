<?php

namespace App\Modules\Prestamos\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Prestamos\Services\PrestamoService;

class PrestamoFacade extends CrudFacade
{
    public function __construct(PrestamoService $prestamoService)
    {
        parent::__construct($prestamoService);
    }

}
