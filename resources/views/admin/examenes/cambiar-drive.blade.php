@extends('layouts.admin')
@section('titulo', 'Cambiar cuenta Google Drive')
@section('volver', route('admin.examenes.index'))

@section('contenido')

<div class="admin-welcome">
    <div>
        <h2 style="font-size:1.3rem;font-weight:700;color:#1a2e44;margin:0 0 4px;">
            <i class="fab fa-google-drive" style="color:#4285f4;margin-right:8px;"></i>
            Cambiar cuenta de Google Drive
        </h2>
        <p style="font-size:.88rem;color:#6b7280;margin:0;">
            Serás redirigido automáticamente a Google para iniciar sesión.
        </p>
    </div>
</div>

{{-- Estado del proceso --}}
<div class="cd-card" id="estadoInicio">
    <div class="cd-spinner">
        <div class="cd-spinner__ring"></div>
        <i class="fab fa-google-drive cd-spinner__icon"></i>
    </div>
    <h3 class="cd-title">Iniciando autorización con Google Drive…</h3>
    <p class="cd-desc">En unos segundos se abrirá la página de Google para que inicies sesión con la nueva cuenta.</p>
    <p class="cd-desc" style="color:#9ca3af;font-size:.8rem;">Si no se abre automáticamente, haz clic en el botón de abajo.</p>

    <button onclick="abrirOAuth()" class="cd-btn-google" id="btnAbrir" style="opacity:0;pointer-events:none;">
        <i class="fab fa-google"></i> Abrir página de autorización
    </button>
</div>

{{-- Estado después de autorizar --}}
<div class="cd-card cd-card--success" id="estadoListo" style="display:none;">
    <i class="fas fa-circle-check cd-success-icon"></i>
    <h3 class="cd-title" style="color:#15803d;">¿Ya autorizaste con Google?</h3>
    <p class="cd-desc">Si completaste el inicio de sesión en Google y la página dijo que fue exitoso, haz clic en continuar.</p>

    <a href="{{ route('admin.examenes.guardar-drive') }}" class="cd-btn-ok">
        <i class="fas fa-check"></i> Sí, ya autoricé — Continuar
    </a>
    <a href="#" onclick="reintentar()" class="cd-btn-retry">
        <i class="fas fa-rotate-left"></i> Reintentar
    </a>
</div>

@endsection

@push('styles')
<style>
.cd-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:40px 32px;
           text-align:center; max-width:480px; margin:20px auto; box-shadow:0 2px 12px rgba(0,0,0,.06); }
.cd-card--success { border-color:#bbf7d0; background:#f0fdf4; }

/* Spinner */
.cd-spinner { position:relative; width:72px; height:72px; margin:0 auto 20px; }
.cd-spinner__ring { position:absolute; inset:0; border-radius:50%;
                    border:3px solid #e5e7eb; border-top-color:#4285f4;
                    animation:spin 1s linear infinite; }
.cd-spinner__icon { position:absolute; inset:0; display:flex; align-items:center;
                    justify-content:center; font-size:1.8rem; color:#4285f4; }
@keyframes spin { to { transform:rotate(360deg); } }

.cd-success-icon { font-size:3rem; color:#22c55e; display:block; margin-bottom:16px; }

.cd-title { font-size:1.05rem; font-weight:700; color:#1a2e44; margin:0 0 10px; }
.cd-desc  { font-size:.87rem; color:#6b7280; margin:0 0 8px; line-height:1.5; }

.cd-btn-google { display:inline-flex; align-items:center; gap:10px; padding:12px 28px;
                 background:linear-gradient(135deg,#4285f4,#1a73e8); color:#fff;
                 border:none; border-radius:8px; font-weight:600; cursor:pointer;
                 font-size:.9rem; margin-top:20px; transition:all .2s; text-decoration:none; }
.cd-btn-google:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(66,133,244,.4); }

.cd-btn-ok { display:inline-flex; align-items:center; gap:8px; padding:12px 28px;
             background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff;
             border-radius:8px; font-weight:600; font-size:.9rem; margin-top:20px;
             text-decoration:none; transition:all .2s; }
.cd-btn-ok:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(34,197,94,.4); }

.cd-btn-retry { display:block; color:#6b7280; font-size:.82rem; margin-top:12px;
                text-decoration:none; }
.cd-btn-retry:hover { color:#374151; }
</style>
@endpush

@push('scripts')
<script>
const OAUTH_URL = 'http://localhost:53682/';

function abrirOAuth() {
    window.open(OAUTH_URL, '_blank');
    mostrarListo();
}

function mostrarListo() {
    document.getElementById('estadoInicio').style.display = 'none';
    document.getElementById('estadoListo').style.display  = 'block';
}

function reintentar() {
    document.getElementById('estadoInicio').style.display = 'block';
    document.getElementById('estadoListo').style.display  = 'none';
    iniciarContador();
}

function iniciarContador() {
    const btn = document.getElementById('btnAbrir');
    let seg = 2;

    const t = setInterval(() => {
        seg--;
        if (seg <= 0) {
            clearInterval(t);
            btn.style.opacity       = '1';
            btn.style.pointerEvents = 'auto';
            abrirOAuth();
        }
    }, 1000);
}

// Arrancar al cargar la página
iniciarContador();
</script>
@endpush
