<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Mi Médico') — Telemedicina Ecuador</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_transparente.png') }}">

    {{-- Vite compila Tailwind + CSS propio --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome 6 Free (CDN) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
    <style>.nav-container { height: 86px !important; }</style>
</head>
<body class="{{ request()->routeIs('inicio') ? 'page-inicio' : '' }}">

    {{-- ═══ NAVBAR ═══ --}}
    <nav class="main-nav" id="navbar">
        <div class="nav-container">

            {{-- Hamburguesa (solo mobile, extremo izquierdo) --}}
            <button class="hamburger" id="hamburger" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>

            {{-- Logo --}}
            <a href="{{ route('inicio') }}" class="logo">
                <img src="{{ asset('assets/images/logo_transparente.png') }}" alt="Mi Médico" style="height:80px;width:auto;">
            </a>

            {{-- Menú desktop --}}
            <ul class="nav-links">
                <li><a href="{{ route('inicio') }}"       class="{{ request()->routeIs('inicio')       ? 'active' : '' }}">Inicio</a></li>
                <li><a href="{{ route('nosotros') }}"     class="{{ request()->routeIs('nosotros')     ? 'active' : '' }}">Nosotros</a></li>
                <li><a href="{{ route('telemedicina') }}" class="{{ request()->routeIs('telemedicina') ? 'active' : '' }}">Telemedicina</a></li>
                <li><a href="{{ route('contacto') }}"     class="{{ request()->routeIs('contacto')     ? 'active' : '' }}">Contacto</a></li>
                <li><a href="{{ route('terminos') }}"     class="{{ request()->routeIs('terminos')     ? 'active' : '' }}">Términos</a></li>
            </ul>

            {{-- Botón login --}}
            <div class="nav-actions">
                <a href="/login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar sesión</span>
                </a>
            </div>
        </div>

        {{-- Menú móvil --}}
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="{{ route('inicio') }}">Inicio</a></li>
                <li><a href="{{ route('nosotros') }}">Nosotros</a></li>
                <li><a href="{{ route('telemedicina') }}">Telemedicina</a></li>
                <li><a href="{{ route('contacto') }}">Contacto</a></li>
                <li><a href="{{ route('terminos') }}">Términos</a></li>
                <li><a href="/login" class="btn-login-mobile">Iniciar sesión</a></li>
            </ul>
        </div>
    </nav>

    {{-- ═══ CONTENIDO DE CADA PÁGINA ═══ --}}
    <main>
        @yield('contenido')
    </main>

    {{-- ═══ FOOTER ═══ --}}
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="Mi Médico">
                <p>Tu salud, más cerca de ti.</p>
            </div>
            <div class="footer-links">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="{{ route('inicio') }}">Inicio</a></li>
                    <li><a href="{{ route('nosotros') }}">Nosotros</a></li>
                    <li><a href="{{ route('telemedicina') }}">Telemedicina</a></li>
                    <li><a href="{{ route('contacto') }}">Contacto</a></li>
                </ul>
            </div>
            @php $cfg = \App\Http\Controllers\Admin\ConfiguracionController::leerConfig()['general'] ?? []; @endphp
            <div class="footer-contact">
                <h4>Contacto</h4>
                <p><i class="fas fa-envelope"></i> {{ $cfg['email_contacto']    ?? 'globalhealthsas@gmail.com' }}</p>
                <p><i class="fas fa-phone"></i> {{ $cfg['telefono_contacto'] ?? '+593 97 932 8112' }}</p>
                <p><i class="fas fa-map-marker-alt"></i> {{ $cfg['direccion']         ?? 'Calle César Chávez y 5ta transversal' }}</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Mi Médico — Fundación Global Health. Todos los derechos reservados.</p>
        </div>
    </footer>

    {{-- JS global --}}
    <script src="{{ asset('assets/js/public.js') }}"></script>
    @stack('scripts')
</body>
</html>