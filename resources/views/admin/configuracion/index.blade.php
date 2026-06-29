@extends('layouts.admin')

@section('titulo', 'Configuración del Sistema')
@section('seccion_titulo', 'Configuración del Sistema')
@section('volver', route('admin.dashboard'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-sliders-h"></i> Configuración del Sistema</h1>
            <p class="page-header__sub">Ajustes generales, citas, notificaciones y seguridad</p>
        </div>
        <form method="POST" action="{{ route('admin.configuracion.cache') }}" id="formCache">
            @csrf
            <button type="submit" class="btn-secondary">
                <i class="fas fa-broom"></i> Limpiar caché
            </button>
        </form>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    {{-- ═══ PESTAÑAS ═══ --}}
    <div class="config-tabs">
        <button type="button" class="config-tab config-tab--active" data-tab="general">
            <i class="fas fa-building"></i> General
        </button>
        <button type="button" class="config-tab" data-tab="citas">
            <i class="fas fa-calendar-alt"></i> Citas
        </button>
        <button type="button" class="config-tab" data-tab="notificaciones">
            <i class="fas fa-bell"></i> Notificaciones
        </button>
        <button type="button" class="config-tab" data-tab="seguridad">
            <i class="fas fa-shield-alt"></i> Seguridad
        </button>
        <button type="button" class="config-tab" data-tab="backup">
            <i class="fas fa-database"></i> Backup
        </button>
        <button type="button" class="config-tab" data-tab="permisos">
            <i class="fas fa-user-lock"></i> Permisos
        </button>
    </div>

    {{-- ═══ GENERAL ═══ --}}
    <div class="config-panel config-panel--active" id="panel-general">
        <form method="POST" action="{{ route('admin.configuracion.update', 'general') }}" class="form-card" id="formGeneral">
            @csrf
            @method('PUT')
            <h3 class="form-card__seccion"><i class="fas fa-building"></i> Información General</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre del sistema</label>
                    <input type="text" name="nombre_sistema" value="{{ $config['general']['nombre_sistema'] }}">
                </div>
                <div class="form-group">
                    <label>Empresa</label>
                    <input type="text" name="empresa" value="{{ $config['general']['empresa'] }}">
                </div>
                <div class="form-group">
                    <label>Teléfono de contacto</label>
                    <input type="text" name="telefono_contacto" value="{{ $config['general']['telefono_contacto'] }}">
                </div>
                <div class="form-group">
                    <label>Email de contacto</label>
                    <input type="email" name="email_contacto" value="{{ $config['general']['email_contacto'] }}">
                </div>
                <div class="form-group form-group--full">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="{{ $config['general']['direccion'] }}">
                </div>
                <div class="form-group form-group--full">
                    <label>Horario de atención</label>
                    <input type="text" name="horario_atencion" value="{{ $config['general']['horario_atencion'] }}">
                </div>
            </div>
            <div class="form-card__actions">
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>

    {{-- ═══ CITAS ═══ --}}
    <div class="config-panel" id="panel-citas">
        <form method="POST" action="{{ route('admin.configuracion.update', 'citas') }}" class="form-card" id="formCitas">
            @csrf
            @method('PUT')
            <h3 class="form-card__seccion"><i class="fas fa-calendar-alt"></i> Configuración de Citas</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Duración de cita por defecto (min)</label>
                    <input type="number" name="duracion_cita_default" min="10" max="120" value="{{ $config['citas']['duracion_cita_default'] }}">
                </div>
                <div class="form-group">
                    <label>Máximo de citas por día (por médico)</label>
                    <input type="number" name="max_citas_dia" min="1" max="50" value="{{ $config['citas']['max_citas_dia'] }}">
                </div>
                <div class="form-group">
                    <label>Tiempo de recordatorio (horas antes)</label>
                    <input type="number" name="tiempo_recordatorio" min="1" max="72" value="{{ $config['citas']['tiempo_recordatorio'] }}">
                </div>
                <div class="form-group">
                    <label class="checkbox-item" style="width:100%;">
                        <input type="checkbox" name="recordatorio_cita" value="1" {{ $config['citas']['recordatorio_cita'] ? 'checked' : '' }}>
                        Enviar recordatorio de cita automáticamente
                    </label>
                </div>
            </div>
            <div class="form-card__actions">
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>

    {{-- ═══ NOTIFICACIONES ═══ --}}
    <div class="config-panel" id="panel-notificaciones">
        <form method="POST" action="{{ route('admin.configuracion.update', 'notificaciones') }}" class="form-card" id="formNotif">
            @csrf
            @method('PUT')
            <h3 class="form-card__seccion"><i class="fas fa-bell"></i> Notificaciones</h3>
            <div class="checkbox-grid" style="margin-bottom: 20px;">
                <label class="checkbox-item">
                    <input type="checkbox" name="notif_nueva_cita" value="1" {{ $config['notificaciones']['notif_nueva_cita'] ? 'checked' : '' }}>
                    Nueva cita agendada
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="notif_cita_cancelada" value="1" {{ $config['notificaciones']['notif_cita_cancelada'] ? 'checked' : '' }}>
                    Cita cancelada
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="notif_recordatorio" value="1" {{ $config['notificaciones']['notif_recordatorio'] ? 'checked' : '' }}>
                    Recordatorio de cita
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="notif_urgencia" value="1" {{ $config['notificaciones']['notif_urgencia'] ? 'checked' : '' }}>
                    Emergencias
                </label>
            </div>
            <div class="form-group">
                <label>Método de notificación</label>
                <select name="metodo_notificacion">
                    <option value="email" {{ $config['notificaciones']['metodo_notificacion'] === 'email' ? 'selected' : '' }}>Solo Email</option>
                    <option value="sms" {{ $config['notificaciones']['metodo_notificacion'] === 'sms' ? 'selected' : '' }}>Solo WhatsApp</option>
                    <option value="ambos" {{ $config['notificaciones']['metodo_notificacion'] === 'ambos' ? 'selected' : '' }}>Email + WhatsApp</option>
                </select>
            </div>
            <div class="form-card__actions">
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>

        {{-- ── Recordatorios manuales ── --}}
        <div class="form-card" style="margin-top:20px;">
            <h3 class="form-card__seccion"><i class="fas fa-paper-plane"></i> Enviar recordatorios ahora</h3>
            <p style="color:#6b7280;font-size:.875rem;margin-bottom:16px;">
                Envía un mensaje de WhatsApp a todos los pacientes que tengan cita en las próximas
                <strong>{{ $config['citas']['tiempo_recordatorio'] ?? 24 }} horas</strong>
                (según el tiempo configurado en la pestaña Citas).
            </p>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" id="btn-recordatorios" onclick="enviarRecordatorios()"
                        class="btn-success">
                    <i class="fas fa-paper-plane"></i> Enviar recordatorios
                </button>
                <span id="rec-resultado" style="font-size:.875rem;color:#6b7280;"></span>
            </div>
        </div>

        {{-- ── WhatsApp ── --}}
        <div class="form-card" style="margin-top:20px;">
            <h3 class="form-card__seccion"><i class="fab fa-whatsapp" style="color:#25d366"></i> Conexión WhatsApp</h3>

            <div id="wa-status-box" style="display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid #e8edf2;margin-bottom:16px;">
                <span id="wa-dot" style="width:12px;height:12px;border-radius:50%;background:#d1d5db;flex-shrink:0;"></span>
                <div>
                    <div id="wa-label" style="font-weight:600;color:#374151;">Verificando...</div>
                    <div id="wa-sub" style="font-size:13px;color:#6b7280;"></div>
                </div>
                <div style="margin-left:auto;display:flex;gap:8px;" id="wa-actions"></div>
            </div>

            <div id="wa-qr-box" style="display:none;text-align:center;padding:10px 0;">
                <p style="color:#555;font-size:13px;margin-bottom:10px;">
                    Abre WhatsApp → <strong>Dispositivos vinculados → Vincular dispositivo</strong> y escanea:
                </p>
                <img id="wa-qr-img" src="" alt="QR WhatsApp"
                     style="width:220px;height:220px;border:5px solid #25d366;border-radius:10px;">
                <p style="font-size:12px;color:#9ca3af;margin-top:8px;">El código se renueva cada 55 segundos</p>
            </div>
        </div>
    </div>

    {{-- ═══ SEGURIDAD ═══ --}}
    <div class="config-panel" id="panel-seguridad">
        <form method="POST" action="{{ route('admin.configuracion.update', 'seguridad') }}" class="form-card" id="formSeguridad">
            @csrf
            @method('PUT')
            <h3 class="form-card__seccion"><i class="fas fa-shield-alt"></i> Seguridad</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Longitud mínima de contraseña</label>
                    <input type="number" name="pass_min_length" min="6" max="32" value="{{ $config['seguridad']['pass_min_length'] }}">
                </div>
                <div class="form-group">
                    <label>Tiempo de inactividad de sesión (min)</label>
                    <input type="number" name="session_timeout" min="5" max="240" value="{{ $config['seguridad']['session_timeout'] }}">
                </div>
                <div class="form-group">
                    <label class="checkbox-item" style="width:100%;">
                        <input type="checkbox" name="sesion_unica" value="1" {{ $config['seguridad']['sesion_unica'] ? 'checked' : '' }}>
                        Permitir solo una sesión activa por usuario
                    </label>
                </div>
            </div>
            <div class="form-card__nota" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <a href="{{ route('admin.auditoria.index') }}"
                   class="btn-secondary" style="white-space:nowrap;text-decoration:none;">
                    <i class="fas fa-clipboard-list"></i> Ver Auditoría
                </a>
            </div>
            <div class="form-card__actions">
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>

    {{-- ═══ PERMISOS ═══ --}}
    <div class="config-panel" id="panel-permisos">
        @php use App\Services\Permisos; @endphp

        <div style="margin-bottom:16px;">
            <input type="text" id="buscadorPermisos" placeholder="Buscar por nombre o cédula..."
                   oninput="filtrarPermisos()"
                   style="width:100%;max-width:380px;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.875rem;">
        </div>

        <h4 style="font-size:.9rem;font-weight:700;color:#1e3a5f;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-stethoscope"></i> Médicos
        </h4>

        @forelse($medicos as $u)
        @php
            $perms      = Permisos::obtenerPermisos($u->cedula);
            $badgeBg    = $u->estado === 'activo' ? '#dcfce7' : '#fee2e2';
            $badgeColor = $u->estado === 'activo' ? '#166534' : '#991b1b';
        @endphp
        <div class="form-card perm-card" style="margin-bottom:14px;padding:16px 20px;"
             data-nombre="{{ strtolower($u->nombres.' '.$u->apellido1.' '.$u->cedula) }}">
            <form method="POST" action="{{ route('admin.permisos.update', $u->cedula) }}">
                @csrf @method('PUT')
                <input type="hidden" name="rol" value="medico">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <div>
                        <span style="font-weight:700;font-size:.9rem;">{{ $u->apellido1 }}, {{ $u->nombres }}</span>
                        <span style="display:block;font-size:.75rem;color:#6b7280;">{{ $u->cedula }}</span>
                    </div>
                    <span style="padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                        {{ ucfirst($u->estado) }}
                    </span>
                </div>
                <div class="checkbox-grid" style="margin-bottom:12px;">
                    @foreach(Permisos::MEDICO_MODULOS as $clave => $nombre)
                    <label class="checkbox-item">
                        <input type="checkbox" name="perm_{{ $clave }}" value="1"
                               {{ ($perms[$clave] ?? true) ? 'checked' : '' }}>
                        {{ $nombre }}
                    </label>
                    @endforeach
                </div>
                <div class="form-card__actions" style="margin:0;">
                    <button type="submit" class="btn-success" style="padding:6px 16px;font-size:.82rem;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
        @empty
        <p style="color:#9ca3af;font-size:.875rem;margin-bottom:16px;">Sin médicos registrados.</p>
        @endforelse

        <h4 style="font-size:.9rem;font-weight:700;color:#1e3a5f;margin:20px 0 10px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-user-nurse"></i> Paramédicos
        </h4>

        @forelse($paramedicos as $u)
        @php
            $perms      = Permisos::obtenerPermisos($u->cedula);
            $badgeBg    = $u->estado === 'activo' ? '#dcfce7' : '#fee2e2';
            $badgeColor = $u->estado === 'activo' ? '#166534' : '#991b1b';
        @endphp
        <div class="form-card perm-card" style="margin-bottom:14px;padding:16px 20px;"
             data-nombre="{{ strtolower($u->nombres.' '.$u->apellido1.' '.$u->cedula) }}">
            <form method="POST" action="{{ route('admin.permisos.update', $u->cedula) }}">
                @csrf @method('PUT')
                <input type="hidden" name="rol" value="paramedico">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <div>
                        <span style="font-weight:700;font-size:.9rem;">{{ $u->apellido1 }}, {{ $u->nombres }}</span>
                        <span style="display:block;font-size:.75rem;color:#6b7280;">{{ $u->cedula }}</span>
                    </div>
                    <span style="padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                        {{ ucfirst($u->estado) }}
                    </span>
                </div>
                <div class="checkbox-grid" style="margin-bottom:12px;">
                    @foreach(Permisos::PARA_MODULOS as $clave => $nombre)
                    <label class="checkbox-item">
                        <input type="checkbox" name="perm_{{ $clave }}" value="1"
                               {{ ($perms[$clave] ?? true) ? 'checked' : '' }}>
                        {{ $nombre }}
                    </label>
                    @endforeach
                </div>
                <div class="form-card__actions" style="margin:0;">
                    <button type="submit" class="btn-success" style="padding:6px 16px;font-size:.82rem;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
        @empty
        <p style="color:#9ca3af;font-size:.875rem;">Sin paramédicos registrados.</p>
        @endforelse
    </div>

    {{-- ═══ BACKUP ═══ --}}
    <div class="config-panel" id="panel-backup">

        {{-- Descargar --}}
        <div class="form-card" style="margin-bottom:20px;">
            <h3 class="form-card__seccion"><i class="fas fa-download"></i> Crear Backup</h3>
            <div class="form-grid">
                <div class="form-group form-group--full">
                    <p style="color:#64748b;font-size:.9rem;line-height:1.6;margin:0 0 12px;">
                        Genera un archivo <strong>.sql</strong> con toda la estructura y datos actuales de la base de datos.
                        El archivo se descarga directamente a tu computadora.
                    </p>
                    <div class="form-card__nota" style="margin-bottom:16px;">
                        <i class="fas fa-info-circle"></i>
                        Guarda el archivo en un lugar seguro fuera del servidor. Puedes usarlo para restaurar la base de datos en cualquier momento.
                    </div>
                    <a href="{{ route('admin.backup.descargar') }}"
                       class="btn-success"
                       style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                        <i class="fas fa-download"></i> Descargar Backup Ahora
                    </a>
                </div>
            </div>
        </div>

        {{-- Restaurar --}}
        <div class="form-card">
            <h3 class="form-card__seccion"><i class="fas fa-upload"></i> Restaurar Backup</h3>
            <form method="POST" action="{{ route('admin.backup.restaurar') }}"
                  enctype="multipart/form-data" id="formRestaura">
                @csrf
                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <p style="color:#64748b;font-size:.9rem;line-height:1.6;margin:0 0 12px;">
                            Sube un archivo <strong>.sql</strong> generado previamente.
                            <strong style="color:#dc2626;">Esto reemplazará todos los datos actuales de la base de datos.</strong>
                        </p>
                        <div style="background:#fff7ed;border:1px solid #fdba74;border-radius:8px;padding:12px 16px;font-size:.85rem;color:#9a3412;margin-bottom:16px;">
                            <i class="fas fa-triangle-exclamation"></i>
                            Esta acción no se puede deshacer. Asegúrate de tener un backup reciente antes de restaurar.
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar archivo .sql</label>
                        <input type="file" name="backup_file" accept=".sql" required
                               style="padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;background:#f9fafb;width:100%;">
                    </div>
                </div>
                <div class="form-card__actions">
                    <button type="button" class="action-btn action-btn--delete"
                            style="display:inline-flex;align-items:center;gap:8px;"
                            onclick="confirmarRestaura()">
                        <i class="fas fa-upload"></i> Restaurar Base de Datos
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/configuracion.css') }}">
@endpush

@push('scripts')
<script>
function confirmarConfig(formId, titulo) {
    document.getElementById(formId).addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: titulo,
            text: 'Los cambios se aplicarán de inmediato.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
}
confirmarConfig('formGeneral',     '¿Guardar configuración general?');
confirmarConfig('formCitas',       '¿Guardar configuración de citas?');
confirmarConfig('formNotif',       '¿Guardar configuración de notificaciones?');
confirmarConfig('formSeguridad',   '¿Guardar configuración de seguridad?');

function filtrarPermisos() {
    var txt = document.getElementById('buscadorPermisos').value.toLowerCase();
    document.querySelectorAll('.perm-card').forEach(function(c) {
        c.style.display = c.dataset.nombre.includes(txt) ? '' : 'none';
    });
}

function confirmarRestaura() {
    Swal.fire({
        title: '¿Restaurar base de datos?',
        html: 'Esta acción <strong>reemplazará todos los datos actuales</strong> con los del archivo seleccionado.<br><br>¿Estás seguro de continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-upload"></i> Sí, restaurar',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (result.isConfirmed) document.getElementById('formRestaura').submit();
    });
}

document.getElementById('formCache').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Limpiar caché?',
        text: 'Se eliminarán todos los archivos de caché del sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

document.querySelectorAll('.config-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.config-tab').forEach(t => t.classList.remove('config-tab--active'));
        document.querySelectorAll('.config-panel').forEach(p => p.classList.remove('config-panel--active'));

        tab.classList.add('config-tab--active');
        document.getElementById('panel-' + tab.dataset.tab).classList.add('config-panel--active');
    });
});

// ── WhatsApp WAHA ──────────────────────────────────────────
let waTimer = null;

function waActualizar() {
    fetch('{{ route('admin.whatsapp.status') }}')
        .then(r => r.json())
        .then(data => {
            const dot     = document.getElementById('wa-dot');
            const label   = document.getElementById('wa-label');
            const sub     = document.getElementById('wa-sub');
            const actions = document.getElementById('wa-actions');
            const qrBox   = document.getElementById('wa-qr-box');
            const qrImg   = document.getElementById('wa-qr-img');

            if (data.status === 'WORKING') {
                dot.style.background   = '#22c55e';
                label.textContent      = '✅ WhatsApp conectado';
                label.style.color      = '#15803d';
                sub.textContent        = data.nombre ? `${data.nombre} (${(data.numero||'').replace('@c.us','')})` : 'Número vinculado';
                qrBox.style.display    = 'none';
                clearInterval(waTimer);
                actions.innerHTML = `
                    <button type="button" onclick="waCambiarNumero()"
                        style="padding:6px 14px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;font-size:13px;cursor:pointer;">
                        <i class="fas fa-sync-alt"></i> Cambiar número
                    </button>`;
            } else if (data.status === 'SCAN_QR_CODE') {
                dot.style.background   = '#f59e0b';
                label.textContent      = '📱 Esperando escaneo del QR';
                label.style.color      = '#92400e';
                sub.textContent        = 'Escanea el código con WhatsApp';
                qrBox.style.display    = 'block';
                qrImg.src              = '{{ route('admin.whatsapp.qr') }}?t=' + Date.now();
                actions.innerHTML      = '';
                clearInterval(waTimer);
                waTimer = setInterval(() => {
                    qrImg.src = '{{ route('admin.whatsapp.qr') }}?t=' + Date.now();
                    waActualizar();
                }, 20000);
            } else if (data.status === 'FAILED') {
                // Sesión fallida: reiniciar automáticamente
                dot.style.background   = '#f59e0b';
                label.textContent      = '⏳ Iniciando sesión...';
                label.style.color      = '#92400e';
                sub.textContent        = 'Reconectando, espera un momento';
                qrBox.style.display    = 'none';
                actions.innerHTML      = '';
                fetch('{{ route('admin.whatsapp.logout') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                }).then(() => setTimeout(waActualizar, 8000));
            } else if (data.status === 'STARTING') {
                dot.style.background   = '#f59e0b';
                label.textContent      = '⏳ Iniciando sesión...';
                label.style.color      = '#92400e';
                sub.textContent        = 'Esto tarda unos segundos';
                qrBox.style.display    = 'none';
                actions.innerHTML      = '';
                clearInterval(waTimer);
                waTimer = setInterval(waActualizar, 5000);
            } else if (data.status === 'OFFLINE') {
                dot.style.background   = '#ef4444';
                label.textContent      = 'WAHA no disponible';
                label.style.color      = '#991b1b';
                sub.textContent        = 'El servicio de WhatsApp no responde';
                qrBox.style.display    = 'none';
                actions.innerHTML      = '';
            } else {
                dot.style.background   = '#f59e0b';
                label.textContent      = `Estado: ${data.status}`;
                label.style.color      = '#92400e';
                sub.textContent        = 'Esperando...';
                qrBox.style.display    = 'none';
                actions.innerHTML      = '';
                clearInterval(waTimer);
                waTimer = setInterval(waActualizar, 5000);
            }
        })
        .catch(() => {
            document.getElementById('wa-label').textContent = 'Sin conexión con WAHA';
            document.getElementById('wa-dot').style.background = '#ef4444';
        });
}

function waCambiarNumero() {
    Swal.fire({
        title: '¿Cambiar número de WhatsApp?',
        text: 'Se desconectará el número actual y deberás escanear el QR con el nuevo número.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('{{ route('admin.whatsapp.logout') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        }).then(() => {
            setTimeout(waActualizar, 2000);
        });
    });
}

function enviarRecordatorios() {
    const btn = document.getElementById('btn-recordatorios');
    const res = document.getElementById('rec-resultado');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    res.textContent = '';

    fetch('{{ route('admin.whatsapp.recordatorios') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar recordatorios';
        if (data.total === 0) {
            res.style.color = '#6b7280';
            res.textContent = 'No hay citas próximas en ese rango de horas.';
        } else {
            res.style.color = data.fallidos > 0 ? '#b45309' : '#15803d';
            res.textContent = `✓ ${data.enviados} de ${data.total} enviados` +
                              (data.fallidos > 0 ? ` (${data.fallidos} fallaron)` : '');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar recordatorios';
        res.style.color = '#dc2626';
        res.textContent = 'Error al enviar. Verifica la conexión WhatsApp.';
    });
}

// Ejecutar solo cuando la pestaña de notificaciones esté activa
document.querySelectorAll('.config-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        if (tab.dataset.tab === 'notificaciones') waActualizar();
    });
});
// Ejecutar si la pestaña activa al cargar es notificaciones
if (document.querySelector('.config-tab--active')?.dataset?.tab === 'notificaciones') {
    waActualizar();
}
</script>
@endpush