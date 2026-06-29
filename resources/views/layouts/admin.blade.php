<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel de Administración') – MiMedico</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_transparente_casa.png') }}">

    {{-- Vite: Tailwind + CSS propio (mismo pipeline que el sitio público) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    {{-- SweetAlert2 — solo para confirmaciones/alertas, no aporta líneas de código propio --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')
</head>
<body class="admin-body">

    {{-- ═══ TOPBAR ═══ --}}
    <header class="admin-topbar">
        <div class="admin-topbar__inner">
            <a href="{{ route('admin.dashboard') }}" class="admin-topbar__brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
                <span>@yield('seccion_titulo', 'Panel de Administración')</span>
            </a>

            <nav class="admin-topbar__actions">
                @hasSection('volver')
                    <a href="@yield('volver')" class="admin-topbar__link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Regresar</span>
                    </a>
                @endif

                <span class="admin-topbar__user">
                    <i class="fas fa-user-shield"></i>
                    {{ session('usuario_nombre', 'Administrador') }}
                </span>

                <a href="{{ route('admin.credenciales.index') }}" class="admin-topbar__link">
                    <i class="fas fa-key"></i>
                    <span>Credenciales</span>
                </a>

                <a href="{{ route('admin.auditoria.index') }}" class="admin-topbar__link">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Auditoría</span>
                </a>

                <a href="{{ route('admin.perfil.index') }}" class="admin-topbar__link">
                    <i class="fas fa-user-edit"></i>
                    <span>Mi Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="admin-topbar__logout-form">
                    @csrf
                    <button type="submit" class="admin-topbar__link admin-topbar__link--danger">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    {{-- ═══ CONTENIDO ═══ --}}
    <main class="admin-main">
        @yield('contenido')
    </main>

    {{-- ═══ FOOTER ═══ --}}
    <footer class="admin-footer">
        <p>&copy; {{ date('Y') }} Mi Médico. Todos los derechos reservados.</p>
        <p>Desarrollado por Dody Ortega, Jefferson Anchundia, Erick Plaza, Kevin Soledispa</p>
    </footer>

    <script src="{{ asset('assets/js/public.js') }}"></script>
    @stack('scripts')
</body>
</html>