<?php

namespace App\Modules\Categorias\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Categorias\Services\CategoriaService;

class CategoriaFacade extends CrudFacade
{
    public function __construct(CategoriaService $categoriaService)
    {
        parent::__construct($categoriaService);
    }

}
