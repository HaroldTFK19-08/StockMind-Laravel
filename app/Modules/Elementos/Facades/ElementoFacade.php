<?php

namespace App\Modules\Elementos\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Elementos\Models\Element;
use App\Modules\Elementos\Services\ElementoService;
use Illuminate\Http\Response;

class ElementoFacade extends CrudFacade
{
    public function __construct(ElementoService $elementoService)
    {
        parent::__construct($elementoService);
    }

    public function pdfVer(): Response
    {
        return $this->servicio->pdfVer();
    }

    public function pdfDescargar(): Response
    {
        return $this->servicio->pdfDescargar();
    }
}
