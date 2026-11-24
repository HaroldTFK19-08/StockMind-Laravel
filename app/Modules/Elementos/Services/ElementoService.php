<?php

namespace App\Modules\Elementos\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Modules\Shared\Services\CacheService;

class ElementoService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private CacheService $cache
    ) {}

    // Listado paginado: las primeras páginas salen de Redis
    public function listar()
    {
        return $this->cache->recordarPagina(
            'elementos',
            CacheService::TTL_ELEMENTOS,
            10,
            fn () => Element::with(['category', 'environment', 'warehouse'])
                ->withCount(['assignments', 'loans', 'motions', 'reports'])
                ->latest()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'elementos',
            "detalle:{$id}",
            CacheService::TTL_ELEMENTOS,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan crear, actualizar y eliminar
    // para trabajar siempre con datos frescos
    private function buscar(int $id): Element
    {
        return Element::with(['category', 'environment', 'warehouse'])
            ->withCount(['assignments', 'loans', 'motions', 'reports'])
            ->findOrFail($id);
    }

    public function crear(array $datos): Element
    {
        $foto = $datos['img_elemento'];
        $estado = $datos['estado'] ?? 'disponible';
        unset($datos['img_elemento']);

        if (in_array($estado, ['asignado', 'prestado'], true)) {
            throw new ReglasNegocioExceptions(
                'El estado asignado o prestado debe gestionarse desde el módulo correspondiente.'
            );
        }

        $imagen = $this->cloudinaryService->subir($foto, 'elementos');

        try {
            $elemento = DB::transaction(function () use ($datos, $imagen) {
                return Element::create([
                    ...$datos,
                    'img_elemento' => $imagen['url'],
                    'img_elemento_public_id' => $imagen['public_id'],
                ]);
            });
        } catch (Throwable $e) {
            $this->eliminarImagenSinInterrumpir($imagen['public_id']);
            throw $e;
        }

        $this->cache->invalidar('elementos');

        return $this->buscar($elemento->id);
    }

    public function actualizar(int $id, array $datos): Element
    {
        $publicIdAnterior = Element::findOrFail($id)->img_elemento_public_id;
        $foto = $datos['img_elemento'] ?? null;
        unset($datos['img_elemento']);
        $imagenNueva = null;

        if ($foto instanceof UploadedFile) {
            $imagenNueva = $this->cloudinaryService->subir($foto, 'elementos');
            $datos['img_elemento'] = $imagenNueva['url'];
            $datos['img_elemento_public_id'] = $imagenNueva['public_id'];
        }

        try {
            DB::transaction(function () use ($id, $datos) {
                $elemento = Element::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $this->validarEstado($elemento, $datos);
                $elemento->update($datos);
            });
        } catch (Throwable $e) {
            if ($imagenNueva !== null) {
                $this->eliminarImagenSinInterrumpir($imagenNueva['public_id']);
            }

            throw $e;
        }

        if ($imagenNueva !== null && $publicIdAnterior) {
            $this->eliminarImagenSinInterrumpir($publicIdAnterior);
        }

        $this->cache->invalidar('elementos');

        return $this->buscar($id);
    }

    public function eliminar(int $id): void
    {
        $elemento = $this->buscar($id);

        if (
            $elemento->assignments()->exists()
            || $elemento->loans()->exists()
            || $elemento->motions()->exists()
            || $elemento->reports()->exists()
        ) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el elemento porque tiene asignaciones, préstamos, movimientos o reportes asociados.'
            );
        }

        $publicId = $elemento->img_elemento_public_id;
        $elemento->delete();

        $this->cache->invalidar('elementos');

        if ($publicId) {
            $this->eliminarImagenSinInterrumpir($publicId);
        }
    }

    private function validarEstado(Element $elemento, array $datos): void
    {
        $estado = $datos['estado'] ?? null;

        if ($estado === null) {
            return;
        }

        $tieneAsignacionActiva = Assignment::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activa', 'vencida'])
            ->lockForUpdate()
            ->get(['id'])
            ->isNotEmpty();
        $tienePrestamoActivo = Loan::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activo', 'vencido'])
            ->lockForUpdate()
            ->get(['id'])
            ->isNotEmpty();

        if ($tieneAsignacionActiva && $estado !== 'asignado') {
            throw new ReglasNegocioExceptions(
                'No se puede cambiar el estado mientras el elemento tenga una asignación activa.'
            );
        }

        if ($tienePrestamoActivo && $estado !== 'prestado') {
            throw new ReglasNegocioExceptions(
                'No se puede cambiar el estado mientras el elemento tenga un préstamo activo.'
            );
        }

        if ($estado === 'asignado' && !$tieneAsignacionActiva) {
            throw new ReglasNegocioExceptions(
                'El estado asignado debe gestionarse desde el módulo de asignaciones.'
            );
        }

        if ($estado === 'prestado' && !$tienePrestamoActivo) {
            throw new ReglasNegocioExceptions(
                'El estado prestado debe gestionarse desde el módulo de préstamos.'
            );
        }
    }

    private function eliminarImagenSinInterrumpir(string $publicId): void
    {
        try {
            $this->cloudinaryService->eliminar($publicId);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
