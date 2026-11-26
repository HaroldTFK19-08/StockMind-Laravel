@extends('pdfs.layout')

@section('titulo', 'Asignaciones de elementos')

@section('cabecera')
    @include('pdfs.partials.cabecera', [
        'titulo' => 'Asignaciones de elementos',
        'subtitulo' => 'StockMind - Responsables y uso por asignación',
        'total' => $total,
        'generado_en' => $generado_en,
    ])
@endsection

@section('contenido')
    <table class="pdf-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Elemento</th>
                <th>Aprendiz</th>
                <th>Instructor</th>
                <th>Fechas</th>
                <th>Estado</th>
                <th>Responsable actual y uso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $asignacion)
                <tr>
                    <td class="pdf-nowrap">{{ $asignacion['id'] }}</td>
                    <td>
                        <strong class="pdf-cell-title">{{ $asignacion['elemento'] }}</strong>
                        <span class="pdf-cell-detail">{{ $asignacion['codigo'] }}</span>
                    </td>
                    <td>{{ $asignacion['aprendiz'] }}</td>
                    <td>{{ $asignacion['instructor'] }}</td>
                    <td>
                        <strong class="pdf-cell-title">{{ $asignacion['fecha_asignacion']?->format('d/m/Y') ?? 'N/D' }}</strong>
                        <span class="pdf-cell-detail">
                            Devolución esperada: {{ $asignacion['fecha_devolucion_esperada']?->format('d/m/Y') ?? 'N/D' }}
                        </span>
                        @if($asignacion['fecha_devolucion_real'])
                            <span class="pdf-cell-detail">Devolución real: {{ $asignacion['fecha_devolucion_real']->format('d/m/Y') }}</span>
                        @endif
                    </td>
                    <td><span class="pdf-status">{{ ucfirst(str_replace('_', ' ', (string) $asignacion['estado'])) }}</span></td>
                    <td>
                        <strong class="pdf-cell-title">{{ $asignacion['responsable'] }}</strong>
                        <span class="pdf-cell-detail">{{ $asignacion['tipo_responsable'] }}</span>
                        <span class="pdf-cell-detail">{{ $asignacion['resumen_uso'] }}</span>
                        @if($asignacion['observaciones'])
                            <span class="pdf-cell-detail">{!! nl2br(e($asignacion['observaciones'])) !!}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="pdf-empty">No hay asignaciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('pie')
    @include('pdfs.partials.pie', [
        'texto' => 'El responsable actual se calcula sobre el elemento asignado. El uso incluye el histórico de asignaciones, préstamos, movimientos y reportes de ese elemento.',
    ])
@endsection
