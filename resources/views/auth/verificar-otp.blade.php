@extends('layouts.auth')
@section('titulo', 'Verificar código – Mi Médico')

@section('contenido')
<section class="login-section">
    <div class="login-wrapper">

        {{-- ═══ PANEL IZQUIERDO ═══ --}}
        <div class="login-branding">
            <div class="login-branding__inner">

                <div class="rec-step-badge">Paso 2 de 3</div>

                <div class="login-branding__icon" style="margin-top:12px;background:rgba(37,211,102,.2);">
                    <i class="fab fa-whatsapp" style="color:#25d366;font-size:2.2rem;"></i>
                </div>

                <h2 class="login-branding__title">Revisa tu WhatsApp</h2>
                <p class="login-branding__sub">
                    @if(session('tel_masked'))
                        Enviamos el código al número que termina en
                        <strong style="color:#fff;font-size:1.1rem;">{{ session('tel_masked') }}</strong>.
                    @else
                        Ingresa el código de 6 dígitos que recibiste por WhatsApp.
                    @endif
                </p>

                <div class="rec-countdown-wrap">
                    <div class="rec-countdown-ring">
                        <svg viewBox="0 0 36 36">
                            <path class="rec-ring-bg"
                                  d="M18 2.0845 a15.9155 15.9155 0 0 1 0 31.831 a15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="rec-ring-fill" id="ringFill"
                                  stroke-dasharray="100, 100"
                                  d="M18 2.0845 a15.9155 15.9155 0 0 1 0 31.831 a15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <span id="countdownText">10:00</span>
                    </div>
                    <p style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:8px;">
                        Tiempo restante para ingresar el código
                    </p>
                </div>

                <div class="rec-tip">
                    <i class="fas fa-info-circle"></i>
                    Si no recibes el mensaje, revisa que tu número esté activo en WhatsApp.
                </div>
            </div>
        </div>

        {{-- ═══ FORMULARIO ═══ --}}
        <div class="login-form-side">

            <div class="login-mobile-header">
                <div class="login-mobile-header__icon" style="background:#25d366;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h2>Mi Médico</h2>
                <p>Ingresa tu código de WhatsApp</p>
            </div>

            <h2 class="login-form-side__title">Ingresa el código</h2>
            <p class="login-form-side__sub">Escribe los 6 dígitos que recibiste.</p>

            @if(session('error'))
            <div class="login-alert login-alert--error">
                <div class="login-alert__icon"><i class="fas fa-times-circle"></i></div>
                <div class="login-alert__body"><p>{{ session('error') }}</p></div>
            </div>
            @endif

            <form method="POST" action="{{ route('recuperar.verificar.post') }}" id="formOtp" class="login-form">
                @csrf

                {{-- Input oculto que recibe el valor concatenado --}}
                <input type="hidden" name="codigo" id="codigoHidden">

                {{-- 6 casillas visuales --}}
                <div class="otp-grid">
                    @for($i = 0; $i < 6; $i++)
                    <input class="otp-box" type="text" maxlength="1"
                           inputmode="numeric" pattern="[0-9]"
                           autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                           data-index="{{ $i }}">
                    @endfor
                </div>

                @error('codigo')
                    <p class="rec-field-error" style="text-align:center;">{{ $message }}</p>
                @enderror

                <button type="submit" class="login-btn" id="btnVerificar" disabled>
                    <i class="fas fa-check-circle"></i>
                    <span>Verificar código</span>
                </button>
            </form>

            <div class="login-divider"><span>¿No recibiste el código?</span></div>

            <a href="{{ route('recuperar.form') }}" class="login-btn-register">
                <i class="fas fa-redo"></i>
                <span>Solicitar un código nuevo</span>
            </a>

        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
<style>
/* ── Step badge / tip ── */
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

/* ── Countdown ── */
.rec-countdown-wrap { text-align: center; margin-top: 24px; }
.rec-countdown-ring {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto;
}
.rec-countdown-ring svg { transform: rotate(-90deg); }
.rec-ring-bg   { fill: none; stroke: rgba(255,255,255,.2); stroke-width: 3.5; }
.rec-ring-fill { fill: none; stroke: #6ee7b7; stroke-width: 3.5;
                 stroke-linecap: round; transition: stroke-dasharray .5s linear; }
#countdownText {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem; font-weight: 700;
}

/* ── OTP grid ── */
.otp-grid {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 8px 0 4px;
}
.otp-box {
    width: 52px;
    height: 60px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: #f9fafb;
    font-size: 1.6rem;
    font-weight: 700;
    text-align: center;
    color: #1a2e44;
    transition: border-color .2s, box-shadow .2s, transform .15s;
    caret-color: transparent;
}
.otp-box:focus {
    outline: none;
    border-color: #0078d7;
    box-shadow: 0 0 0 3px rgba(0,120,215,.15);
    background: #fff;
    transform: scale(1.06);
}
.otp-box.filled {
    border-color: #0078d7;
    background: #eff6ff;
    color: #0078d7;
}
.otp-box.error {
    border-color: #ef4444;
    background: #fff5f5;
    animation: shake .4s ease;
}
@keyframes shake {
    0%,100%{ transform: translateX(0); }
    20%    { transform: translateX(-4px); }
    40%    { transform: translateX(4px); }
    60%    { transform: translateX(-4px); }
    80%    { transform: translateX(4px); }
}
@media (max-width: 420px) {
    .otp-box { width: 42px; height: 52px; font-size: 1.3rem; }
    .otp-grid { gap: 7px; }
}
</style>
@endpush

@push('scripts')
<script>
/* ── OTP boxes ── */
const boxes   = document.querySelectorAll('.otp-box');
const hidden  = document.getElementById('codigoHidden');
const btnVer  = document.getElementById('btnVerificar');
const form    = document.getElementById('formOtp');

boxes.forEach((box, i) => {
    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace') {
            box.value = '';
            box.classList.remove('filled');
            if (i > 0) boxes[i - 1].focus();
            updateHidden();
            e.preventDefault();
        }
    });

    box.addEventListener('input', e => {
        const val = e.target.value.replace(/\D/g, '').slice(-1);
        box.value = val;
        box.classList.toggle('filled', !!val);
        if (val && i < 5) boxes[i + 1].focus();
        updateHidden();
    });

    box.addEventListener('paste', e => {
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        if (!text) return;
        e.preventDefault();
        [...text].slice(0, 6).forEach((ch, j) => {
            if (boxes[j]) { boxes[j].value = ch; boxes[j].classList.add('filled'); }
        });
        const next = Math.min(text.length, 5);
        boxes[next].focus();
        updateHidden();
    });

    box.addEventListener('focus', () => box.select());
});

function updateHidden() {
    const code = [...boxes].map(b => b.value).join('');
    hidden.value = code;
    btnVer.disabled = code.length < 6;
    if (code.length === 6) {
        btnVer.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verificando...</span>';
        setTimeout(() => form.submit(), 200);
    }
}

boxes[0].focus();

/* ── Countdown 10 min ── */
const ring  = document.getElementById('ringFill');
const label = document.getElementById('countdownText');
let total   = 600;

(function tick() {
    const m = String(Math.floor(total / 60)).padStart(2, '0');
    const s = String(total % 60).padStart(2, '0');
    label.textContent = `${m}:${s}`;

    const pct = (total / 600) * 100;
    ring.setAttribute('stroke-dasharray', `${pct}, 100`);

    if (total <= 60)  ring.style.stroke = '#fbbf24';
    if (total <= 0) {
        label.textContent = '00:00';
        ring.style.stroke = '#ef4444';
        boxes.forEach(b => b.classList.add('error'));
        return;
    }
    total--;
    setTimeout(tick, 1000);
})();
</script>
@endpush
