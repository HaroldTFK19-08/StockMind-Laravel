<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo')</title>
    @include('pdfs.partials.estilos')
    <style>
        @page {
            size: a4 landscape;
            margin: 16mm;
        }
    </style>
</head>
<body class="pdf-body">
    @yield('cabecera')
    @yield('contenido')
    @yield('pie')
</body>
</html>
