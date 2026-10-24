<?php

namespace App\Modules\Reportes\Services;

use App\Modules\Reportes\Models\Report;
use App\Modules\Shared\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReporteService
{
    public function __construct(
        private CloudinaryService $cloudinaryService
    ) {}

    public function listar()
    {
        return Report::with(['apprentice.user', 'elements'])
            ->withCount('elements')
            ->latest('fecha_reporte')
            ->paginate(10);
    }

    public function mostrar(int $id): Report
    {
        return Report::with(['apprentice.user', 'elements'])
            ->withCount('elements')
            ->findOrFail($id);
    }

    public function crear(array $datos): Report
    {
        $foto = $datos['foto_probatorio'];
        $elementIds = $datos['element_ids'];
        unset($datos['foto_probatorio'], $datos['element_ids']);

        $imagen = $this->cloudinaryService->subir($foto, 'reportes');

        try {
            $reporte = DB::transaction(function () use ($datos, $elementIds, $imagen) {
                $reporte = Report::create([
                    ...$datos,
                    'foto_probatorio' => $imagen['url'],
                    'foto_probatorio_public_id' => $imagen['public_id'],
                ]);
                $reporte->elements()->sync($elementIds);

                return $reporte;
            });
        } catch (Throwable $e) {
            $this->eliminarImagenSinInterrumpir($imagen['public_id']);
            throw $e;
        }

        return $this->mostrar($reporte->id);
    }

    public function actualizar(int $id, array $datos): Report
    {
        $reporte = $this->mostrar($id);
        $publicIdAnterior = $reporte->foto_probatorio_public_id;
        $foto = $datos['foto_probatorio'] ?? null;
        $elementIds = $datos['element_ids'] ?? null;
        unset($datos['foto_probatorio'], $datos['element_ids']);
        $imagenNueva = null;

        if ($foto instanceof UploadedFile) {
            $imagenNueva = $this->cloudinaryService->subir($foto, 'reportes');
            $datos['foto_probatorio'] = $imagenNueva['url'];
            $datos['foto_probatorio_public_id'] = $imagenNueva['public_id'];
        }

        try {
            DB::transaction(function () use ($reporte, $datos, $elementIds) {
                $reporte->update($datos);

                if ($elementIds !== null) {
                    $reporte->elements()->sync($elementIds);
                }
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

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $reporte = $this->mostrar($id);
        $publicId = $reporte->foto_probatorio_public_id;
        $reporte->delete();

        if ($publicId) {
            $this->eliminarImagenSinInterrumpir($publicId);
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
