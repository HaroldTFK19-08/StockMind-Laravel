<?php

namespace App\Modules\Reportes\Services;

use App\Modules\Elementos\Models\Element;
use App\Modules\Reportes\Models\Report;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\Concerns\GeneraPdf;
use App\Modules\Shared\Services\Concerns\GestionaImagenes;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Shared\Services\PDFService;
use App\Modules\Shared\Services\ResponsablesElementoService;

class ReporteService
{
    // El trait aporta los helpers ver/descargar para el PDF general de reportes.
    use GeneraPdf;

    // Subida, transacción y limpieza de imágenes se comparten con otros servicios.
    use GestionaImagenes;

    public function __construct(
        private CloudinaryService $cloudinaryService,
        private CacheService $cache,
        private PDFService $pdfService,
        private NotificacionService $notificacionService,
        private ResponsablesElementoService $responsablesElementoService
    ) {}

    private const REPORT_USER_FIELDS = [
        'id',
        'nombre_1',
        'nombre_2',
        'apellido_1',
        'apellido_2',
        'email',
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

        $reporte = $this->transaccionConImagen(
            $foto,
            'reportes',
            function (array $imagen) use ($datos, $elementIds) {
                $reporte = Report::create([
                    ...$datos,
                    'foto_probatorio' => $imagen['url'],
                    'foto_probatorio_public_id' => $imagen['public_id'],
                ]);
                $reporte->elements()->sync($elementIds);
                $this->notificarReporteCreado($reporte, $elementIds);

                return $reporte;
            }
        );

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

        $this->transaccionConImagen(
            $foto,
            'reportes',
            function (?array $imagen) use ($reporte, &$datos, $elementIds) {
                if ($imagen !== null) {
                    $datos['foto_probatorio'] = $imagen['url'];
                    $datos['foto_probatorio_public_id'] = $imagen['public_id'];
                }

                $reporte->update($datos);

                if ($elementIds !== null) {
                    $reporte->elements()->sync($elementIds);
                }
            },
            $publicIdAnterior
        );

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
            $this->cloudinaryService->eliminarSinInterrumpir($publicId);
        }
    }

    public function pdfListadoDescargar()
    {
        return $this->descargarPdf($this->datosListadoParaPdf(), 'reportes.listado-pdf', 'reportes.pdf');
    }

    public function pdfListadoVer()
    {
        return $this->verPdf($this->datosListadoParaPdf(), 'reportes.listado-pdf', 'reportes.pdf');
    }

    /**
     * @return array{reportes: array<int, array<string, mixed>>, total: int, generado_en: \Illuminate\Support\Carbon}
     */
    private function datosListadoParaPdf(): array
    {
        $reportes = Report::query()
            ->with($this->relacionesParaPdf())
            ->latest('fecha_reporte')
            ->get();

        $filas = $reportes->map(function (Report $reporte) {
            $fichas = $this->responsablesElementoService->preparar($reporte->elements);
            // La suma de uso vive en el servicio compartido de fichas.
            $uso = $this->responsablesElementoService->sumarUso($fichas);
            $responsables = array_values(array_unique(array_filter(
                array_column($fichas, 'responsable')
            )));

            return [
                'id' => $reporte->id,
                'fecha_reporte' => $reporte->fecha_reporte,
                'tipo' => $reporte->tipo,
                'estado' => $reporte->estado,
                'descripcion' => $reporte->descripcion,
                'aprendiz' => ResponsablesElementoService::nombreCompleto($reporte->apprentice?->user)
                    ?? 'Sin aprendiz registrado',
                'elementos' => collect($fichas)
                    ->map(fn (array $ficha) => $ficha['codigo'] . ' - ' . $ficha['nombre'])
                    ->implode(', '),
                'responsables' => implode(', ', $responsables),
                'uso' => $this->responsablesElementoService->resumenUso($uso),
            ];
        })->all();

        return [
            'reportes' => $filas,
            'total' => $reportes->count(),
            'generado_en' => now(),
        ];
    }

    private function relacionesParaPdf(): array
    {
        return [
            'apprentice' => static fn ($query) => $query
                ->select(['id', 'user_id', 'coursegroup_id', 'estado']),
            'apprentice.user' => static fn ($query) => $query->select(self::REPORT_USER_FIELDS),
            'elements' => static fn ($query) => $query
                ->with(ResponsablesElementoService::relacionesParaPdf()),
        ];
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

        $ambienteIds = $elementos->pluck('environment_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($ambienteIds !== []) {
            $destinatarios = array_merge(
                $destinatarios,
                $this->notificacionService->idsCuentadantesDeAmbientes($ambienteIds),
                $this->notificacionService->idsInstructoresDeAmbientes($ambienteIds)
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

}
