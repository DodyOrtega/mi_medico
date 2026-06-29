<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Mi Médico')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_transparente.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>
<body>

    {{-- Botón flotante volver al inicio --}}
    <a href="{{ route('inicio') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i>
        <span>Volver al inicio</span>
    </a>

    <main>
        @yield('contenido')
    </main>

    <script src="{{ asset('assets/js/public.js') }}"></script>
    @stack('scripts')
</body>
</html>
