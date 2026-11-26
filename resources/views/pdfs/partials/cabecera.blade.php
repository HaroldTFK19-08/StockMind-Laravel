{{-- Cabecera común: evita repetir título, subtítulo y fecha en cada PDF. --}}
<div class="pdf-header">
    <h1 class="pdf-title">{{ $titulo }}</h1>
    <div class="pdf-subtitle">{{ $subtitulo }}</div>
    <div class="pdf-small">
        @isset($total) Total: {{ $total }} · @endisset
        Generado: {{ $generado_en?->format('d/m/Y H:i') }}
    </div>
</div>
