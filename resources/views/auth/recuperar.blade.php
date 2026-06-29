@extends('layouts.auth')
@section('titulo', 'Recuperar contraseña – Mi Médico')

@section('contenido')
<section class="login-section">
    <div class="login-wrapper">

        {{-- ═══ PANEL IZQUIERDO ═══ --}}
        <div class="login-branding">
            <div class="login-branding__inner">

                <div class="rec-step-badge">Paso 1 de 3</div>

                <div class="login-branding__icon" style="margin-top:12px;">
                    <i class="fas fa-key"></i>
                </div>

                <h2 class="login-branding__title">Recupera tu acceso</h2>
                <p class="login-branding__sub">
                    Te enviaremos un código de verificación a tu número de WhatsApp registrado.
                </p>

                <ul class="login-branding__features">
                    <li><i class="fab fa-whatsapp" style="color:#6ee7b7;"></i> El código llega por WhatsApp</li>
                    <li><i class="fas fa-clock"    style="color:#6ee7b7;"></i> Válido durante 10 minutos</li>
                    <li><i class="fas fa-shield-alt" style="color:#6ee7b7;"></i> Máximo 3 intentos por código</li>
                </ul>

                <div class="rec-tip">
                    <i class="fas fa-info-circle"></i>
                    Usa tu cédula o correo con los que te registraste.
                </div>
            </div>
        </div>

        {{-- ═══ FORMULARIO ═══ --}}
        <div class="login-form-side">

            <div class="login-mobile-header">
                <div class="login-mobile-header__icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Mi Médico</h2>
                <p>Recupera tu contraseña</p>
            </div>

            <h2 class="login-form-side__title">¿Olvidaste tu contraseña?</h2>
            <p class="login-form-side__sub">Ingresa tu cédula o correo y te enviamos un código.</p>

            @if(session('error'))
            <div class="login-alert login-alert--error">
                <div class="login-alert__icon"><i class="fas fa-times-circle"></i></div>
                <div class="login-alert__body"><p>{{ session('error') }}</p></div>
            </div>
            @endif

            <form method="POST" action="{{ route('recuperar.enviar') }}" class="login-form">
                @csrf

                <div class="login-field">
                    <label><i class="fas fa-id-card"></i> Cédula o correo electrónico</label>
                    <div class="login-field__input-wrap">
                        <i class="fas fa-id-card login-field__icon"></i>
                        <input type="text"
                               name="identificador"
                               placeholder="Ej: 1700000001 o usuario@correo.com"
                               value="{{ old('identificador') }}"
                               required autofocus>
                    </div>
                    @error('identificador')
                        <p class="rec-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="login-btn" id="btnEnviar">
                    <i class="fab fa-whatsapp"></i>
                    <span>Enviar código por WhatsApp</span>
                </button>
            </form>

            <div class="login-divider"><span>¿Recordaste tu contraseña?</span></div>

            <a href="{{ route('login') }}" class="login-btn-register">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al inicio de sesión</span>
            </a>

        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
<style>
.rec-step-badge {
    display: inline-block;
    background: rgba(255,255,255,.2);
    color: #fff;
    font-size: .78rem;
    font-weight: 600;
    padding: 4px 14px;
    border-radius: 20px;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.rec-tip {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 10px;
    padding: 12px 16px;
    color: rgba(255,255,255,.9);
    font-size: .83rem;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-top: 24px;
    text-align: left;
}
.rec-tip i { flex-shrink: 0; margin-top: 1px; color: #93c5fd; }
.rec-field-error { color: #dc2626; font-size: .82rem; margin-top: 4px; }
</style>
@endpush

@push('scripts')
<script>
document.querySelector('#btnEnviar').closest('form').addEventListener('submit', function () {
    const btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Enviando código...</span>';
});
</script>
@endpush
