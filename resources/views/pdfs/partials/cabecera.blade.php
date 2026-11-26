{{-- Cabecera común: identidad, título y datos de emisión del documento. --}}
<div class="pdf-header">
    <table class="pdf-header-grid">
        <tr>
            <td>
                <span class="pdf-brand-mark">SM</span>
                <span class="pdf-brand-name">STOCKMIND</span>
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
    <table class="pdf-header-meta">
        <tr>
            <td>
                <span class="pdf-meta-label">REGISTROS INCLUIDOS</span>
                <strong class="pdf-meta-value">{{ $total ?? 0 }}</strong>
            </td>
            <td class="text-right">
                <span class="pdf-meta-label">FECHA DE EMISIÓN</span>
                <strong class="pdf-meta-value">{{ $generado_en?->format('d/m/Y H:i') ?? 'N/D' }}</strong>
            </td>
        </tr>
    </table>
</div>
