<?php
//crud Roles
namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Roles\Facades\RolFacade;
use App\Modules\Roles\Requests\ActualizarRolRequest;
use App\Modules\Roles\Requests\RegistroRolRequest;
use Illuminate\Http\JsonResponse;

class RolesController extends Controller
{
    public function __construct(
        private RolFacade $rolFacade
    ) {}
    public function index(): JsonResponse
    {
        return response()->json(
            $this->rolFacade->listar()
        );
    }
    public function store(
        RegistroRolRequest $request
    ): JsonResponse {
        $rol = $this->rolFacade->crear(
            $request->validated()
        );
        return response()->json([
            'message' => 'Rol registrado correctamente.',
            'data' => $rol,
        ], 201);
    }
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->rolFacade->mostrar($id),
        ]);
    }
    public function update(
        ActualizarRolRequest $request,
        int $id
    ): JsonResponse {
        $rol = $this->rolFacade->actualizar(
            $id,
            $request->validated()
        );
        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'data' => $rol,
        ]);
    }
    public function destroy(int $id): JsonResponse
    {
        $this->rolFacade->eliminar($id);
        return response()->json([
            'message' => 'Rol eliminado correctamente.',
        ]);
    }
}
