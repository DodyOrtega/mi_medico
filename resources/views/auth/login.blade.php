@extends('layouts.auth')

@section('titulo', 'Iniciar Sesión – MiMedico')

@section('contenido')

<section class="login-section">
    <div class="login-wrapper">

        {{-- ═══ LADO IZQUIERDO — Branding ═══ --}}
        <div class="login-branding">
            <div class="login-branding__inner">
                <div class="login-branding__icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <img src="{{ asset('assets/images/logo-normal.png') }}"
                     alt="MiMedico" class="login-branding__logo">
                <h2 class="login-branding__title">Bienvenido de nuevo</h2>
                <p class="login-branding__sub">
                    Accede a tu cuenta para gestionar tus consultas médicas y citas en línea
                </p>
                <ul class="login-branding__features">
                    <li><i class="fas fa-check-circle"></i> Consultas médicas online</li>
                    <li><i class="fas fa-check-circle"></i> Historial clínico digital</li>
                    <li><i class="fas fa-check-circle"></i> Recetas electrónicas</li>
                </ul>
            </div>
        </div>

        {{-- ═══ LADO DERECHO — Formulario ═══ --}}
        <div class="login-form-side">

            {{-- Header móvil --}}
            <div class="login-mobile-header">
                <div class="login-mobile-header__icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h2>MiMedico</h2>
                <p>Tu salud en buenas manos</p>
            </div>

            <h2 class="login-form-side__title">Iniciar sesión</h2>
            <p class="login-form-side__sub">Ingresa tus credenciales para acceder</p>

            {{-- ═══ MENSAJES ═══ --}}
            @if(session('mensaje'))
            <div class="login-alert" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;
                        display:flex;gap:12px;padding:14px 16px;margin-bottom:18px;">
                <div style="color:#16a34a;font-size:1.1rem;padding-top:1px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div><p style="color:#15803d;font-size:.9rem;">{{ session('mensaje') }}</p></div>
            </div>
            @endif

            @if(session('error'))
                @if(session('error_tipo') === 'inactivo')
                <div class="login-alert login-alert--warning">
                    <div class="login-alert__icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="login-alert__body">
                        <strong>Cuenta inactiva</strong>
                        <p>{{ session('error') }}</p>
                        <div class="login-alert__contacto">
                            <p>Contacta al administrador:</p>
                            <a href="mailto:globalhealthsas@gmail.com">
                                <i class="fas fa-envelope"></i> globalhealthsas@gmail.com
                            </a>
                            <a href="https://wa.me/+593979328112" target="_blank">
                                <i class="fab fa-whatsapp"></i> +593 97 932 8112
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="login-alert login-alert--error">
                    <div class="login-alert__icon"><i class="fas fa-times-circle"></i></div>
                    <div class="login-alert__body">
                        <strong>Error de autenticación</strong>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
                @endif
            @endif

            @if(!session('error'))
            <div class="login-tip">
                <i class="fas fa-lightbulb"></i>
                <p><strong>Consejo:</strong> Usa tu email o número de cédula para iniciar sesión</p>
            </div>
            @endif

            {{-- ═══ FORMULARIO ═══ --}}
            <form method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf

                <div class="login-field">
                    <label><i class="fas fa-id-card"></i> Usuario</label>
                    <div class="login-field__input-wrap">
                        <i class="fas fa-id-card login-field__icon"></i>
                        <input type="text"
                               name="nombre_usuario"
                               placeholder="Email o cédula"
                               value="{{ old('nombre_usuario') }}"
                               required>
                    </div>
                </div>

                <div class="login-field">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="login-field__input-wrap">
                        <i class="fas fa-key login-field__icon"></i>
                        <input type="password"
                               name="contrasena"
                               id="password"
                               placeholder="••••••••"
                               required>
                        <button type="button" class="login-field__toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="login-form__extra">
                    <label class="login-check">
                        <input type="checkbox" name="recordar">
                        <span>Recordarme</span>
                    </label>
                    <a href="{{ route('recuperar.form') }}"
                       style="font-size:.85rem;color:#0078d7;text-decoration:none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="login-btn">
                    <span>Acceder</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="login-divider"><span>¿No tienes cuenta?</span></div>

            <a href="{{ route('registro') }}" class="login-btn-register">
                <i class="fas fa-user-plus"></i> Crear nueva cuenta
            </a>

        </div>{{-- /login-form-side --}}
    </div>{{-- /login-wrapper --}}
</section>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush