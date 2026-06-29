<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Farmacia') – MiMedico</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/farmacia.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="farm-body">

    <header class="farm-topbar">
        <div class="farm-topbar__inner">
            <a href="{{ route('farmacia.buscar') }}" class="farm-topbar__brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
                <span>@yield('seccion_titulo', 'Farmacia')</span>
            </a>
            <nav class="farm-topbar__actions">
                @hasSection('volver')
                    <a href="@yield('volver')" class="farm-topbar__link">
                        <i class="fas fa-arrow-left"></i><span>Regresar</span>
                    </a>
                @endif
                <span class="farm-topbar__user">
                    <i class="fas fa-prescription-bottle-medical"></i>
                    {{ session('usuario_nombre', 'Farmacia') }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="farm-topbar__logout-form">
                    @csrf
                    <button type="submit" class="farm-topbar__link farm-topbar__link--danger">
                        <i class="fas fa-right-from-bracket"></i><span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="farm-main">
        @yield('contenido')
    </main>

    <footer class="farm-footer">
        <p>&copy; {{ date('Y') }} Mi Médico. Todos los derechos reservados.</p>
        <p>Desarrollado por Dody Ortega, Jefferson Anchundia, Erick Plaza, Kevin Soledispa</p>
    </footer>

    @stack('scripts')
</body>
</html>