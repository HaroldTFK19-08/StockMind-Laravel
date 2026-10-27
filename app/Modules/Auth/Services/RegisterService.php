<?php

namespace App\Modules\Auth\Services;

use App\Modules\Shared\Enums\EstadoUsuario;      // ← NUEVO
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Usuarios\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function __construct(
        private CloudinaryService $cloudinaryService
    ) {}

    public function registrar(array $datos): User
    {
        // Si mandó foto de perfil, se sube a Cloudinary
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
        $datos['estado'] = EstadoUsuario::Pendiente->value; // ← NUEVO: espera aprobación del admin

        return User::create($datos);
    }
}
