<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Laboratorio') – MiMedico</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/laboratorio.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="lab-body">

    <header class="lab-topbar">
        <div class="lab-topbar__inner">
            <a href="{{ route('laboratorio.buscar') }}" class="lab-topbar__brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
                <span>@yield('seccion_titulo', 'Laboratorio Clínico')</span>
            </a>
            <nav class="lab-topbar__actions">
                @hasSection('volver')
                    <a href="@yield('volver')" class="lab-topbar__link">
                        <i class="fas fa-arrow-left"></i><span>Regresar</span>
                    </a>
                @endif
                <span class="lab-topbar__user">
                    <i class="fas fa-flask"></i>
                    {{ session('usuario_nombre', 'Laboratorio') }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="lab-topbar__logout-form">
                    @csrf
                    <button type="submit" class="lab-topbar__link lab-topbar__link--danger">
                        <i class="fas fa-right-from-bracket"></i><span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="lab-main">
        @yield('contenido')
    </main>

    <footer class="lab-footer">
        <p>&copy; {{ date('Y') }} Mi Médico. Todos los derechos reservados.</p>
        <p>Desarrollado por Dody Ortega, Jefferson Anchundia, Erick Plaza, Kevin Soledispa</p>
    </footer>

    @stack('scripts')
</body>
</html>