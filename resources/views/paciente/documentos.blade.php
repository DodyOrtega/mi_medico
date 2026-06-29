@extends('layouts.paciente')

@section('titulo', 'Mis Documentos')
@section('seccion_titulo', 'Mis Documentos')
@section('volver', route('paciente.dashboard'))

@section('contenido')

<div class="page-header">
    <div>
        <h2 class="page-header__title"><i class="fas fa-file-medical"></i> Mis Documentos</h2>
        <p class="page-header__sub">Sube y consulta tus exámenes, radiografías y resultados</p>
    </div>
</div>

{{-- ═══ ALERTAS ═══ --}}
@if(session('exito'))
<div class="doc-alert doc-alert--ok">
    <i class="fas fa-check-circle"></i> {{ session('exito') }}
</div>
@endif
@if(session('error') || $errors->any())
<div class="doc-alert doc-alert--err">
    <i class="fas fa-times-circle"></i>
    {{ session('error') ?? $errors->first() }}
</div>
@endif

{{-- ═══ SUBIR ARCHIVO ═══ --}}
<div class="doc-upload-card">
    <div class="doc-upload-card__header">
        <i class="fas fa-cloud-upload-alt"></i>
        <span>Subir nuevo documento</span>
    </div>

    <form method="POST" action="{{ route('paciente.examenes.subir') }}"
          enctype="multipart/form-data" id="formSubir">
        @csrf

        <div class="doc-upload-body">
            <div class="doc-dropzone" id="dropzone">
                <input type="file" name="archivo" id="archivoInput" class="doc-dropzone__input"
                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                <div class="doc-dropzone__content" id="dropContent">
                    <i class="fas fa-file-arrow-up doc-dropzone__icon"></i>
                    <p class="doc-dropzone__txt">Arrastra tu archivo aquí o <span>haz clic para seleccionar</span></p>
                    <p class="doc-dropzone__hint">PDF, JPG, PNG, WEBP — máximo 10 MB</p>
                </div>
                <div class="doc-dropzone__preview" id="filePreview" style="display:none;">
                    <i class="fas fa-file-check" style="color:#22c55e;font-size:1.8rem;"></i>
                    <div>
                        <p class="doc-dropzone__fname" id="fileName"></p>
                        <p class="doc-dropzone__fsize" id="fileSize"></p>
                    </div>
                    <button type="button" onclick="limpiarArchivo()" class="doc-dropzone__clear">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="doc-upload-desc">
                <label for="descripcion"><i class="fas fa-tag"></i> Descripción (opcional)</label>
                <input type="text" name="descripcion" id="descripcion"
                       placeholder="Ej: Radiografía de tórax, Examen de sangre…"
                       maxlength="120">
            </div>
        </div>

        <div class="doc-upload-footer">
            <button type="submit" class="doc-btn-upload" id="btnSubir" disabled>
                <i class="fas fa-upload"></i> Subir documento
            </button>
        </div>
    </form>
</div>

{{-- ═══ ARCHIVOS SUBIDOS ═══ --}}
<div class="doc-section">
    <h3 class="doc-section__title">
        <i class="fas fa-folder-open"></i> Documentos subidos
        <span class="doc-badge">{{ count($archivos) }}</span>
    </h3>

    @if(empty($archivos))
    <div class="doc-empty">
        <i class="fas fa-file-circle-plus"></i>
        <p>Aún no has subido ningún documento.</p>
        <p style="font-size:.82rem;color:#9ca3af;">Usa el formulario de arriba para subir tus resultados.</p>
    </div>
    @else
    <div class="doc-grid">
        @foreach($archivos as $arch)
        @php
            $iconos = [
                'pdf'  => ['fas fa-file-pdf',   '#ef4444'],
                'jpg'  => ['fas fa-file-image',  '#0ea5e9'],
                'jpeg' => ['fas fa-file-image',  '#0ea5e9'],
                'png'  => ['fas fa-file-image',  '#0ea5e9'],
                'webp' => ['fas fa-file-image',  '#0ea5e9'],
            ];
            [$icono, $color] = $iconos[$arch['extension']] ?? ['fas fa-file','#6b7280'];
            $kb = round($arch['tamanio'] / 1024);
            $mb = $kb > 1024 ? round($kb / 1024, 1) . ' MB' : $kb . ' KB';
            $fecha = \Carbon\Carbon::parse($arch['fecha'])->isoFormat('D MMM YYYY · HH:mm');
        @endphp
        <div class="doc-card">
            <div class="doc-card__icon" style="color:{{ $color }};">
                <i class="{{ $icono }}"></i>
            </div>
            <div class="doc-card__body">
                <p class="doc-card__name">{{ $arch['descripcion'] }}</p>
                <p class="doc-card__meta">{{ strtoupper($arch['extension']) }} · {{ $mb }}</p>
                <p class="doc-card__date"><i class="fas fa-clock"></i> {{ $fecha }}</p>
            </div>
            <div class="doc-card__actions">
                <a href="{{ route('paciente.examenes.descargar', $arch['id']) }}"
                   class="doc-card__btn doc-card__btn--ver" title="Descargar">
                    <i class="fas fa-download"></i>
                </a>
                <form method="POST" action="{{ route('paciente.examenes.eliminar', $arch['id']) }}"
                      onsubmit="return confirm('¿Eliminar este archivo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="doc-card__btn doc-card__btn--del" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ═══ EXÁMENES DEL MÉDICO (BD) ═══ --}}
@if(!$examenesBd->isEmpty())
<div class="doc-section">
    <h3 class="doc-section__title">
        <i class="fas fa-stethoscope"></i> Exámenes registrados por tu médico
        <span class="doc-badge">{{ $examenesBd->count() }}</span>
    </h3>
    <div class="paciente-table-wrap">
        <table class="paciente-table">
            <thead>
                <tr>
                    <th>Tipo de Examen</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examenesBd as $e)
                <tr>
                    <td><i class="fas fa-file-medical" style="color:#0078d7;margin-right:6px;"></i>{{ $e->tipo_examen }}</td>
                    <td>{{ \Carbon\Carbon::parse($e->fecha)->format('d/m/Y') }}</td>
                    <td>
                        @if($e->resultado)
                            <span class="badge-virtual"><i class="fas fa-check-circle"></i> Disponible</span>
                        @else
                            <span style="color:#aaa;font-size:.82rem;">Pendiente</span>
                        @endif
                    </td>
                    <td>
                        @if($e->resultado)
                            <a href="{{ $e->resultado }}" class="btn-ver" target="_blank">
                                <i class="fas fa-download"></i> Ver
                            </a>
                        @endif
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
.doc-alert { padding:12px 16px; border-radius:10px; font-size:.88rem;
             display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.doc-alert--ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
.doc-alert--err { background:#fff5f5; border:1px solid #fecaca; color:#dc2626; }

/* ── Upload card ── */
.doc-upload-card { background:#fff; border-radius:16px; box-shadow:0 1px 6px rgba(0,0,0,.07);
                   border:1px solid #e5e7eb; margin-bottom:24px; overflow:hidden; }
.doc-upload-card__header { background:linear-gradient(135deg,#0078d7,#005fa3);
                            color:#fff; padding:14px 20px; display:flex;
                            align-items:center; gap:10px; font-weight:600; font-size:.95rem; }

/* ── Dropzone ── */
.doc-upload-body { padding:20px 20px 0; display:flex; flex-direction:column; gap:14px; }
.doc-dropzone { border:2px dashed #d1d5db; border-radius:12px; position:relative;
                cursor:pointer; transition:border-color .2s, background .2s; overflow:hidden; }
.doc-dropzone:hover, .doc-dropzone.over { border-color:#0078d7; background:#eff6ff; }
.doc-dropzone__input { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; }
.doc-dropzone__content { padding:32px 20px; display:flex; flex-direction:column;
                         align-items:center; gap:8px; pointer-events:none; }
.doc-dropzone__icon { font-size:2.5rem; color:#0078d7; }
.doc-dropzone__txt  { font-size:.9rem; color:#374151; text-align:center; margin:0; }
.doc-dropzone__txt span { color:#0078d7; font-weight:600; }
.doc-dropzone__hint { font-size:.78rem; color:#9ca3af; margin:0; }
.doc-dropzone__preview { padding:16px 20px; display:flex; align-items:center; gap:14px; }
.doc-dropzone__fname { font-weight:600; font-size:.9rem; color:#1a2e44; margin:0 0 2px; }
.doc-dropzone__fsize { font-size:.78rem; color:#6b7280; margin:0; }
.doc-dropzone__clear { background:none; border:none; color:#9ca3af; cursor:pointer;
                       font-size:1rem; margin-left:auto; padding:4px 8px; }
.doc-dropzone__clear:hover { color:#ef4444; }

.doc-upload-desc label { font-size:.85rem; font-weight:600; color:#374151;
                          display:flex; align-items:center; gap:6px; margin-bottom:6px; }
.doc-upload-desc label i { color:#0078d7; }
.doc-upload-desc input  { width:100%; padding:10px 14px; border:1px solid #e5e7eb;
                           border-radius:8px; font-size:.9rem; font-family:inherit;
                           transition:border-color .2s; }
.doc-upload-desc input:focus { outline:none; border-color:#0078d7;
                                box-shadow:0 0 0 3px rgba(0,120,215,.1); }

.doc-upload-footer { padding:16px 20px; display:flex; justify-content:flex-end; }
.doc-btn-upload { display:flex; align-items:center; gap:8px; padding:10px 24px;
                  background:linear-gradient(135deg,#0078d7,#005fa3); color:#fff;
                  border:none; border-radius:8px; font-weight:600; cursor:pointer;
                  transition:all .2s; font-size:.9rem; }
.doc-btn-upload:disabled { opacity:.45; cursor:not-allowed; }
.doc-btn-upload:not(:disabled):hover { transform:translateY(-1px);
                                        box-shadow:0 4px 14px rgba(0,120,215,.35); }

/* ── Sección ── */
.doc-section { background:#fff; border-radius:16px; box-shadow:0 1px 6px rgba(0,0,0,.07);
               border:1px solid #e5e7eb; padding:20px; margin-bottom:24px; }
.doc-section__title { font-size:1rem; font-weight:700; color:#1a2e44;
                       display:flex; align-items:center; gap:8px; margin:0 0 16px; }
.doc-section__title i { color:#0078d7; }
.doc-badge { background:#eff6ff; color:#0078d7; border-radius:20px;
             padding:2px 10px; font-size:.78rem; font-weight:700; }

/* ── Empty ── */
.doc-empty { text-align:center; padding:40px 20px; color:#9ca3af; }
.doc-empty i { font-size:2.5rem; display:block; margin-bottom:12px; color:#d1d5db; }
.doc-empty p { margin:4px 0; font-size:.9rem; }

/* ── Grid de tarjetas ── */
.doc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:12px; }
.doc-card  { border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px;
             display:flex; align-items:center; gap:14px; transition:box-shadow .2s; }
.doc-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
.doc-card__icon { font-size:2rem; flex-shrink:0; }
.doc-card__body { flex:1; min-width:0; }
.doc-card__name { font-weight:600; font-size:.88rem; color:#1a2e44; margin:0 0 2px;
                  white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.doc-card__meta { font-size:.75rem; color:#6b7280; margin:0 0 2px; }
.doc-card__date { font-size:.72rem; color:#9ca3af; margin:0; }
.doc-card__date i { margin-right:3px; }
.doc-card__actions { display:flex; flex-direction:column; gap:6px; flex-shrink:0; }
.doc-card__btn { width:32px; height:32px; border-radius:8px; display:flex;
                 align-items:center; justify-content:center; border:none; cursor:pointer;
                 font-size:.8rem; text-decoration:none; transition:all .2s; }
.doc-card__btn--ver { background:#eff6ff; color:#0078d7; }
.doc-card__btn--ver:hover { background:#0078d7; color:#fff; }
.doc-card__btn--del { background:#fff5f5; color:#ef4444; }
.doc-card__btn--del:hover { background:#ef4444; color:#fff; }

@media(max-width:600px) {
    .doc-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@push('scripts')
<script>
const input    = document.getElementById('archivoInput');
const dropzone = document.getElementById('dropzone');
const content  = document.getElementById('dropContent');
const preview  = document.getElementById('filePreview');
const nameEl   = document.getElementById('fileName');
const sizeEl   = document.getElementById('fileSize');
const btnSubir = document.getElementById('btnSubir');

function mostrarArchivo(file) {
    nameEl.textContent = file.name;
    const kb = Math.round(file.size / 1024);
    sizeEl.textContent = kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb + ' KB';
    content.style.display  = 'none';
    preview.style.display  = 'flex';
    btnSubir.disabled = false;
}

function limpiarArchivo() {
    input.value        = '';
    content.style.display  = 'flex';
    preview.style.display  = 'none';
    btnSubir.disabled = true;
}

input.addEventListener('change', () => { if (input.files[0]) mostrarArchivo(input.files[0]); });

dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('over'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        mostrarArchivo(file);
    }
});

document.getElementById('formSubir').addEventListener('submit', function () {
    btnSubir.disabled = true;
    btnSubir.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
});
</script>
@endpush
