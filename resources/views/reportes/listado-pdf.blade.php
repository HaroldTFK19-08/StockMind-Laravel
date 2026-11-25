<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de elementos</title>
    @include('pdfs.partials.estilos')
    <style>@page { size: a4 landscape; }</style>
</head>
<body class="pdf-body">
    <div class="pdf-header">
        <h1 class="pdf-title">Reportes de elementos</h1>
        <div class="pdf-subtitle">StockMind - Consolidado de novedades</div>
        <div class="pdf-small">
            Total: {{ $total }} · Generado: {{ $generado_en?->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="pdf-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Aprendiz</th>
                <th>Elementos</th>
                <th>Responsables</th>
                <th>Uso de los elementos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $reporte)
                <tr>
                    <td class="pdf-nowrap">{{ $reporte['id'] }}</td>
                    <td class="pdf-nowrap">{{ $reporte['fecha_reporte']?->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', (string) $reporte['tipo'])) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', (string) $reporte['estado'])) }}</td>
                    <td>{{ $reporte['aprendiz'] }}</td>
                    <td>{{ $reporte['elementos'] ?: 'Sin elementos asociados' }}</td>
                    <td>{{ $reporte['responsables'] ?: 'Sin responsable registrado' }}</td>
                    <td>{!! nl2br(e($reporte['uso'])) !!}</td>
                </tr>
                <tr>
                    <td colspan="8" class="pdf-small">
                        {{ $reporte['descripcion'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No hay reportes registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pdf-footer">
        Los responsables se resuelven por elemento: aprendiz con asignación activa, instructor con
        préstamo activo, cuenta-dante del ambiente, instructor responsable o almacén.
    </div>
</body>
</html>
