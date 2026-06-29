@extends('layouts.auth')
@section('titulo', 'Nueva contraseña – Mi Médico')

@section('contenido')
<section class="login-section">
    <div class="login-wrapper">

        {{-- ═══ PANEL IZQUIERDO ═══ --}}
        <div class="login-branding">
            <div class="login-branding__inner">

                <div class="rec-step-badge">Paso 3 de 3</div>

                <div class="login-branding__icon" style="margin-top:12px;background:rgba(34,197,94,.2);">
                    <i class="fas fa-shield-alt" style="color:#6ee7b7;font-size:2rem;"></i>
                </div>

                <h2 class="login-branding__title">¡Último paso!</h2>
                <p class="login-branding__sub">
                    Crea una contraseña segura para proteger tu cuenta.
                </p>

                <ul class="login-branding__features" style="text-align:left;">
                    <li><i class="fas fa-check" style="color:#6ee7b7;"></i> Mínimo 8 caracteres</li>
                    <li><i class="fas fa-check" style="color:#6ee7b7;"></i> Combina letras y números</li>
                    <li><i class="fas fa-check" style="color:#6ee7b7;"></i> Agrega un símbolo para más seguridad</li>
                    <li><i class="fas fa-check" style="color:#6ee7b7;"></i> No uses tu nombre ni fecha de nacimiento</li>
                </ul>

                <div class="rec-tip" style="margin-top:24px;">
                    <i class="fas fa-lock"></i>
                    Tu contraseña se cifra antes de guardarse. Nadie más puede verla.
                </div>
            </div>
        </div>

        {{-- ═══ FORMULARIO ═══ --}}
        <div class="login-form-side">

            <div class="login-mobile-header">
                <div class="login-mobile-header__icon" style="background:#22c55e;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Mi Médico</h2>
                <p>Crea tu nueva contraseña</p>
            </div>

            <h2 class="login-form-side__title">Nueva contraseña</h2>
            <p class="login-form-side__sub">Elige una contraseña que solo tú conozcas.</p>

            @if(session('error'))
            <div class="login-alert login-alert--error">
                <div class="login-alert__icon"><i class="fas fa-times-circle"></i></div>
                <div class="login-alert__body"><p>{{ session('error') }}</p></div>
            </div>
            @endif

            <form method="POST" action="{{ route('recuperar.guardar') }}" class="login-form" id="formNueva">
                @csrf

                <div class="login-field">
                    <label><i class="fas fa-lock"></i> Nueva contraseña</label>
                    <div class="login-field__input-wrap">
                        <i class="fas fa-lock login-field__icon"></i>
                        <input type="password" name="contrasena" id="pass1"
                               placeholder="Mínimo 8 caracteres" required minlength="8" autofocus>
                        <button type="button" class="login-field__toggle" onclick="togglePass('pass1','icon1')">
                            <i class="fas fa-eye" id="icon1"></i>
                        </button>
                    </div>
                    @error('contrasena')
                        <p class="rec-field-error">{{ $message }}</p>
                    @enderror

                    {{-- Barra de fortaleza --}}
                    <div id="strengthWrap" style="display:none;margin-top:10px;">
                        <div class="strength-bar-track">
                            <div class="strength-bar-fill" id="strengthFill"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:5px;">
                            <span id="strengthLabel" style="font-size:.78rem;font-weight:600;"></span>
                            <span id="strengthHint"  style="font-size:.75rem;color:#9ca3af;"></span>
                        </div>
                    </div>
                </div>

                <div class="login-field">
                    <label><i class="fas fa-lock"></i> Confirmar contraseña</label>
                    <div class="login-field__input-wrap">
                        <i class="fas fa-lock login-field__icon"></i>
                        <input type="password" name="confirmacion" id="pass2"
                               placeholder="Repite tu nueva contraseña" required>
                        <button type="button" class="login-field__toggle" onclick="togglePass('pass2','icon2')">
                            <i class="fas fa-eye" id="icon2"></i>
                        </button>
                    </div>
                    @error('confirmacion')
                        <p class="rec-field-error">{{ $message }}</p>
                    @enderror
                    <p id="matchMsg" style="font-size:.82rem;margin-top:5px;display:none;"></p>
                </div>

                <button type="submit" class="login-btn" id="btnGuardar">
                    <i class="fas fa-check-circle"></i>
                    <span>Guardar nueva contraseña</span>
                </button>
            </form>

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
    text-align: left;
}
.rec-tip i { flex-shrink: 0; margin-top: 1px; color: #93c5fd; }
.rec-field-error { color: #dc2626; font-size: .82rem; margin-top: 4px; }

.strength-bar-track {
    height: 7px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}
.strength-bar-fill {
    height: 100%;
    width: 0;
    border-radius: 4px;
    transition: width .35s ease, background .35s ease;
}
</style>
@endpush

@push('scripts')
<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

const pass1  = document.getElementById('pass1');
const pass2  = document.getElementById('pass2');
const wrap   = document.getElementById('strengthWrap');
const fill   = document.getElementById('strengthFill');
const lbl    = document.getElementById('strengthLabel');
const hint   = document.getElementById('strengthHint');
const match  = document.getElementById('matchMsg');

const levels = [
    { max: 24,  color: '#ef4444', text: 'Muy débil',  hint: 'Agrega más caracteres' },
    { max: 49,  color: '#f97316', text: 'Débil',       hint: 'Agrega números o mayúsculas' },
    { max: 74,  color: '#eab308', text: 'Aceptable',   hint: 'Agrega un símbolo' },
    { max: 99,  color: '#22c55e', text: 'Fuerte',      hint: '¡Buena contraseña!' },
    { max: 100, color: '#16a34a', text: 'Muy fuerte',  hint: '¡Excelente contraseña!' },
];

pass1.addEventListener('input', function () {
    const v = this.value;
    wrap.style.display = v ? 'block' : 'none';

    let score = 0;
    if (v.length >= 8)              score += 25;
    if (v.length >= 12)             score += 10;
    if (/[A-Z]/.test(v))            score += 20;
    if (/[0-9]/.test(v))            score += 20;
    if (/[^A-Za-z0-9]/.test(v))    score += 25;

    score = Math.min(score, 100);
    const lvl = levels.find(l => score <= l.max);
    fill.style.width      = score + '%';
    fill.style.background = lvl.color;
    lbl.textContent       = lvl.text;
    lbl.style.color       = lvl.color;
    hint.textContent      = lvl.hint;

    checkMatch();
});

pass2.addEventListener('input', checkMatch);

function checkMatch() {
    if (!pass2.value) { match.style.display = 'none'; return; }
    match.style.display = 'block';
    const ok = pass1.value === pass2.value;
    match.innerHTML  = ok
        ? '<i class="fas fa-check-circle" style="color:#22c55e;margin-right:4px;"></i>Las contraseñas coinciden'
        : '<i class="fas fa-times-circle" style="color:#ef4444;margin-right:4px;"></i>Las contraseñas no coinciden';
    match.style.color = ok ? '#22c55e' : '#ef4444';
}

document.getElementById('formNueva').addEventListener('submit', function () {
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Guardando...</span>';
});
</script>
@endpush
