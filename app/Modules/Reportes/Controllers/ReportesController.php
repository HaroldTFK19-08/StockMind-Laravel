<?php

namespace App\Modules\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Reportes\Facades\ReporteFacade;
use App\Modules\Reportes\Requests\ActualizarReporteRequest;
use App\Modules\Reportes\Requests\RegistroReporteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReportesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private ReporteFacade $reporteFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->reporteFacade->listar()
        );
    }

    public function store(RegistroReporteRequest $request): JsonResponse
    {
        $reporte = $this->reporteFacade->crear(
            $request->validated()
        );

        return $this->creado('Reporte', $reporte);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->reporteFacade->mostrar($id));
    }

    public function update(
        ActualizarReporteRequest $request,
        int $id
    ): JsonResponse {
        $reporte = $this->reporteFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Reporte', $reporte);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->reporteFacade->eliminar($id);

        return $this->eliminado('Reporte');
    }

    public function pdfDescargar(int $id): Response
    {
        return $this->reporteFacade->pdfDescargar($id);
    }

    public function pdfVer(int $id): Response
    {
        return $this->reporteFacade->pdfVer($id);
    }

    public function pdfListadoVer(): Response
    {
        return $this->reporteFacade->pdfListadoVer();
    }

    public function pdfListadoDescargar(): Response
    {
        return $this->reporteFacade->pdfListadoDescargar();
    }
}
