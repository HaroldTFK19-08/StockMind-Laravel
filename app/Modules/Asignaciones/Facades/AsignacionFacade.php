<?php

namespace App\Modules\Asignaciones\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Asignaciones\Services\AsignacionService;
use Illuminate\Http\Response;

class AsignacionFacade extends CrudFacade
{
    public function __construct(AsignacionService $asignacionService)
    {
        parent::__construct($asignacionService);
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
