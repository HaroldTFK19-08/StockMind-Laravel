@extends('pdfs.layout')

@section('titulo', 'Reportes de elementos')

@section('cabecera')
    @include('pdfs.partials.cabecera', [
        'titulo' => 'Reportes de elementos',
        'subtitulo' => 'StockMind - Consolidado de novedades',
        'total' => $total,
        'generado_en' => $generado_en,
    ])
@endsection

@section('contenido')
    <table class="pdf-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha y novedad</th>
                <th>Estado</th>
                <th>Aprendiz</th>
                <th>Elementos y responsables</th>
                <th>Uso de los elementos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $reporte)
                <tr>
                    <td class="pdf-nowrap">{{ $reporte['id'] }}</td>
                    <td>
                        <strong class="pdf-cell-title">{{ $reporte['fecha_reporte']?->format('d/m/Y H:i') ?? 'N/D' }}</strong>
                        <span class="pdf-cell-detail">{{ ucfirst(str_replace('_', ' ', (string) $reporte['tipo'])) }}</span>
                        <span class="pdf-report-description">{!! nl2br(e($reporte['descripcion'])) !!}</span>
                    </td>
                    <td><span class="pdf-status">{{ ucfirst(str_replace('_', ' ', (string) $reporte['estado'])) }}</span></td>
                    <td>{{ $reporte['aprendiz'] }}</td>
                    <td>
                        <strong class="pdf-cell-title">{{ $reporte['elementos'] ?: 'Sin elementos asociados' }}</strong>
                        <span class="pdf-cell-detail">{{ $reporte['responsables'] ?: 'Sin responsable registrado' }}</span>
                    </td>
                    <td>{!! nl2br(e($reporte['uso'])) !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="pdf-empty">No hay reportes registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('pie')
    @include('pdfs.partials.pie', [
        'texto' => 'Los responsables se resuelven por elemento: aprendiz con asignación activa, instructor con préstamo activo, cuenta-dante del ambiente, instructor responsable o almacén.',
    ])
@endsection
