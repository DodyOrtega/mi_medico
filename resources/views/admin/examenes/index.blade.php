@extends('layouts.admin')

@section('titulo', 'Documentos Pacientes')

@section('contenido')

<div class="admin-welcome">
    <div>
        <h2 style="font-size:1.3rem;font-weight:700;color:#1a2e44;margin:0 0 4px;">
            <i class="fas fa-folder-open" style="color:#0078d7;margin-right:8px;"></i>
            Documentos de Pacientes
        </h2>
        <p style="font-size:.88rem;color:#6b7280;margin:0;">
            Archivos subidos por los pacientes desde su portal
        </p>
    </div>
    {{-- Botones Drive --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <a href="{{ route('admin.examenes.cambiar-drive') }}" class="adex-btn-change-drive">
        <i class="fas fa-rotate"></i> Cambiar cuenta
    </a>
    <form method="POST" action="{{ route('admin.examenes.backup-drive') }}" id="formBackup">
        @csrf
        <button type="submit" class="adex-btn-drive {{ $backupInfo['configurado'] ? '' : 'adex-btn-drive--disabled' }}"
                {{ $backupInfo['configurado'] ? '' : 'disabled' }}
                onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Enviando…';this.closest(\'form\').submit();">
            <i class="fab fa-google-drive"></i>
            {{ $backupInfo['configurado'] ? 'Backup a Drive ahora' : 'Drive no configurado' }}
        </button>
    </form>
    @if($backupInfo["configurado"])
    <button type="button" class="adex-btn-restaurar" onclick="document.getElementById('seccionRestaurar').scrollIntoView({behavior:'smooth'})">
        <i class="fas fa-cloud-download-alt"></i> Restaurar desde Drive
    </button>
    @endif
    </div>
</div>

{{-- ═══ ALERTAS ═══ --}}
@if(session('backup_ok'))
<div class="adex-alert adex-alert--ok">
    <i class="fas fa-check-circle"></i> {{ session('backup_ok') }}
</div>
@endif
@if(session('backup_error'))
<div class="adex-alert adex-alert--err">
    <i class="fas fa-times-circle"></i> {{ session('backup_error') }}
</div>
@endif
@if(session('restaurar_ok'))
<div class="adex-alert adex-alert--ok">
    <i class="fas fa-check-circle"></i> {{ session('restaurar_ok') }}
</div>
@endif
@if(session('restaurar_error'))
<div class="adex-alert adex-alert--err">
    <i class="fas fa-times-circle"></i> {{ session('restaurar_error') }}
</div>
@endif

{{-- ═══ PANEL BACKUP ═══ --}}
<div class="adex-section adex-backup-panel">
    <h3 class="adex-section__title">
        <i class="fab fa-google-drive" style="color:#4285f4;"></i> Estado del Backup a Google Drive
    </h3>

    <div class="adex-backup-row">
        {{-- Estado del remote --}}
        <div class="adex-backup-info">
            <div class="adex-backup-estado">
                @if($backupInfo['configurado'])
                    <span class="adex-chip adex-chip--ok"><i class="fas fa-check-circle"></i> Drive configurado</span>
                @else
                    <span class="adex-chip adex-chip--warn"><i class="fas fa-triangle-exclamation"></i> Drive no configurado</span>
                    <p class="adex-backup-hint">
                        Abre WSL Ubuntu y ejecuta: <code>rclone config</code>
                    </p>
                @endif
            </div>

            @if($backupInfo['ultimo'])
            <p class="adex-backup-meta">
                <i class="fas fa-clock" style="color:#6b7280;"></i>
                Último intento:
                <strong>{{ \Carbon\Carbon::parse($backupInfo['ultimo'])->isoFormat('D MMM YYYY · HH:mm') }}</strong>
            </p>
            @else
            <p class="adex-backup-meta" style="color:#9ca3af;">Sin backups ejecutados todavía.</p>
            @endif

            @if($backupInfo['hay_error'])
            <span class="adex-chip adex-chip--err" style="margin-top:6px;">
                <i class="fas fa-circle-exclamation"></i> El último backup tuvo errores — revisa el log
            </span>
            @elseif($backupInfo['ultimo'])
            <span class="adex-chip adex-chip--ok" style="margin-top:6px;">
                <i class="fas fa-circle-check"></i> Sin errores detectados
            </span>
            @endif
        </div>

        {{-- Log --}}
        @if(!empty($backupInfo['log_lines']))
        <div class="adex-backup-log">
            <div class="adex-backup-log__header">
                <i class="fas fa-terminal"></i> Últimas líneas del log
                <a href="{{ route('admin.examenes.backup-log') }}" target="_blank" class="adex-backup-log__full">
                    ver completo <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <pre class="adex-backup-log__body">{{ implode("\n", $backupInfo['log_lines']) }}</pre>
        </div>
        @endif
    </div>

    @if(!$backupInfo['configurado'])
    <div class="adex-backup-steps">
        <p style="font-weight:700;font-size:.88rem;color:#374151;margin:0 0 10px;">
            <i class="fas fa-circle-info" style="color:#0078d7;"></i> Cómo configurar el backup automático:
        </p>
        <ol class="adex-steps-list">
            <li>Abre <strong>WSL Ubuntu</strong> (no PowerShell)</li>
            <li>Instala rclone: <code>curl https://rclone.org/install.sh | sudo bash</code></li>
            <li>Configura Drive: <code>rclone config</code> → nombre: <code>mimedico-drive</code></li>
            <li>Vuelve aquí y haz clic en <strong>"Backup a Drive ahora"</strong></li>
        </ol>
    </div>

    {{-- Panel de diagnóstico --}}
    <div style="margin-top:14px;background:#0f172a;border-radius:10px;padding:14px 16px;">
        <p style="color:#94a3b8;font-size:.72rem;font-weight:700;margin:0 0 8px;text-transform:uppercase;letter-spacing:.05em;">
            <i class="fas fa-bug"></i> Diagnóstico (solo visible cuando no está configurado)
        </p>
        <table style="font-size:.75rem;font-family:monospace;border-collapse:collapse;width:100%;">
            <tr>
                <td style="color:#64748b;padding:3px 10px 3px 0;white-space:nowrap;">rclone binario</td>
                <td style="color:{{ $backupInfo['debug']['bin'] !== 'NO ENCONTRADO' ? '#86efac' : '#f87171' }};">
                    {{ $backupInfo['debug']['bin'] }}
                </td>
            </tr>
            <tr>
                <td style="color:#64748b;padding:3px 10px 3px 0;white-space:nowrap;">archivo config</td>
                <td style="color:{{ $backupInfo['debug']['config_file'] !== 'NO ENCONTRADO' ? '#86efac' : '#f87171' }};">
                    {{ $backupInfo['debug']['config_file'] }}
                </td>
            </tr>
            <tr>
                <td style="color:#64748b;padding:3px 10px 3px 0;">remote detectado</td>
                <td style="color:{{ $backupInfo['debug']['config_ok'] ? '#86efac' : '#f87171' }};">
                    {{ $backupInfo['debug']['config_ok'] ? 'SÍ — [mimedico-drive] encontrado' : 'NO encontrado en config' }}
                </td>
            </tr>
            <tr>
                <td style="color:#64748b;padding:3px 10px 3px 0;">exec() en PHP</td>
                <td style="color:{{ $backupInfo['debug']['exec_ok'] ? '#86efac' : '#fbbf24' }};">
                    {{ $backupInfo['debug']['exec_ok'] ? 'habilitado' : 'DESHABILITADO — backup manual' }}
                </td>
            </tr>
            <tr>
                <td style="color:#64748b;padding:3px 10px 3px 0;">HOME (PHP)</td>
                <td style="color:#e2e8f0;">{{ $backupInfo['debug']['home'] }}</td>
            </tr>
        </table>
    </div>
    @endif

{{-- ═══ RESTAURAR DESDE DRIVE ═══ --}}
@if($backupInfo['configurado'])
<div class="adex-section adex-restaurar-panel" id="seccionRestaurar">
    <h3 class="adex-section__title">
        <i class="fas fa-cloud-download-alt" style="color:#f97316;"></i> Restaurar desde Google Drive
    </h3>

    <form method="POST" action="{{ route('admin.examenes.restaurar-drive') }}" id="formRestaurar"
          onsubmit="return confirmarRestaurar(this);">
        @csrf
        <div class="adex-restaurar-form">
            <div class="adex-restaurar-input-wrap">
                <label class="adex-restaurar-label">
                    <i class="fas fa-id-card" style="color:#0078d7;margin-right:5px;"></i>
                    Cédula del paciente <span style="font-weight:400;color:#9ca3af;">(opcional — vacío restaura todo)</span>
                </label>
                <input type="text" name="cedula" id="inputCedula"
                       class="adex-restaurar-input"
                       placeholder="Ej: 1234567890"
                       maxlength="12"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
            <button type="submit" class="adex-btn-restaurar adex-btn-restaurar--lg" id="btnRestaurar">
                <i class="fas fa-cloud-download-alt"></i> Iniciar restauración
            </button>
        </div>
        <p class="adex-restaurar-hint">
            <i class="fas fa-circle-info"></i>
            Los archivos existentes <strong>no se borran</strong>. Solo se copian los que faltan.
        </p>
    </form>

    {{-- Panel de estado ─────────────────── --}}
    <div id="panelEstado" style="margin-top:18px;display:none;">
        <div class="adex-restaurar-estado" id="estadoContenido">
            <div class="adex-restaurar-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <span id="estadoTexto">Verificando estado...</span>
            </div>
        </div>
        <div class="adex-backup-log" style="margin-top:12px;">
            <div class="adex-backup-log__header">
                <i class="fas fa-terminal"></i> Log en tiempo real
                <a href="{{ route('admin.examenes.restaurar-log') }}" target="_blank" class="adex-backup-log__full">
                    ver completo <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <pre class="adex-backup-log__body" id="logRestaurar" style="max-height:180px;">Esperando...</pre>
        </div>
    </div>
</div>
@endif

{{-- ═══ ESTADÍSTICAS ═══ --}}
<div class="adex-stats">
    <div class="adex-stat-card">
        <div class="adex-stat-card__icon" style="background:#eff6ff;color:#0078d7;">
            <i class="fas fa-file-medical"></i>
        </div>
        <div>
            <p class="adex-stat-card__val">{{ number_format($resumen['total_archivos']) }}</p>
            <p class="adex-stat-card__lbl">Archivos totales</p>
        </div>
    </div>
    <div class="adex-stat-card">
        <div class="adex-stat-card__icon" style="background:#f0fdf4;color:#22c55e;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <p class="adex-stat-card__val">{{ number_format($resumen['total_pacientes']) }}</p>
            <p class="adex-stat-card__lbl">Pacientes con archivos</p>
        </div>
    </div>
    <div class="adex-stat-card">
        <div class="adex-stat-card__icon" style="background:#fff7ed;color:#f97316;">
            <i class="fas fa-hard-drive"></i>
        </div>
        <div>
            @php
                $bytes = $resumen['total_bytes'];
                $size  = $bytes > 1048576 ? round($bytes / 1048576, 1) . ' MB'
                       : ($bytes > 1024    ? round($bytes / 1024)      . ' KB' : $bytes . ' B');
            @endphp
            <p class="adex-stat-card__val">{{ $size }}</p>
            <p class="adex-stat-card__lbl">Espacio usado</p>
        </div>
    </div>
</div>

{{-- ═══ ÚLTIMAS SUBIDAS ═══ --}}
<div class="adex-section">
    <h3 class="adex-section__title">
        <i class="fas fa-clock"></i> Últimas subidas
    </h3>

    @if(empty($resumen['recientes']))
    <div class="adex-empty">
        <i class="fas fa-folder-open"></i>
        <p>Ningún paciente ha subido archivos todavía.</p>
    </div>
    @else
    <div class="adex-table-wrap">
        <table class="adex-table">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Paciente</th>
                    <th>Tamaño</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumen['recientes'] as $r)
                @php
                    $iconos = [
                        'pdf'  => ['fas fa-file-pdf',   '#ef4444'],
                        'jpg'  => ['fas fa-file-image',  '#0ea5e9'],
                        'jpeg' => ['fas fa-file-image',  '#0ea5e9'],
                        'png'  => ['fas fa-file-image',  '#0ea5e9'],
                        'webp' => ['fas fa-file-image',  '#0ea5e9'],
                    ];
                    [$icono, $color] = $iconos[$r['extension']] ?? ['fas fa-file','#6b7280'];
                    $kb = round($r['tamanio'] / 1024);
                    $sz = $kb > 1024 ? round($kb / 1024, 1) . ' MB' : $kb . ' KB';
                    $fecha = \Carbon\Carbon::parse($r['fecha'])->isoFormat('D MMM YYYY · HH:mm');
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="{{ $icono }}" style="color:{{ $color }};font-size:1.2rem;"></i>
                            <div>
                                <p style="margin:0;font-weight:600;font-size:.85rem;color:#1a2e44;">{{ $r['descripcion'] }}</p>
                                <p style="margin:0;font-size:.72rem;color:#9ca3af;">{{ $r['nombre_original'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight:600;font-size:.85rem;">{{ $r['nombre_paciente'] }}</span>
                        <span style="display:block;font-size:.72rem;color:#9ca3af;">{{ $r['cedula'] }}</span>
                    </td>
                    <td style="font-size:.83rem;color:#6b7280;">{{ $sz }}</td>
                    <td style="font-size:.83rem;color:#6b7280;">{{ $fecha }}</td>
                    <td>
                        <a href="{{ route('admin.examenes.descargar', [$r['cedula'], $r['id']]) }}"
                           class="adex-btn-dl" title="Descargar">
                            <i class="fas fa-download"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ═══ POR PACIENTE ═══ --}}
@if(!empty($resumen['pacientes']))
<div class="adex-section">
    <h3 class="adex-section__title">
        <i class="fas fa-users"></i> Resumen por paciente
    </h3>
    <div class="adex-table-wrap">
        <table class="adex-table">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Archivos</th>
                    <th>Espacio</th>
                    <th>Última subida</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach(collect($resumen['pacientes'])->sortByDesc('ultimo') as $p)
                @php
                    $kb = round($p['tamanio'] / 1024);
                    $sz = $kb > 1024 ? round($kb / 1024, 1) . ' MB' : $kb . ' KB';
                    $ult = \Carbon\Carbon::parse($p['ultimo'])->isoFormat('D MMM YYYY');
                @endphp
                <tr>
                    <td>
                        <span style="font-weight:600;font-size:.88rem;">{{ $p['nombre'] }}</span>
                        <span style="display:block;font-size:.72rem;color:#9ca3af;">{{ $p['cedula'] }}</span>
                    </td>
                    <td>
                        <span class="adex-badge">
                            <i class="fas fa-file" style="margin-right:4px;"></i>{{ $p['archivos'] }}
                        </span>
                    </td>
                    <td style="font-size:.85rem;color:#6b7280;">{{ $sz }}</td>
                    <td style="font-size:.83rem;color:#6b7280;">{{ $ult }}</td>
                    <td>
                        <a href="{{ route('medico.examenes.index', $p['cedula']) }}"
                           class="adex-btn-view" title="Ver archivos">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
/* ── Alertas ── */
.adex-alert { padding:12px 16px; border-radius:10px; font-size:.88rem;
              display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.adex-alert--ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
.adex-alert--err { background:#fff5f5; border:1px solid #fecaca; color:#dc2626; }

/* ── Botón cambiar cuenta ── */
.adex-btn-change-drive { display:inline-flex; align-items:center; gap:8px; padding:9px 18px;
                         background:#f3f4f6; color:#374151; border-radius:8px; font-weight:600;
                         font-size:.85rem; text-decoration:none; transition:all .2s;
                         white-space:nowrap; border:1px solid #e5e7eb; }
.adex-btn-change-drive:hover { background:#e5e7eb; color:#1a2e44; }

/* ── Botón Drive ── */
.adex-btn-drive { display:inline-flex; align-items:center; gap:8px; padding:9px 18px;
                  background:linear-gradient(135deg,#4285f4,#1a73e8); color:#fff;
                  border:none; border-radius:8px; font-weight:600; cursor:pointer;
                  font-size:.85rem; transition:all .2s; white-space:nowrap; }
.adex-btn-drive:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(66,133,244,.4); }
.adex-btn-drive--disabled { background:#d1d5db; color:#9ca3af; cursor:not-allowed; }
.adex-btn-drive--disabled:hover { transform:none; box-shadow:none; }

/* ── Panel backup ── */
.adex-backup-panel { margin-bottom:20px; }
.adex-backup-row { display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start; }
.adex-backup-info { flex:0 0 280px; display:flex; flex-direction:column; gap:8px; }
.adex-backup-meta { font-size:.83rem; color:#374151; margin:4px 0; }
.adex-backup-hint { font-size:.8rem; color:#6b7280; margin:4px 0; }
.adex-backup-hint code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:.78rem; }

/* ── Chips ── */
.adex-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px;
             border-radius:20px; font-size:.78rem; font-weight:600; }
.adex-chip--ok   { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.adex-chip--warn { background:#fefce8; color:#854d0e; border:1px solid #fde68a; }
.adex-chip--err  { background:#fff5f5; color:#dc2626; border:1px solid #fecaca; }

/* ── Log terminal ── */
.adex-backup-log { flex:1; min-width:0; }
.adex-backup-log__header { font-size:.8rem; font-weight:700; color:#374151;
                            display:flex; align-items:center; gap:6px; margin-bottom:8px; }
.adex-backup-log__full { margin-left:auto; font-size:.75rem; color:#0078d7;
                          text-decoration:none; display:flex; align-items:center; gap:4px; }
.adex-backup-log__full:hover { text-decoration:underline; }
.adex-backup-log__body { background:#0f172a; color:#86efac; font-size:.72rem; font-family:monospace;
                          padding:12px 14px; border-radius:8px; margin:0; white-space:pre-wrap;
                          word-break:break-all; max-height:200px; overflow-y:auto; line-height:1.5; }

/* ── Steps ── */
.adex-backup-steps { background:#f8fafc; border:1px dashed #cbd5e1; border-radius:10px;
                     padding:14px 18px; margin-top:14px; }
.adex-steps-list { margin:0; padding-left:18px; display:flex; flex-direction:column; gap:6px; }
.adex-steps-list li { font-size:.85rem; color:#374151; }
.adex-steps-list code { background:#e2e8f0; padding:2px 6px; border-radius:4px; font-size:.78rem; }

/* ── Stats ── */
.adex-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
              gap:14px; margin-bottom:24px; }
.adex-stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px;
                  padding:18px; display:flex; align-items:center; gap:14px;
                  box-shadow:0 1px 4px rgba(0,0,0,.05); }
.adex-stat-card__icon { width:44px; height:44px; border-radius:12px; display:flex;
                         align-items:center; justify-content:center; font-size:1.2rem;
                         flex-shrink:0; }
.adex-stat-card__val { font-size:1.5rem; font-weight:800; color:#1a2e44; margin:0 0 2px; }
.adex-stat-card__lbl { font-size:.75rem; color:#9ca3af; margin:0; }

/* ── Sección ── */
.adex-section { background:#fff; border:1px solid #e5e7eb; border-radius:14px;
                padding:20px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.adex-section__title { font-size:.95rem; font-weight:700; color:#1a2e44;
                        display:flex; align-items:center; gap:8px; margin:0 0 16px; }
.adex-section__title i { color:#0078d7; }

/* ── Empty ── */
.adex-empty { text-align:center; padding:40px; color:#9ca3af; }
.adex-empty i { font-size:2.5rem; display:block; margin-bottom:12px; color:#d1d5db; }
.adex-empty p { margin:4px 0; font-size:.9rem; }

/* ── Tabla ── */
.adex-table-wrap { overflow-x:auto; }
.adex-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.adex-table th { padding:10px 14px; background:#f9fafb; color:#6b7280;
                 font-size:.75rem; font-weight:700; text-transform:uppercase;
                 letter-spacing:.04em; border-bottom:1px solid #e5e7eb; text-align:left; }
.adex-table td { padding:12px 14px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.adex-table tr:last-child td { border-bottom:none; }
.adex-table tr:hover td { background:#fafafa; }

/* ── Botones ── */
.adex-btn-dl { width:32px; height:32px; border-radius:8px; background:#eff6ff;
               color:#0078d7; display:inline-flex; align-items:center; justify-content:center;
               text-decoration:none; font-size:.8rem; transition:all .2s; }
.adex-btn-dl:hover { background:#0078d7; color:#fff; }

.adex-btn-view { display:inline-flex; align-items:center; gap:5px; padding:6px 12px;
                 background:#f3f4f6; border-radius:7px; color:#374151; text-decoration:none;
                 font-size:.8rem; font-weight:600; transition:all .2s; }
.adex-btn-view:hover { background:#0078d7; color:#fff; }

.adex-badge { background:#eff6ff; color:#0078d7; border-radius:20px;
              padding:3px 10px; font-size:.78rem; font-weight:700; }

.adex-btn-restaurar { display:inline-flex; align-items:center; gap:8px; padding:9px 18px;
                      background:linear-gradient(135deg,#f97316,#ea580c); color:#fff;
                      border:none; border-radius:8px; font-weight:600; cursor:pointer;
                      font-size:.85rem; transition:all .2s; white-space:nowrap; }
.adex-btn-restaurar:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(249,115,22,.4); }
.adex-btn-restaurar--lg { padding:10px 22px; font-size:.9rem; }

/* ── Restaurar panel ── */
.adex-restaurar-panel { margin-bottom:20px; }
.adex-restaurar-form { display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap; }
.adex-restaurar-input-wrap { display:flex; flex-direction:column; gap:5px; flex:1; min-width:220px; }
.adex-restaurar-label { font-size:.8rem; font-weight:700; color:#374151; }
.adex-restaurar-input { padding:9px 13px; border:1px solid #d1d5db; border-radius:8px;
                         font-size:.88rem; color:#1a2e44; outline:none; transition:border .2s; width:100%; }
.adex-restaurar-input:focus { border-color:#f97316; box-shadow:0 0 0 3px rgba(249,115,22,.12); }
.adex-restaurar-hint { font-size:.78rem; color:#6b7280; margin:10px 0 0; }
.adex-restaurar-hint i { color:#0078d7; margin-right:4px; }

/* ── Estado panel ── */
.adex-restaurar-estado { padding:12px 16px; border-radius:10px; font-size:.88rem;
                          border:1px solid #e5e7eb; background:#f9fafb; }
.adex-restaurar-spinner { display:flex; align-items:center; gap:10px; color:#f97316; font-weight:600; }
.adex-restaurar-ok { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
.adex-restaurar-err { background:#fff5f5; border-color:#fecaca; color:#dc2626; }
</style>
@endpush

@push('scripts')
<script>
const ESTADO_URL = "{{ route('admin.examenes.restaurar-estado') }}";
let pollingInterval = null;

function confirmarRestaurar(form) {
    const cedula = document.getElementById('inputCedula').value.trim();
    const msg = cedula
        ? `Restaurar examenes del paciente con cedula ${cedula} desde Google Drive?\n\nSolo se copiaran los archivos que falten.`
        : 'Restaurar TODOS los examenes desde Google Drive?\n\nSolo se copiaran los archivos que falten.';
    if (!confirm(msg)) return false;
    document.getElementById('btnRestaurar').disabled = true;
    document.getElementById('btnRestaurar').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando...';
    return true;
}

function iniciarPolling() {
    document.getElementById('panelEstado').style.display = 'block';
    verificarEstado();
    pollingInterval = setInterval(verificarEstado, 3000);
}

async function verificarEstado() {
    try {
        const res  = await fetch(ESTADO_URL);
        const data = await res.json();

        if (!data.inicio) return;

        document.getElementById('panelEstado').style.display = 'block';

        const log = data.log_lines.join('\n') || 'Esperando salida de rclone...';
        document.getElementById('logRestaurar').textContent = log;

        const box    = document.getElementById('estadoContenido');
        const textoEl = document.getElementById('estadoTexto');

        const cedulaInfo = data.cedula ? ` (cedula: ${data.cedula})` : ' completa';

        if (data.completado && !data.hay_error) {
            box.className = 'adex-restaurar-estado adex-restaurar-ok';
            box.innerHTML = '<i class="fas fa-check-circle"></i> Restauracion' + cedulaInfo + ' completada correctamente.';
            pararPolling();
        } else if (data.hay_error) {
            box.className = 'adex-restaurar-estado adex-restaurar-err';
            box.innerHTML = '<i class="fas fa-circle-exclamation"></i> La restauracion tuvo errores. Revisa el log completo.';
            pararPolling();
        } else if (data.en_progreso) {
            box.className = 'adex-restaurar-estado';
            box.innerHTML = '<div class="adex-restaurar-spinner"><i class="fas fa-spinner fa-spin"></i><span>Restauracion' + cedulaInfo + ' en progreso...</span></div>';
        }
    } catch (e) {
        console.error('Error verificando estado:', e);
    }
}

function pararPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Al cargar la pagina, verificar si hay una restauracion activa o reciente
document.addEventListener('DOMContentLoaded', function () {
    @if(session('restaurar_ok'))
    iniciarPolling();
    @endif
    // Si ya habia una en progreso antes de recargar, tambien la verificamos
    verificarEstado().then(() => {
        const box = document.getElementById('estadoContenido');
        if (box && box.querySelector('.adex-restaurar-spinner')) {
            iniciarPolling();
        }
    });
});
</script>
@endpush
