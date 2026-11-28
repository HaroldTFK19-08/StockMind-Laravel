@extends('pdfs.layout')

@section('titulo', 'Inventario de elementos')

@php
    $ubicaciones = collect($elementos)
        ->pluck('ubicacion')
        ->filter()
        ->unique()
        ->values();
    $ubicacionInventario = $ubicaciones->count() === 1
        ? $ubicaciones->first()
        : ($ubicaciones->isEmpty() ? 'N/D' : 'Varias ubicaciones');
    $conteosEstado = collect($elementos)->countBy(
        static fn (array $elemento): string => strtolower(
            str_replace('_', ' ', (string) ($elemento['estado'] ?? ''))
        )
    );
@endphp

@section('cabecera')
    @include('pdfs.partials.cabecera', [
        'titulo' => 'Inventario de elementos',
        'subtitulo' => 'StockMind - Responsables y uso del inventario institucional',
        'total' => $total,
        'generado_en' => $generado_en,
        'metadatosAdicionales' => [
            ['etiqueta' => 'UBICACIÓN', 'valor' => $ubicacionInventario],
            ['etiqueta' => 'TIPO DE INFORME', 'valor' => 'Inventario general'],
        ],
    ])
@endsection

@section('contenido')
    <table class="pdf-summary-grid">
        <tr>
            <td>
                <div class="pdf-stat-card pdf-stat-card--available">
                    <span class="pdf-stat-label">DISPONIBLES</span>
                    <table class="pdf-stat-details">
                        <tr>
                            <td><strong class="pdf-stat-value">{{ $conteosEstado->get('disponible', 0) }}</strong></td>
                            <td class="pdf-stat-note">Listos para asignar</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="pdf-stat-card pdf-stat-card--loan">
                    <span class="pdf-stat-label">EN PRÉSTAMO ACTIVO</span>
                    <table class="pdf-stat-details">
                        <tr>
                            <td><strong class="pdf-stat-value">{{ $conteosEstado->get('prestado', 0) }}</strong></td>
                            <td class="pdf-stat-note">En uso temporal</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="pdf-stat-card pdf-stat-card--maintenance">
                    <span class="pdf-stat-label">EN MANTENIMIENTO</span>
                    <table class="pdf-stat-details">
                        <tr>
                            <td><strong class="pdf-stat-value">{{ $conteosEstado->get('en mantenimiento', 0) }}</strong></td>
                            <td class="pdf-stat-note">Revisión técnica</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="pdf-table pdf-table--inventory">
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th>Código</th>
                <th>Elemento</th>
                <th>Categoría</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Uso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($elementos as $elemento)
                <tr>
                    <td class="pdf-nowrap">{{ $elemento['codigo'] }}</td>
                    <td>
                        <strong class="pdf-cell-title">{{ $elemento['nombre'] }}</strong>
                        @php
                            $ficha = collect([
                                $elemento['serial'] ? "Serial {$elemento['serial']}" : null,
                                $elemento['marca'],
                                $elemento['modelo'],
                            ])->filter()->implode(' · ');
                        @endphp
                        @if($ficha !== '')
                            <div class="pdf-small">{{ $ficha }}</div>
                        @endif
                    </td>
                    <td>{{ $elemento['categoria'] }}</td>
                    <td>{{ $elemento['ubicacion'] }}</td>
                    <td>
                        @php
                            $estadoNormalizado = strtolower(str_replace('_', ' ', (string) $elemento['estado']));
                            $claseEstado = match ($estadoNormalizado) {
                                'disponible' => ' pdf-status--available',
                                'prestado' => ' pdf-status--loan',
                                'en mantenimiento' => ' pdf-status--maintenance',
                                default => '',
                            };
                        @endphp
                        <span class="pdf-status{{ $claseEstado }}">{{ ucfirst($estadoNormalizado) }}</span>
                        @if($elemento['condicion'])
                            <span class="pdf-cell-detail">Condición: {{ $elemento['condicion'] }}</span>
                        @endif
                    </td>
                    <td>
                        <strong class="pdf-cell-title">{{ $elemento['responsable'] }}</strong>
                        <span class="pdf-cell-detail">{{ $elemento['tipo_responsable'] }}</span>
                    </td>
                    <td>
                        {!! nl2br(e($elemento['resumen_uso'])) !!}
                        @php
                            $fechas = collect([
                                $elemento['uso']['ultima_asignacion'] ? "Última asignación: {$elemento['uso']['ultima_asignacion']}" : null,
                                $elemento['uso']['ultimo_prestamo'] ? "Último préstamo: {$elemento['uso']['ultimo_prestamo']}" : null,
                                $elemento['uso']['ultimo_movimiento'] ? "Último movimiento: {$elemento['uso']['ultimo_movimiento']}" : null,
                            ])->filter()->implode(' · ');
                        @endphp
                        @if($fechas !== '')
                                <span class="pdf-cell-detail">{{ $fechas }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="pdf-empty">No hay elementos registrados en el inventario.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('pie')
    @include('pdfs.partials.pie', [
        'texto' => 'Responsable actual por prioridad: aprendiz con asignación activa, instructor con préstamo activo, cuenta-dante del ambiente, instructor responsable del ambiente o almacén.',
    ])
@endsection
