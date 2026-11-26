<?php

namespace App\Modules\Asignaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Asignaciones\Facades\AsignacionFacade;
use App\Modules\Asignaciones\Requests\ActualizarAsignacionRequest;
use App\Modules\Asignaciones\Requests\RegistroAsignacionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AsignacionesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private AsignacionFacade $asignacionFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->asignacionFacade->listar()
        );
    }

    public function store(RegistroAsignacionRequest $request): JsonResponse
    {
        $asignacion = $this->asignacionFacade->crear(
            $request->validated()
        );

        return $this->creado('Asignación', $asignacion, true);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->asignacionFacade->mostrar($id));
    }

    public function update(
        ActualizarAsignacionRequest $request,
        int $id
    ): JsonResponse {
        $asignacion = $this->asignacionFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Asignación', $asignacion, true);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->asignacionFacade->eliminar($id);

        return $this->eliminado('Asignación', true);
    }

    public function pdfVer(): Response
    {
        return $this->asignacionFacade->pdfVer();
    }

    public function pdfDescargar(): Response
    {
        return $this->asignacionFacade->pdfDescargar();
    }
}
