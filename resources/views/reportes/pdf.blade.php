<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte {{ $reporte->id }}</title>
    @include('pdfs.partials.estilos')
</head>
<body class="pdf-body">
    {{-- Datos de identificación del documento. --}}
    @include('pdfs.partials.cabecera', [
        'titulo' => "Reporte #{$reporte->id}",
        'subtitulo' => 'StockMind - Registro de novedad',
        'generado_en' => $generado_en,
    ])

    <table class="pdf-meta">
        <tr>
            <td class="pdf-label">Aprendiz</td>
            {{-- El modelo User centraliza el formato del nombre. --}}
            <td>{{ $reporte->apprentice?->user?->nombre_completo ?? 'No disponible' }}</td>
        </tr>
        <tr>
            <td class="pdf-label">Tipo</td>
            <td>{{ ucfirst(str_replace('_', ' ', $reporte->tipo)) }}</td>
        </tr>
        <tr>
            <td class="pdf-label">Estado</td>
            <td>{{ ucfirst(str_replace('_', ' ', $reporte->estado)) }}</td>
        </tr>
        <tr>
            <td class="pdf-label">Fecha del reporte</td>
            <td>{{ $reporte->fecha_reporte?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="pdf-label">Descripción</td>
            <td>{!! nl2br(e($reporte->descripcion)) !!}</td>
        </tr>
    </table>

    <h2 class="pdf-section">Elementos asociados, responsables y uso</h2>
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Elemento</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Uso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($elementos ?? [] as $elemento)
                <tr>
                    <td class="pdf-nowrap">{{ $elemento['codigo'] }}</td>
                    <td>
                        {{ $elemento['nombre'] }}
                        <div class="pdf-small">
                            {{ $elemento['categoria'] }}
                            @if($elemento['serial']) · Serial {{ $elemento['serial'] }} @endif
                            @if($elemento['marca']) · {{ $elemento['marca'] }} @endif
                        </div>
                    </td>
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
                        @if($elemento['uso']['ultima_asignacion'])
                            <div class="pdf-small">Última asignación: {{ $elemento['uso']['ultima_asignacion'] }}</div>
                        @endif
                        @if($elemento['uso']['ultimo_reporte'])
                            <div class="pdf-small">Último reporte: {{ $elemento['uso']['ultimo_reporte'] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Este reporte no tiene elementos asociados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($reporte->foto_probatorio)
        <h2 class="pdf-section">Foto probatoria</h2>
        <img class="pdf-photo" src="{{ $reporte->foto_probatorio }}" alt="Foto probatoria">
    @endif

    @include('pdfs.partials.pie', [
        'texto' => 'Responsable actual por prioridad: aprendiz con asignación activa, instructor con préstamo activo, cuenta-dante del ambiente, instructor responsable del ambiente o almacén.',
    ])
</body>
</html>
