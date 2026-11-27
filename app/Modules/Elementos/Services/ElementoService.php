<?php

namespace App\Modules\Elementos\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\Concerns\GeneraPdf;
use App\Modules\Shared\Services\Concerns\GestionaImagenes;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Shared\Services\PDFService;
use App\Modules\Shared\Services\ResponsablesElementoService;

class ElementoService
{
    // El trait aporta pdfVer() y pdfDescargar(); los datos los construye este servicio.
    use GeneraPdf;

    // Subida, transacción y limpieza de imágenes se comparten con otros servicios.
    use GestionaImagenes;

    protected function vistaPdf(): string
    {
        return 'elementos.pdf';
    }

    protected function nombrePdf(): string
    {
        return 'inventario.pdf';
    }

    public function __construct(
        private CloudinaryService $cloudinaryService,
        private CacheService $cache,
        private NotificacionService $notificacionService,
        private PDFService $pdfService,
        private ResponsablesElementoService $responsablesElementoService
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

    public function historial(string $codigo): array
    {
        $elemento = Element::query()
            ->select(['id', 'codigo_inventario', 'nombre'])
            ->where('codigo_inventario', $codigo)
            ->with([
                'loans.teacher.user:id,nombre_1,nombre_2,apellido_1,apellido_2',
                'assignments.apprentice.user:id,nombre_1,nombre_2,apellido_1,apellido_2',
                'assignments.teacher.user:id,nombre_1,nombre_2,apellido_1,apellido_2',
            ])
            ->firstOrFail();

        $nombre = static fn ($usuario): string => collect([
            $usuario->nombre_1,
            $usuario->nombre_2,
            $usuario->apellido_1,
            $usuario->apellido_2,
        ])->filter()->implode(' ');

        $prestamos = $elemento->loans->map(fn ($prestamo) => [
            'tipo' => 'Préstamo',
            'persona' => $nombre($prestamo->teacher->user),
            'tipo_persona' => 'Instructor',
            'instructor_responsable' => null,
            'fecha_entrega' => $prestamo->fecha_prestamo,
            'fecha_devolucion_esperada' => $prestamo->fecha_devolucion_esperada,
            'fecha_devolucion_real' => $prestamo->fecha_devolucion_real,
            'estado' => $prestamo->estado,
            'observaciones' => $prestamo->observaciones,
        ]);

        $asignaciones = $elemento->assignments->map(fn ($asignacion) => [
            'tipo' => 'Asignación',
            'persona' => $nombre($asignacion->apprentice->user),
            'tipo_persona' => 'Aprendiz',
            'instructor_responsable' => $nombre($asignacion->teacher->user),
            'fecha_entrega' => $asignacion->fecha_asignacion,
            'fecha_devolucion_esperada' => $asignacion->fecha_devolucion_esperada,
            'fecha_devolucion_real' => $asignacion->fecha_devolucion_real,
            'estado' => $asignacion->estado,
            'observaciones' => $asignacion->observaciones,
        ]);

        return [
            'elemento' => $elemento->only(['codigo_inventario', 'nombre']),
            'historial' => $prestamos->concat($asignaciones)
                ->sortByDesc('fecha_entrega')
                ->values()
                ->all(),
        ];
    }

    public function inventarioPorSedeAlmacenCategoria()
    {
        return Element::query()
            ->join('warehouses as w', 'w.id', '=', 'elements.warehouse_id')
            ->join('headquarters as h', 'h.id', '=', 'w.headquarter_id')
            ->join('categories as c', 'c.id', '=', 'elements.category_id')
            ->selectRaw(
                'h.id as sede_id, h.nombre as sede, w.id as almacen_id, ' .
                'w.nombre as almacen, c.id as categoria_id, c.nombre as categoria, COUNT(*) as cantidad'
            )
            ->groupBy(['h.id', 'h.nombre', 'w.id', 'w.nombre', 'c.id', 'c.nombre'])
            ->orderBy('h.nombre')
            ->orderBy('w.nombre')
            ->orderBy('c.nombre')
            ->toBase()
            ->get();
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

        $elemento = $this->transaccionConImagen(
            $foto,
            'elementos',
            fn (array $imagen): Element => Element::create([
                ...$datos,
                'img_elemento' => $imagen['url'],
                'img_elemento_public_id' => $imagen['public_id'],
            ])
        );

        $this->notificarCambioStock(
            $elemento,
            'Nuevo elemento en el inventario',
            "Se registró el elemento {$elemento->nombre} con estado {$elemento->estado}."
        );

        $this->cache->invalidar('elementos');

        return $this->buscar($elemento->id);
    }

    public function actualizar(int $id, array $datos): Element
    {
        $elementoAnterior = Element::findOrFail($id);
        $publicIdAnterior = $elementoAnterior->img_elemento_public_id;
        $estadoAnterior = $elementoAnterior->estado;
        $foto = $datos['img_elemento'] ?? null;
        unset($datos['img_elemento']);

        $this->transaccionConImagen(
            $foto,
            'elementos',
            function (?array $imagen) use ($id, &$datos) {
                if ($imagen !== null) {
                    $datos['img_elemento'] = $imagen['url'];
                    $datos['img_elemento_public_id'] = $imagen['public_id'];
                }

                $elemento = Element::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $this->validarEstado($elemento, $datos);
                $elemento->update($datos);
            },
            $publicIdAnterior
        );

        // Una sola lectura fresca para notificar y responder.
        $elementoActualizado = $this->buscar($id);

        if (($datos['estado'] ?? $estadoAnterior) !== $estadoAnterior) {
            $this->notificarCambioStock(
                $elementoActualizado,
                'Actualización de stock',
                "El elemento {$elementoActualizado->nombre} ahora está {$elementoActualizado->estado}."
            );
        }

        $this->cache->invalidar('elementos');

        return $elementoActualizado;
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
            $this->cloudinaryService->eliminarSinInterrumpir($publicId);
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

    /**
     * Avisa a los aprendices y al cuenta-dante del ambiente cuando cambia el
     * inventario (ASMR-37).
     */
    private function notificarCambioStock(
        Element $elemento,
        string $titulo,
        string $mensaje
    ): void {
        $destinatarios = $this->notificacionService->idsConRol('Aprendiz');

        if ($elemento->environment_id !== null) {
            $destinatarios = array_merge(
                $destinatarios,
                $this->notificacionService->idsCuentadantesDeAmbiente($elemento->environment_id)
            );
        }

        $this->notificacionService->aUsuarios(
            $destinatarios,
            $titulo,
            $mensaje,
            TipoNotificacion::StockActualizado,
            [
                'elemento_id' => $elemento->id,
                'ambiente_id' => $elemento->environment_id,
                'estado' => $elemento->estado,
            ]
        );
    }

    /**
     * @return array{elementos: array<int, array<string, mixed>>, total: int, generado_en: \Illuminate\Support\Carbon}
     */
    private function datosParaPdf(): array
    {
        $elementos = Element::query()
            ->with(ResponsablesElementoService::relacionesParaPdf())
            ->orderBy('codigo_inventario')
            ->get();

        return [
            'elementos' => $this->responsablesElementoService->preparar($elementos),
            'total' => $elementos->count(),
            'generado_en' => now(),
        ];
    }

}
