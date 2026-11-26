<?php

namespace App\Modules\Reportes\Facades;

use App\Modules\Shared\Facades\CrudFacade;

use App\Modules\Reportes\Models\Report;
use App\Modules\Reportes\Services\ReporteService;
use Illuminate\Http\Response;

class ReporteFacade extends CrudFacade
{
    public function __construct(ReporteService $reporteService)
    {
        parent::__construct($reporteService);
    }

    public function pdfListadoVer(): Response
    {
        return $this->servicio->pdfListadoVer();
    }

    public function pdfListadoDescargar(): Response
    {
        return $this->servicio->pdfListadoDescargar();
    }
}
