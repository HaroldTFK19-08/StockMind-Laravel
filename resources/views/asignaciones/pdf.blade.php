<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaciones de elementos</title>
    @include('pdfs.partials.estilos')
    <style>@page { size: a4 landscape; }</style>
</head>
<body class="pdf-body">
    <div class="pdf-header">
        <h1 class="pdf-title">Asignaciones de elementos</h1>
        <div class="pdf-subtitle">StockMind - Responsables y uso por asignación</div>
        <div class="pdf-small">
            Total: {{ $total }} · Generado: {{ $generado_en?->format('d/m/Y H:i') }}
        </div>
    </div>

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
                        {{ $asignacion['codigo'] }} - {{ $asignacion['elemento'] }}
                    </td>
                    <td>{{ $asignacion['aprendiz'] }}</td>
                    <td>{{ $asignacion['instructor'] }}</td>
                    <td>
                        Asignación: {{ $asignacion['fecha_asignacion']?->format('d/m/Y') }}
                        <div class="pdf-small">
                            Devolución esperada: {{ $asignacion['fecha_devolucion_esperada']?->format('d/m/Y') }}
                        </div>
                        @if($asignacion['fecha_devolucion_real'])
                            <div class="pdf-small">Devolución real: {{ $asignacion['fecha_devolucion_real']?->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', (string) $asignacion['estado'])) }}</td>
                    <td>
                        {{ $asignacion['responsable'] }}
                        <div class="pdf-small">{{ $asignacion['tipo_responsable'] }}</div>
                        <div class="pdf-small">{{ $asignacion['resumen_uso'] }}</div>
                        @if($asignacion['observaciones'])
                            <div class="pdf-small">Observaciones: {{ $asignacion['observaciones'] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay asignaciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pdf-footer">
        El responsable actual se calcula sobre el elemento asignado. El uso incluye el histórico de
        asignaciones, préstamos, movimientos y reportes de ese elemento.
    </div>
</body>
</html>
