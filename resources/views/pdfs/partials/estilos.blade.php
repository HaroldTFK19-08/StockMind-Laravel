@php
    $pdfCssPath = public_path('css/pdf.css');
@endphp
<style>
@if(is_file($pdfCssPath))
    {!! file_get_contents($pdfCssPath) !!}
@else
    /* Fallback mientras no se ejecute: npm run build:pdf-css */
    body, .pdf-body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1c261b;
        background: #ffffff;
    }
    .pdf-header { border-bottom: 2px solid #17330f; margin-bottom: 0.75rem; padding-bottom: 0.5rem; }
    .pdf-title { font-size: 1.25rem; font-weight: 700; color: #17330f; margin: 0; line-height: 1.75rem; }
    .pdf-subtitle { color: #5f6b5c; }
    .pdf-section { margin: 1rem 0 0.25rem; font-size: 13px; font-weight: 700; color: #2b8000; }
    .pdf-meta, .pdf-table { width: 100%; border-collapse: collapse; }
    .pdf-meta td, .pdf-table th, .pdf-table td { border: 1px solid #dde3d8; padding: 0.25rem; text-align: left; vertical-align: top; }
    .pdf-table th { background: #39a900; color: #ffffff; font-size: 9px; font-weight: 700; }
    .pdf-table tbody tr:nth-child(even) td { background: #f4f6f1; }
    .pdf-label { width: 20%; background: #eaf4e2; font-weight: 700; }
    .pdf-small { font-size: 8px; color: #5f6b5c; }
    .pdf-footer { margin-top: 0.75rem; border-top: 1px solid #dde3d8; padding-top: 0.5rem; font-size: 8px; color: #5f6b5c; }
    .pdf-photo { max-width: 100%; max-height: 280px; margin-top: 8px; }
    .pdf-nowrap { white-space: nowrap; }
@endif
</style>
