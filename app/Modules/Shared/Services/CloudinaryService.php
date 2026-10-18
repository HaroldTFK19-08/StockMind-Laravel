<?php

namespace App\Modules\Shared\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Throwable;
use RuntimeException;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new RuntimeException(
                'Faltan las credenciales de Cloudinary.'
            );
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }
    public function subir(
        UploadedFile $archivo,
        string $carpeta
    ): array {
        try {
            $resultado = $this->cloudinary
                ->uploadApi()
                ->upload($archivo->getRealPath(), [
                    'asset_folder' => $carpeta,
                    'resource_type' => 'image',
                ]);
            return [
                'url' => $resultado['secure_url'],
                'public_id' => $resultado['public_id'],
            ];
        } catch (\Throwable $e) {
            report($e);
            throw new ReglasNegocioExceptions(
                'No fue posible subir la imagen. Inténtalo nuevamente.',
                502
            );
        }
    }
    public function eliminar(string $publicId): void
    {
        try {
            $resultado = $this->cloudinary
                ->uploadApi()
                ->destroy($publicId, [
                    'resource_type' => 'image',
                ]);
            if (($resultado['result'] ?? null) !== 'ok') {
                throw new RuntimeException(
                    'Cloudinary no confirmó la eliminación.'
                );
            }
        } catch (Throwable $e) {
            report($e);
            throw new ReglasNegocioExceptions(
                'No fue posible eliminar la imagen.',
                502
            );
        }
    }
}
