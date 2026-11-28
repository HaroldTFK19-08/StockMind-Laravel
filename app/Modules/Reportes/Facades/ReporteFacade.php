<?php

namespace App\Modules\Reportes\Facades;

use App\Modules\Reportes\Services\ReporteService;
use App\Modules\Shared\Facades\CrudFacade;
use Illuminate\Http\Response;

class ReporteFacade extends CrudFacade
{
    public function __construct(ReporteService $reporteService)
    {
        parent::__construct($reporteService);
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
