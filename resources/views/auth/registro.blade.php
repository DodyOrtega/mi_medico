@extends('layouts.auth')
@section('titulo', 'Crear cuenta – MiMedico')

@section('contenido')
<div class="reg-page">
    <div class="reg-card">

        {{-- ── HEADER ── --}}
        <div class="reg-header">
            <div class="reg-header__logo">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
            </div>
            <div>
                <h1 class="reg-header__title">Crear cuenta</h1>
                <p class="reg-header__sub">Completa tus datos para acceder a MiMedico. Los datos clínicos los puedes agregar luego desde tu perfil.</p>
            </div>
            <a href="{{ route('login') }}" class="reg-header__login">
                <i class="fas fa-sign-in-alt"></i> Ya tengo cuenta
            </a>
        </div>

        @if(session('error'))
            <div class="reg-alert">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('registro.post') }}" class="reg-body">
            @csrf

            {{-- ── SECCIÓN 1: Datos personales ── --}}
            <div class="reg-section">
                <div class="reg-section__head">
                    <span class="reg-section__icon reg-section__icon--blue">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <span class="reg-section__label">Datos personales</span>
                </div>

                <div class="reg-grid reg-grid--4">
                    <div class="reg-field">
                        <label class="reg-label">Cédula <span class="reg-req">*</span></label>
                        <input class="reg-input" type="text" name="cedula"
                               maxlength="10" pattern="[0-9]{10}"
                               placeholder="Ej: 0912345678"
                               value="{{ old('cedula') }}" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Tipo de documento</label>
                        <select class="reg-input" name="tipo_cedula">
                            <option value="cedula"    {{ old('tipo_cedula','cedula') == 'cedula'    ? 'selected':'' }}>Cédula</option>
                            <option value="pasaporte" {{ old('tipo_cedula') == 'pasaporte' ? 'selected':'' }}>Pasaporte</option>
                        </select>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Nombres <span class="reg-req">*</span></label>
                        <input class="reg-input" type="text" name="nombres"
                               maxlength="40" placeholder="Tus nombres"
                               value="{{ old('nombres') }}" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Primer apellido <span class="reg-req">*</span></label>
                        <input class="reg-input" type="text" name="apellido1"
                               maxlength="40" placeholder="Primer apellido"
                               value="{{ old('apellido1') }}" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Segundo apellido</label>
                        <input class="reg-input" type="text" name="apellido2"
                               maxlength="40" placeholder="Opcional"
                               value="{{ old('apellido2') }}">
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Género</label>
                        <select class="reg-input" name="genero">
                            <option value="M"    {{ old('genero','M') == 'M'    ? 'selected':'' }}>Masculino</option>
                            <option value="F"    {{ old('genero')     == 'F'    ? 'selected':'' }}>Femenino</option>
                            <option value="Otro" {{ old('genero')     == 'Otro' ? 'selected':'' }}>Otro</option>
                        </select>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Fecha de nacimiento <span class="reg-req">*</span></label>
                        <input class="reg-input" type="date" name="fecha_nac"
                               value="{{ old('fecha_nac') }}" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Teléfono</label>
                        <input class="reg-input" type="tel" name="telefono"
                               maxlength="10" placeholder="0999999999"
                               value="{{ old('telefono') }}">
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN 2: Ubicación ── --}}
            <div class="reg-section">
                <div class="reg-section__head">
                    <span class="reg-section__icon reg-section__icon--green">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <span class="reg-section__label">Ubicación</span>
                </div>

                <div class="reg-field">
                    <label class="reg-label">Parroquia <span class="reg-req">*</span></label>
                    <select class="reg-input" name="codigo_parroquia" required>
                        <option value="">Selecciona provincia › cantón › parroquia...</option>
                        @foreach($parroquias as $par)
                            <option value="{{ $par->codigo_parroquia }}"
                                {{ old('codigo_parroquia') == $par->codigo_parroquia ? 'selected':'' }}>
                                {{ $par->nombre_provincia }} › {{ $par->nombre_canton }} › {{ $par->nombre_parroquia }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ── SECCIÓN 3: Acceso ── --}}
            <div class="reg-section">
                <div class="reg-section__head">
                    <span class="reg-section__icon reg-section__icon--purple">
                        <i class="fas fa-lock"></i>
                    </span>
                    <span class="reg-section__label">Datos de acceso</span>
                </div>

                <div class="reg-grid reg-grid--3">
                    <div class="reg-field reg-field--full">
                        <label class="reg-label">Correo electrónico <span class="reg-req">*</span></label>
                        <input class="reg-input" type="email" name="correo"
                               maxlength="100" placeholder="tucorreo@gmail.com"
                               value="{{ old('correo') }}" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Contraseña <span class="reg-req">*</span> <span class="reg-hint">mín. 8 caracteres</span></label>
                        <input class="reg-input" type="password" name="contrasena"
                               placeholder="••••••••" required>
                    </div>
                    <div class="reg-field">
                        <label class="reg-label">Confirmar contraseña <span class="reg-req">*</span></label>
                        <input class="reg-input" type="password" name="contrasena_confirmation"
                               placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            {{-- ── ACCIONES ── --}}
            <div class="reg-footer">
                <a href="{{ route('login') }}" class="reg-btn reg-btn--cancel">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="reg-btn reg-btn--submit">
                    <i class="fas fa-user-plus"></i> Crear cuenta
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
<style>
/* ════ PÁGINA REGISTRO ════════════════════════════════════════════ */
.reg-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #e8f4fb 0%, #f0f4f8 100%);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 48px 20px 64px;
}

.reg-card {
    width: 100%;
    max-width: 860px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.11);
    overflow: hidden;
}

/* ── Header ── */
.reg-header {
    background: linear-gradient(135deg, #0078d7 0%, #004f9a 100%);
    padding: 28px 36px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.reg-header__logo img {
    height: 60px;
    filter: brightness(0) invert(1);
}

.reg-header__title {
    color: #fff;
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0 0 4px;
}

.reg-header__sub {
    color: rgba(255,255,255,.8);
    font-size: .82rem;
    margin: 0;
    line-height: 1.5;
}

.reg-header__login {
    margin-left: auto;
    background: rgba(255,255,255,.15);
    color: #fff;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 8px;
    padding: 9px 18px;
    font-size: .85rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: background .2s;
}
.reg-header__login:hover { background: rgba(255,255,255,.25); }

/* ── Alerta error ── */
.reg-alert {
    margin: 20px 36px 0;
    background: #fff5f5;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 12px 16px;
    color: #991b1b;
    font-size: .88rem;
    display: flex;
    align-items: center;
    gap: 10px;
}
.reg-alert i { color: #ef4444; flex-shrink: 0; }

/* ── Body ── */
.reg-body { padding: 8px 36px 0; }

/* ── Sección ── */
.reg-section { margin: 28px 0; }

.reg-section__head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.reg-section__icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
}
.reg-section__icon--blue   { background: #eff6ff; color: #0078d7; }
.reg-section__icon--green  { background: #f0fdf4; color: #059669; }
.reg-section__icon--purple { background: #faf5ff; color: #7c3aed; }

.reg-section__label {
    font-size: .82rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .06em;
}

/* ── Grid ── */
.reg-grid {
    display: grid;
    gap: 16px;
}
.reg-grid--4 { grid-template-columns: repeat(4, 1fr); }
.reg-grid--3 { grid-template-columns: repeat(2, 1fr); }

.reg-field--full { grid-column: 1 / -1; }

/* ── Campo ── */
.reg-label {
    display: block;
    font-size: .8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.reg-req  { color: #ef4444; }
.reg-hint { font-weight: 400; color: #9ca3af; font-size: .75rem; margin-left: 4px; }

.reg-input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: .9rem;
    color: #1a2e44;
    background: #f9fafb;
    font-family: inherit;
    appearance: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
    box-sizing: border-box;
}
.reg-input:focus {
    outline: none;
    border-color: #0078d7;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,120,215,.1);
}
.reg-input::placeholder { color: #b0b8c4; }

/* ── Footer ── */
.reg-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 20px 0 36px;
    border-top: 1px solid #f0f0f0;
    margin-top: 8px;
}

.reg-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 26px;
    border-radius: 10px;
    font-size: .93rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all .2s;
}
.reg-btn--cancel {
    background: #f3f4f6;
    color: #374151;
}
.reg-btn--cancel:hover { background: #e5e7eb; }

.reg-btn--submit {
    background: linear-gradient(135deg, #0078d7, #005fa3);
    color: #fff;
}
.reg-btn--submit:hover {
    background: linear-gradient(135deg, #005fa3, #004d87);
    box-shadow: 0 4px 16px rgba(0,120,215,.35);
    transform: translateY(-1px);
}

/* ── Responsive ── */
@media (max-width: 700px) {
    .reg-header  { flex-wrap: wrap; padding: 20px 20px; }
    .reg-header__login { margin-left: 0; width: 100%; justify-content: center; }
    .reg-body    { padding: 8px 20px 0; }
    .reg-footer  { padding: 20px 0 28px; }
    .reg-grid--4 { grid-template-columns: 1fr 1fr; }
    .reg-grid--3 { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
    .reg-grid--4 { grid-template-columns: 1fr; }
}
</style>
@endpush
