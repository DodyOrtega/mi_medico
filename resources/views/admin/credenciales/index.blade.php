@extends('layouts.admin')
@section('titulo', 'Credenciales — Admin')
@section('seccion_titulo', 'Gestión de Credenciales')
@section('volver', route('admin.dashboard'))

@section('contenido')

<style>
.cred-page { max-width: 900px; margin: 0 auto; padding: 24px 16px 60px; }
.cred-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.cred-header h1 { font-size:1.5rem; font-weight:800; color:#1a2e44; margin:0; display:flex; align-items:center; gap:10px; }
.cred-header h1 i { color:#2563eb; }

/* Formulario generar */
.cred-gen { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:24px; margin-bottom:28px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.cred-gen__title { font-size:1rem; font-weight:700; color:#1a2e44; margin-bottom:18px; display:flex; align-items:center; gap:8px; }
.cred-gen__title i { color:#2563eb; }
.cred-form { display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; }
.cred-field { display:flex; flex-direction:column; gap:5px; flex:1; min-width:160px; }
.cred-field label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
.cred-field input, .cred-field select {
    padding:10px 14px; border:1.5px solid #d1d5db; border-radius:10px;
    font-size:.88rem; color:#1a2e44; background:#fff; outline:none;
    transition: border-color .2s;
}
.cred-field input:focus, .cred-field select:focus { border-color:#2563eb; }
.btn-gen { padding:10px 22px; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:.88rem; cursor:pointer; display:flex; align-items:center; gap:8px; white-space:nowrap; transition:all .2s; }
.btn-gen:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(37,99,235,.35); }
.btn-gen:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* Rol selector con colores */
.rol-selector { display:flex; gap:10px; }
.rol-opt { flex:1; }
.rol-opt input[type=radio] { display:none; }
.rol-opt label {
    display:flex; align-items:center; justify-content:center; gap:7px;
    padding:9px 14px; border:2px solid #e5e7eb; border-radius:10px;
    font-size:.85rem; font-weight:600; color:#6b7280; cursor:pointer; transition:all .2s;
    white-space:nowrap;
}
.rol-opt input[type=radio]:checked + label.lab { border-color:#0e7490; background:#f0fdfa; color:#0e7490; }
.rol-opt input[type=radio]:checked + label.farm { border-color:#d97706; background:#fffbeb; color:#b45309; }

/* Tabla usuarios */
.cred-table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.cred-table-head { padding:18px 20px; background:#f9fafb; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.cred-table-head span { font-size:.95rem; font-weight:700; color:#1a2e44; }
.cred-badge-count { background:#2563eb; color:#fff; border-radius:20px; padding:2px 10px; font-size:.75rem; font-weight:700; margin-left:8px; }
table.cred-table { width:100%; border-collapse:collapse; }
table.cred-table th { padding:12px 16px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#9ca3af; text-align:left; background:#f9fafb; border-bottom:1px solid #f3f4f6; }
table.cred-table td { padding:13px 16px; font-size:.85rem; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
table.cred-table tr:last-child td { border-bottom:none; }
table.cred-table tbody tr:hover { background:#f9fafb; }

/* Badges rol */
.rbadge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
.rbadge--lab { background:#f0fdfa; color:#0e7490; border:1px solid #99f6e4; }
.rbadge--farm { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }

/* Estado */
.estado-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
.estado-badge--activo { background:#dcfce7; color:#166534; }
.estado-badge--inactivo { background:#fee2e2; color:#991b1b; }

/* Acciones */
.btn-accion { padding:6px 12px; border:none; border-radius:8px; font-size:.75rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all .15s; }
.btn-accion--toggle-on { background:#fee2e2; color:#991b1b; }
.btn-accion--toggle-on:hover { background:#fca5a5; }
.btn-accion--toggle-off { background:#dcfce7; color:#166534; }
.btn-accion--toggle-off:hover { background:#86efac; }
.btn-accion--reset { background:#ede9fe; color:#6d28d9; }
.btn-accion--reset:hover { background:#c4b5fd; }

/* Modal credenciales */
.cred-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:1000; align-items:center; justify-content:center; padding:20px;
}
.cred-modal-overlay.visible { display:flex; }
.cred-modal {
    background:#fff; border-radius:20px; padding:32px; max-width:460px; width:100%;
    box-shadow:0 20px 60px rgba(0,0,0,.15); position:relative; animation:slideUp .25s ease;
}
@keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.cred-modal__close { position:absolute; top:14px; right:16px; background:none; border:none; font-size:1.2rem; color:#9ca3af; cursor:pointer; }
.cred-modal__close:hover { color:#1a2e44; }
.cred-modal__icon { text-align:center; margin-bottom:16px; }
.cred-modal__icon i { font-size:2.5rem; color:#2563eb; }
.cred-modal h2 { text-align:center; font-size:1.1rem; font-weight:800; color:#1a2e44; margin:0 0 20px; }
.cred-dato { margin-bottom:14px; }
.cred-dato label { display:block; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#9ca3af; margin-bottom:4px; }
.cred-dato__val {
    display:flex; align-items:center; justify-content:space-between; gap:8px;
    background:#f8fafc; border:1.5px solid #e5e7eb; border-radius:10px;
    padding:10px 14px; font-size:.95rem; font-weight:700; color:#1a2e44; font-family:monospace;
}
.btn-copy { background:none; border:none; cursor:pointer; color:#2563eb; font-size:.88rem; padding:0; flex-shrink:0; }
.btn-copy:hover { color:#1d4ed8; }
.cred-aviso { background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 14px; font-size:.8rem; color:#b45309; margin-top:20px; display:flex; gap:8px; }
.btn-cerrar-modal { width:100%; margin-top:16px; padding:12px; background:#1a2e44; color:#fff; border:none; border-radius:10px; font-weight:700; font-size:.9rem; cursor:pointer; }
.btn-cerrar-modal:hover { background:#0f1e30; }

.empty-state { padding:60px 20px; text-align:center; color:#9ca3af; }
.empty-state i { font-size:3rem; display:block; margin-bottom:12px; }
</style>

<div class="cred-page">

    <div class="cred-header">
        <h1><i class="fas fa-key"></i> Credenciales de Acceso</h1>
    </div>

    {{-- GENERAR --}}
    <div class="cred-gen">
        <div class="cred-gen__title"><i class="fas fa-plus-circle"></i> Generar nuevas credenciales</div>
        <div class="cred-form">

            <div class="cred-field" style="min-width:200px;">
                <label>Rol del usuario</label>
                <div class="rol-selector">
                    <div class="rol-opt">
                        <input type="radio" name="rol" id="rol-lab" value="laboratorio" checked>
                        <label for="rol-lab" class="lab"><i class="fas fa-flask"></i> Laboratorio</label>
                    </div>
                    <div class="rol-opt">
                        <input type="radio" name="rol" id="rol-farm" value="farmacia">
                        <label for="rol-farm" class="farm"><i class="fas fa-pills"></i> Farmacia</label>
                    </div>
                </div>
            </div>

            <div class="cred-field">
                <label>Nombre del responsable <span style="color:#9ca3af;">(opcional)</span></label>
                <input type="text" id="inp-nombre" placeholder="Ej: Juan Pérez" maxlength="40">
            </div>

            <button class="btn-gen" id="btn-generar" onclick="generarCredenciales()">
                <i class="fas fa-magic"></i> Generar
            </button>
        </div>
    </div>

    {{-- LISTA --}}
    <div class="cred-table-wrap">
        <div class="cred-table-head">
            <span>
                Usuarios del sistema
                <span class="cred-badge-count">{{ $usuarios->count() }}</span>
            </span>
        </div>

        @if($usuarios->isEmpty())
        <div class="empty-state">
            <i class="fas fa-users-slash"></i>
            <p>Aún no hay usuarios de laboratorio o farmacia creados.</p>
        </div>
        @else
        <table class="cred-table">
            <thead>
                <tr>
                    <th>Usuario (login)</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                @foreach($usuarios as $u)
                <tr id="fila-{{ $u->cedula }}">
                    <td style="font-family:monospace;font-weight:700;font-size:.88rem;">{{ $u->cedula }}</td>
                    <td>{{ trim($u->nombres . ' ' . $u->apellido1) }}</td>
                    <td style="font-size:.8rem;color:#6b7280;">{{ $u->correo }}</td>
                    <td>
                        @if($u->rol === 'laboratorio')
                        <span class="rbadge rbadge--lab"><i class="fas fa-flask"></i> Laboratorio</span>
                        @else
                        <span class="rbadge rbadge--farm"><i class="fas fa-pills"></i> Farmacia</span>
                        @endif
                    </td>
                    <td>
                        <span class="estado-badge estado-badge--{{ $u->estado }}" id="estado-{{ $u->cedula }}">
                            {{ $u->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <button class="btn-accion {{ $u->estado === 'activo' ? 'btn-accion--toggle-on' : 'btn-accion--toggle-off' }}"
                                id="btn-toggle-{{ $u->cedula }}"
                                onclick="toggleEstado('{{ $u->cedula }}', this)">
                            <i class="fas {{ $u->estado === 'activo' ? 'fa-ban' : 'fa-check' }}"></i>
                            {{ $u->estado === 'activo' ? 'Deshabilitar' : 'Habilitar' }}
                        </button>
                        <button class="btn-accion btn-accion--reset"
                                onclick="resetPassword('{{ $u->cedula }}', '{{ addslashes(trim($u->nombres . ' ' . $u->apellido1)) }}')">
                            <i class="fas fa-sync-alt"></i> Reset pass
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- MODAL CREDENCIALES --}}
<div class="cred-modal-overlay" id="modalOverlay" onclick="if(event.target===this) cerrarModal()">
    <div class="cred-modal">
        <button class="cred-modal__close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        <div class="cred-modal__icon"><i class="fas fa-shield-alt"></i></div>
        <h2 id="modal-titulo">Credenciales generadas</h2>

        <div class="cred-dato">
            <label>Usuario (ingresar en campo "Cédula")</label>
            <div class="cred-dato__val">
                <span id="modal-usuario">—</span>
                <button class="btn-copy" onclick="copiar('modal-usuario')" title="Copiar usuario">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="cred-dato">
            <label>Contraseña</label>
            <div class="cred-dato__val">
                <span id="modal-password">—</span>
                <button class="btn-copy" onclick="copiar('modal-password')" title="Copiar contraseña">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="cred-dato">
            <label>Rol asignado</label>
            <div class="cred-dato__val">
                <span id="modal-rol">—</span>
            </div>
        </div>

        <div class="cred-aviso">
            <i class="fas fa-exclamation-triangle" style="flex-shrink:0;margin-top:2px;"></i>
            <span>Anote o comparta estas credenciales ahora. La contraseña <strong>no se puede recuperar</strong> después de cerrar esta ventana.</span>
        </div>

        <button class="btn-cerrar-modal" onclick="cerrarModal()">
            <i class="fas fa-check"></i> Listo, ya las anoté
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

async function generarCredenciales() {
    const rol    = document.querySelector('input[name=rol]:checked').value;
    const nombre = document.getElementById('inp-nombre').value.trim();
    const btn    = document.getElementById('btn-generar');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

    const res = await fetch('{{ route('admin.credenciales.generar') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ rol, nombre })
    }).then(r => r.json()).catch(() => null);

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> Generar';

    if (!res || !res.ok) {
        alert(res?.msg || 'Error al generar credenciales.');
        return;
    }

    // Mostrar en modal
    document.getElementById('modal-usuario').textContent  = res.cedula;
    document.getElementById('modal-password').textContent = res.password;
    document.getElementById('modal-rol').textContent      = res.rol === 'laboratorio' ? '🧪 Laboratorio' : '💊 Farmacia';
    document.getElementById('modal-titulo').textContent   = 'Credenciales generadas — ' + (res.nombre || res.rol);
    document.getElementById('modalOverlay').classList.add('visible');

    // Agregar fila a la tabla
    const tbody = document.getElementById('tabla-body');
    if (tbody) {
        const badgeClass = res.rol === 'laboratorio' ? 'rbadge--lab' : 'rbadge--farm';
        const badgeIcon  = res.rol === 'laboratorio' ? 'fa-flask' : 'fa-pills';
        const badgeLabel = res.rol === 'laboratorio' ? 'Laboratorio' : 'Farmacia';

        const tr = document.createElement('tr');
        tr.id = 'fila-' + res.cedula;
        tr.innerHTML = `
            <td style="font-family:monospace;font-weight:700;font-size:.88rem;">${res.cedula}</td>
            <td>${res.nombre} ${res.rol === 'laboratorio' ? 'Laboratorio' : 'Farmacia'}</td>
            <td style="font-size:.8rem;color:#6b7280;">${res.correo}</td>
            <td><span class="rbadge ${badgeClass}"><i class="fas ${badgeIcon}"></i> ${badgeLabel}</span></td>
            <td><span class="estado-badge estado-badge--activo" id="estado-${res.cedula}">Activo</span></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="btn-accion btn-accion--toggle-on"
                        id="btn-toggle-${res.cedula}"
                        onclick="toggleEstado('${res.cedula}', this)">
                    <i class="fas fa-ban"></i> Deshabilitar
                </button>
                <button class="btn-accion btn-accion--reset"
                        onclick="resetPassword('${res.cedula}', '${res.nombre}')">
                    <i class="fas fa-sync-alt"></i> Reset pass
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    document.getElementById('inp-nombre').value = '';
}

async function toggleEstado(cedula, btn) {
    btn.disabled = true;
    const res = await fetch(`/admin/credenciales/${cedula}/revocar`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).catch(() => null);
    btn.disabled = false;

    if (!res?.ok) { alert(res?.msg || 'Error.'); return; }

    const badge = document.getElementById('estado-' + cedula);
    if (res.estado === 'activo') {
        badge.textContent = 'Activo';
        badge.className = 'estado-badge estado-badge--activo';
        btn.className = 'btn-accion btn-accion--toggle-on';
        btn.innerHTML = '<i class="fas fa-ban"></i> Deshabilitar';
    } else {
        badge.textContent = 'Inactivo';
        badge.className = 'estado-badge estado-badge--inactivo';
        btn.className = 'btn-accion btn-accion--toggle-off';
        btn.innerHTML = '<i class="fas fa-check"></i> Habilitar';
    }
}

async function resetPassword(cedula, nombre) {
    if (!confirm(`¿Generar nueva contraseña para "${nombre}"?`)) return;

    const res = await fetch(`/admin/credenciales/${cedula}/reset-password`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).catch(() => null);

    if (!res?.ok) { alert(res?.msg || 'Error.'); return; }

    document.getElementById('modal-titulo').textContent   = 'Nueva contraseña — ' + nombre;
    document.getElementById('modal-usuario').textContent  = cedula;
    document.getElementById('modal-password').textContent = res.password;
    document.getElementById('modal-rol').textContent      = '🔄 Contraseña restablecida';
    document.getElementById('modalOverlay').classList.add('visible');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('visible');
}

function copiar(id) {
    const txt = document.getElementById(id).textContent;
    navigator.clipboard.writeText(txt).then(() => {
        const btn = document.querySelector(`[onclick="copiar('${id}')"]`);
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check" style="color:#22c55e;"></i>';
        setTimeout(() => btn.innerHTML = orig, 1500);
    });
}
</script>
@endpush
