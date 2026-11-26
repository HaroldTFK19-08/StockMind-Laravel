<?php

namespace App\Modules\Centros\Services;

use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\Concerns\GestionaImagenes;

class CentroService
{
    // Subida, transacción y limpieza de imágenes se comparten con otros servicios.
    use GestionaImagenes;

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

        return $this->transaccionConImagen(
            $foto,
            'centros',
            function (array $imagen) use ($datos) {
                $this->cache->invalidar('centros');

                return Trainingcenter::create([
                    ...$datos,
                    'foto_centro' => $imagen['url'],
                    'foto_centro_public_id' => $imagen['public_id'],
                ]);
            }
        );
    }
    public function actualizar(int $id, array $datos): Trainingcenter
    {
        $centro = $this->buscar($id);
        $publicIdAnterior = $centro->foto_centro_public_id;
        $foto = $datos['foto_centro'] ?? null;
        unset($datos['foto_centro']);

        $this->transaccionConImagen(
            $foto,
            'centros',
            function (?array $imagen) use ($centro, &$datos) {
                if ($imagen !== null) {
                    $datos['foto_centro'] = $imagen['url'];
                    $datos['foto_centro_public_id'] = $imagen['public_id'];
                }

                $centro->update($datos);
            },
            $publicIdAnterior
        );

        $this->cache->invalidar('centros');

        return $centro->refresh();
    }
    public function eliminar(int $id): void
    {
        $centro = $this->buscar($id);
        $publicId = $centro->foto_centro_public_id;
        $centro->delete();

        $this->cache->invalidar('centros');
        // El registro ya se eliminó; la limpieza es tolerante a fallos.
        $this->cloudinaryService->eliminarSinInterrumpir($publicId);
    }
}
