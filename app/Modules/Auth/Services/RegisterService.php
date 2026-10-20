<?php

namespace App\Modules\Auth\Services;

use App\Modules\Usuarios\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Modules\Shared\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;

class RegisterService
{
    public function __construct(
        private CloudinaryService $cloudinaryService
    ) {}
    public function registrar(array $datos): User
    {
        if (
            isset($datos['foto_perfil']) &&
            $datos['foto_perfil'] instanceof UploadedFile
        ) {
            $imagen = $this->cloudinaryService->subir(
                $datos['foto_perfil'],
                'stockmind/perfiles'
            );
            $datos['foto_perfil'] = $imagen['url'];
            $datos['foto_perfil_public_id'] = $imagen['public_id'];
        }
        $datos['password'] = Hash::make($datos['password']);
        return User::create($datos);
    }
}
