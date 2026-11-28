{{-- Cabecera común: identidad, título y datos de emisión del documento. --}}
@php
    $metadatosDocumento = [
        ['etiqueta' => 'REGISTROS INCLUIDOS', 'valor' => $total ?? 0],
        ['etiqueta' => 'FECHA DE EMISIÓN', 'valor' => $generado_en?->format('d/m/Y H:i') ?? 'N/D'],
    ];

    if (!empty($metadatosAdicionales)) {
        $metadatosDocumento = array_merge($metadatosDocumento, $metadatosAdicionales);
    }
@endphp
<div class="pdf-header">
    <table class="pdf-header-grid">
        <tr>
            <td>
                <img
                    class="pdf-brand-logo"
                    src="{{ public_path('Capa 1.svg') }}"
                    alt="StockMind"
                >
            </td>
            <td class="pdf-document-meta">
                <span class="pdf-document-label">INFORME DE GESTIÓN</span><br>
                Gestión y seguimiento de recursos
            </td>
        </tr>
    </table>
    <h1 class="pdf-title">{{ $titulo }}</h1>
    @isset($subtitulo)
        <div class="pdf-subtitle">{{ $subtitulo }}</div>
    @endisset
    <table class="pdf-header-meta{{ count($metadatosDocumento) > 2 ? ' pdf-header-meta--four' : '' }}">
        <tr>
            @foreach($metadatosDocumento as $metadato)
                <td>
                    <span class="pdf-meta-label">{{ $metadato['etiqueta'] }}</span>
                    <strong class="pdf-meta-value">{{ $metadato['valor'] }}</strong>
                </td>
            @endforeach
        </tr>
    </table>
</div>
