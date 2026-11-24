<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte {{ $reporte->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin-bottom: 4px; }
        .header { border-bottom: 2px solid #1f2937; margin-bottom: 20px; padding-bottom: 10px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta td { padding: 7px; border: 1px solid #d1d5db; vertical-align: top; }
        .label { width: 24%; background: #f3f4f6; font-weight: bold; }
        .elements { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .elements th, .elements td { border: 1px solid #d1d5db; padding: 7px; text-align: left; }
        .elements th { background: #1f2937; color: white; }
        .photo { max-width: 100%; max-height: 300px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte #{{ $reporte->id }}</h1>
        <div>StockMind - Registro de novedad</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Aprendiz</td>
            <td>
                @if($reporte->apprentice && $reporte->apprentice->user)
                    {{ $reporte->apprentice->user->nombre_1 }}
                    {{ $reporte->apprentice->user->nombre_2 }}
                    {{ $reporte->apprentice->user->apellido_1 }}
                    {{ $reporte->apprentice->user->apellido_2 }}
                @else
                    No disponible
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Tipo</td>
            <td>{{ ucfirst(str_replace('_', ' ', $reporte->tipo)) }}</td>
        </tr>
        <tr>
            <td class="label">Estado</td>
            <td>{{ ucfirst(str_replace('_', ' ', $reporte->estado)) }}</td>
        </tr>
        <tr>
            <td class="label">Fecha</td>
            <td>{{ $reporte->fecha_reporte?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Descripción</td>
            <td>{{ $reporte->descripcion }}</td>
        </tr>
    </table>

    <h2>Elementos asociados</h2>
    <table class="elements">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporte->elements as $elemento)
                <tr>
                    <td>{{ $elemento->codigo_inventario }}</td>
                    <td>{{ $elemento->nombre }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $elemento->estado)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($reporte->foto_probatorio)
        <h2>Foto probatoria</h2>
        <img class="photo" src="{{ $reporte->foto_probatorio }}" alt="Foto probatoria">
    @endif
</body>
</html>
