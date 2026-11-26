<?php

namespace App\Modules\Almacenes\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Almacenes\Services\AlmacenService;

class AlmacenFacade extends CrudFacade
{
    public function __construct(AlmacenService $almacenService)
    {
        parent::__construct($almacenService);
    }

}
