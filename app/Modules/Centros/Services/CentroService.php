<?php

namespace App\Modules\Centros\Services;

use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Shared\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Modules\Shared\Services\CacheService;

class CentroService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private CacheService $cache
    ) {}
    // Listado completo de centros: sale de Redis
    public function listar()
    {
        return $this->cache->recordar(
            'centros',
            'lista',
            CacheService::TTL_CATALOGO,
            fn () => Trainingcenter::latest()->get()->toArray()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'centros',
            "detalle:{$id}",
            CacheService::TTL_CATALOGO,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan actualizar y eliminar
    private function buscar(int $id): Trainingcenter
    {
        return Trainingcenter::findOrFail($id);
    }

    public function crear(array $datos): Trainingcenter
    {
        $foto = $datos['foto_centro'];
        unset($datos['foto_centro']);

        $imagen = $this->cloudinaryService->subir(
            $foto,
            'centros'
        );

        try {
            return DB::transaction(function () use ($datos, $imagen) {
                $this->cache->invalidar('centros');

                return Trainingcenter::create([
                    ...$datos,
                    'foto_centro' => $imagen['url'],
                    'foto_centro_public_id' => $imagen['public_id'],
                ]);
            });
        } catch (Throwable $e) {
            try {
                $this->cloudinaryService->eliminar(
                    $imagen['public_id']
                );
            } catch (Throwable $errorImagen) {
                report($errorImagen);
            }
            throw $e;
        }
    }
    public function actualizar(int $id, array $datos): Trainingcenter
    {
        $centro = $this->buscar($id);
        $publicIdAnterior = $centro->foto_centro_public_id;
        $foto = $datos['foto_centro'] ?? null;
        unset($datos['foto_centro']);
        $imagenNueva = null;
        if ($foto instanceof UploadedFile) {
            $imagenNueva = $this->cloudinaryService->subir(
                $foto,
                'centros'
            );
            $datos['foto_centro'] = $imagenNueva['url'];
            $datos['foto_centro_public_id'] = $imagenNueva['public_id'];
        }
        try {
            DB::transaction(function () use ($centro, $datos) {
                $centro->update($datos);
            });
        } catch (Throwable $e) {
            if ($imagenNueva !== null) {
                try {
                    $this->cloudinaryService->eliminar(
                        $imagenNueva['public_id']
                    );
                } catch (Throwable $errorImagen) {
                    report($errorImagen);
                }
            }
            throw $e;
        }
        if ($imagenNueva !== null && $publicIdAnterior) {
            try {
                $this->cloudinaryService->eliminar($publicIdAnterior);
            } catch (Throwable $e) {
                report($e);
            }
        }
        $this->cache->invalidar('centros');

        return $centro->refresh();
    }
    public function eliminar(int $id): void
    {
        $centro = $this->buscar($id);
        $publicId = $centro->foto_centro_public_id;
        $centro->delete();

        $this->cache->invalidar('centros');
        if ($publicId) {
            try {
                $this->cloudinaryService->eliminar($publicId);
            } catch (Throwable $e) {
                // El registro ya se eliminó; registra el error
                // para poder limpiar la imagen posteriormente.
                report($e);
            }
        }
    }
}
