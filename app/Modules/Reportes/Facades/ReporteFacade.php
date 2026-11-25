<?php

namespace App\Modules\Reportes\Facades;

use App\Modules\Reportes\Models\Report;
use App\Modules\Reportes\Services\ReporteService;
use Illuminate\Http\Response;

class ReporteFacade
{
    public function __construct(
        private ReporteService $reporteService
    ) {}

    public function listar()
    {
        return $this->reporteService->listar();
    }

    public function mostrar(int $id): Report
    {
        return $this->reporteService->mostrar($id);
    }

    public function crear(array $datos): Report
    {
        return $this->reporteService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Report
    {
        return $this->reporteService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->reporteService->eliminar($id);
    }

    public function pdfDescargar(int $id): Response
    {
        return $this->reporteService->pdfDescargar($id);
    }

    public function pdfVer(int $id): Response
    {
        return $this->reporteService->pdfVer($id);
    }

    public function pdfListadoVer(): Response
    {
        return $this->reporteService->pdfListadoVer();
    }

    public function pdfListadoDescargar(): Response
    {
        return $this->reporteService->pdfListadoDescargar();
    }
}
