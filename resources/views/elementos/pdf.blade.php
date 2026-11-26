<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de elementos</title>
    @include('pdfs.partials.estilos')
    <style>@page { size: a4 landscape; }</style>
</head>
<body class="pdf-body">
    {{-- Datos de identificación del documento. --}}
    @include('pdfs.partials.cabecera', [
        'titulo' => 'Inventario de elementos',
        'subtitulo' => 'StockMind - Responsables y uso del inventario',
        'total' => $total,
        'generado_en' => $generado_en,
    ])

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
                        {{ $elemento['nombre'] }}
                        <div class="pdf-small">
                            @if($elemento['serial']) Serial {{ $elemento['serial'] }} @endif
                            @if($elemento['marca']) · {{ $elemento['marca'] }} @endif
                            @if($elemento['modelo']) · {{ $elemento['modelo'] }} @endif
                        </div>
                    </td>
                    <td>{{ $elemento['categoria'] }}</td>
                    <td>{{ $elemento['ubicacion'] }}</td>
                    <td>
                        {{ ucfirst(str_replace('_', ' ', (string) $elemento['estado'])) }}
                        @if($elemento['condicion'])
                            <div class="pdf-small">Condición: {{ $elemento['condicion'] }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $elemento['responsable'] }}
                        <div class="pdf-small">{{ $elemento['tipo_responsable'] }}</div>
                    </td>
                    <td>
                        {!! nl2br(e($elemento['resumen_uso'])) !!}
                        <div class="pdf-small">
                            @if($elemento['uso']['ultima_asignacion']) Última asignación: {{ $elemento['uso']['ultima_asignacion'] }} @endif
                            @if($elemento['uso']['ultimo_prestamo']) · Último préstamo: {{ $elemento['uso']['ultimo_prestamo'] }} @endif
                            @if($elemento['uso']['ultimo_movimiento']) · Último movimiento: {{ $elemento['uso']['ultimo_movimiento'] }} @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay elementos registrados en el inventario.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.pie', [
        'texto' => 'Responsable actual por prioridad: aprendiz con asignación activa, instructor con préstamo activo, cuenta-dante del ambiente, instructor responsable del ambiente o almacén.',
    ])
</body>
</html>
