<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Facades\AuthFacade;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Se inyecta la Facade, igual que en los demás módulos
    public function __construct(
        private AuthFacade $authFacade
    ) {}
    // POST /api/v1/auth/register
    public function register(RegisterRequest $request): JsonResponse
    {
        $usuario = $this->authFacade->registrar($request->validated());
        return response()->json([
            'mensaje' => 'Registro exitoso. Un administrador debe aprobar tu cuenta.',
            'usuario' => $usuario,
        ], 201);
    }
    // POST /api/v1/auth/login
    public function login(LoginRequest $request): JsonResponse
    {
        // Devuelve: mensaje, token y usuario con sus roles
        return response()->json(
            $this->authFacade->login($request->validated())
        );
    }
    // POST /api/v1/auth/logout  (requiere token)
    public function logout(Request $request): JsonResponse
    {
        $this->authFacade->logout($request->user());
        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.',
        ]);
    }
    // GET /api/v1/auth/me  (requiere token)
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $this->authFacade->perfil($request->user())
        );
    }
}
