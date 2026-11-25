<?php

namespace App\Modules\Reportes\Services;

use App\Modules\Elementos\Models\Element;
use App\Modules\Reportes\Models\Report;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Shared\Services\PDFService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Modules\Shared\Services\CacheService;

class ReporteService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private CacheService $cache,
        private PDFService $pdfService,
        private NotificacionService $notificacionService
    ) {}

    private const REPORT_USER_FIELDS = [
        'id',
        'nombre_1',
        'nombre_2',
        'apellido_1',
        'apellido_2',
        'estado',
    ];

    public function listar()
    {
        return $this->withReportRelations(Report::query())
            ->withCount('elements')
            ->latest('fecha_reporte')
            ->paginate(10);
    }

    public function mostrar(int $id): Report
    {
        return $this->withReportRelations(Report::query())
            ->withCount('elements')
            ->findOrFail($id);
    }

    private function withReportRelations($query)
    {
        return $query->with([
            'apprentice' => static fn($q) => $q->select(['id', 'user_id', 'coursegroup_id', 'estado']),
            'apprentice.user' => static fn($q) => $q->select(self::REPORT_USER_FIELDS),
            'elements',
        ]);
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
                $this->notificarReporteCreado($reporte, $elementIds);

                return $reporte;
            });
        } catch (Throwable $e) {
            $this->eliminarImagenSinInterrumpir($imagen['public_id']);
            throw $e;
        }

        $this->cache->invalidar('reportes');

        return $this->mostrar($reporte->id);
    }

    public function actualizar(int $id, array $datos): Report
    {
        $reporte = $this->mostrar($id);
        $publicIdAnterior = $reporte->foto_probatorio_public_id;
        $estadoAnterior = $reporte->estado;
        $elementosAnteriores = $reporte->elements()->pluck('elements.id')->all();
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

        $elementosNuevos = $elementIds === null
            ? []
            : array_values(array_diff($elementIds, $elementosAnteriores));
        $estadoNuevo = $datos['estado'] ?? $estadoAnterior;

        if ($estadoNuevo !== $estadoAnterior) {
            $this->notificarEstadoReporte($reporte, $estadoAnterior, $estadoNuevo);
        }

        if ($elementosNuevos !== []) {
            $this->notificarReporteCreado(
                $reporte->refresh(),
                $elementosNuevos,
                'Se agregaron elementos a un reporte',
                TipoNotificacion::ReporteActualizado
            );
        }

        if ($imagenNueva !== null && $publicIdAnterior) {
            $this->eliminarImagenSinInterrumpir($publicIdAnterior);
        }

        $this->cache->invalidar('reportes');

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $reporte = $this->mostrar($id);
        $publicId = $reporte->foto_probatorio_public_id;
        $reporte->delete();

        $this->cache->invalidar('reportes');

        if ($publicId) {
            $this->eliminarImagenSinInterrumpir($publicId);
        }
    }

    public function pdfDescargar(int $id)
    {
        $reporte = $this->mostrar($id);

        return $this->pdfService->descargar(
            'reportes.pdf',
            ['reporte' => $reporte],
            'reporte-' . $reporte->id . '.pdf'
        );
    }

    public function pdfVer(int $id)
    {
        $reporte = $this->mostrar($id);

        return $this->pdfService->ver(
            'reportes.pdf',
            ['reporte' => $reporte],
            'reporte-' . $reporte->id . '.pdf'
        );
    }

    /**
     * Avisa al cuenta-dante dueño del ambiente, al instructor responsable y
     * a los administradores sobre un reporte de un elemento (ISMR-06, ISMR-21).
     *
     * @param  array<int, int>  $elementIds
     */
    private function notificarReporteCreado(
        Report $reporte,
        array $elementIds,
        string $titulo = 'Nuevo reporte de elemento',
        TipoNotificacion $tipo = TipoNotificacion::ReporteCreado
    ): void {
        $elementos = Element::query()
            ->whereIn('id', $elementIds)
            ->get(['id', 'nombre', 'environment_id']);

        $nombreElemento = $elementos->first()?->nombre ?? 'elemento';
        $mensaje = "Reporte de {$reporte->tipo}: {$reporte->descripcion}. Elemento: {$nombreElemento}.";
        $data = [
            'reporte_id' => $reporte->id,
            'tipo_reporte' => $reporte->tipo,
            'estado' => $reporte->estado,
            'elementos' => $elementos->map(fn (Element $elemento) => [
                'id' => $elemento->id,
                'nombre' => $elemento->nombre,
            ])->all(),
        ];

        $destinatarios = $this->notificacionService->idsConRol('Administrador');

        foreach ($elementos->pluck('environment_id')->filter()->unique() as $ambienteId) {
            $destinatarios = array_merge(
                $destinatarios,
                $this->notificacionService->idsCuentadantesDeAmbiente((int) $ambienteId),
                $this->notificacionService->idsInstructoresDeAmbiente((int) $ambienteId)
            );
        }

        $this->notificacionService->aUsuarios(
            $destinatarios,
            $titulo,
            $mensaje,
            $tipo,
            $data,
            [$reporte->apprentice?->user_id]
        );
    }

    private function notificarEstadoReporte(
        Report $reporte,
        string $estadoAnterior,
        string $estadoNuevo
    ): void {
        $usuarioId = $reporte->apprentice?->user_id;

        if ($usuarioId === null) {
            return;
        }

        $this->notificacionService->aUsuario(
            $usuarioId,
            'El estado de tu reporte cambió',
            "Tu reporte pasó de {$estadoAnterior} a {$estadoNuevo}.",
            TipoNotificacion::ReporteActualizado,
            [
                'reporte_id' => $reporte->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
            ]
        );
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
