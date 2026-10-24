<?php

namespace App\Modules\Usuarios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Usuarios\Facades\UsuarioFacade;
use App\Modules\Usuarios\Requests\ActualizarUsuarioRequest;
use App\Modules\Usuarios\Requests\RegistroUsuarioRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UsuarioFacade $usuarioFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->usuarioFacade->listar()
        );
    }

    public function store(RegistroUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarioFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => $usuario,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->usuarioFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarUsuarioRequest $request,
        int $id
    ): JsonResponse {
        $usuario = $this->usuarioFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $usuario,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->usuarioFacade->eliminar($id);

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }
}
