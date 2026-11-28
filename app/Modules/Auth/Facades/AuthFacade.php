<?php

namespace App\Modules\Auth\Facades;

use App\Modules\Auth\Services\AccessCodeService;
use App\Modules\Auth\Services\LoginService;
use App\Modules\Auth\Services\RegisterService;
use App\Modules\Auth\Services\TokenService;
use App\Modules\Usuarios\Models\User;

class AuthFacade
{
    public function __construct(
        private RegisterService $registerService,
        private LoginService $loginService,
        private TokenService $tokenService,
        private AccessCodeService $accessCodeService
    ) {}

    public function registrar(array $datos): User
    {
        return $this->registerService->registrar($datos);
    }

    /**
     * Reemplaza el token pendiente del registro por uno vigente y lo entrega.
     */
    public function activarCuenta(User $usuario): string
    {
        return $this->tokenService->activar($usuario);
    }

    public function login(array $credenciales): array
    {
        return $this->loginService->login($credenciales);
    }

    public function logout(User $usuario): void
    {
        $this->loginService->logout($usuario);
    }

    public function perfil(User $usuario): User
    {
        return $this->loginService->perfil($usuario);
    }

    public function verificarCodigoAcceso(array $datos): array
    {
        $usuario = User::where('email', $datos['email'])->first();

        if (! $usuario) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $this->accessCodeService->verificar($usuario, $datos['codigo']);

        return [
            'mensaje' => 'Código verificado correctamente. Ya puedes iniciar sesión.',
        ];
    }
}
