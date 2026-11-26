@extends('pdfs.layout')

@section('titulo', 'Inventario de elementos')

@section('cabecera')
    @include('pdfs.partials.cabecera', [
        'titulo' => 'Inventario de elementos',
        'subtitulo' => 'StockMind - Responsables y uso del inventario',
        'total' => $total,
        'generado_en' => $generado_en,
    ])
@endsection

@section('contenido')
    <table class="pdf-table">
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
                        <span class="pdf-status">{{ ucfirst(str_replace('_', ' ', (string) $elemento['estado'])) }}</span>
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
