@extends('layouts.medico')

@section('titulo', 'Documentos del Paciente')

@section('contenido')

<div class="medico-welcome">
    <div>
        <h2 style="font-size:1.3rem;font-weight:700;color:#1a2e44;margin:0 0 4px;">
            <i class="fas fa-folder-open" style="color:#0078d7;margin-right:8px;"></i>
            Documentos de {{ $paciente->nombre_completo }}
        </h2>
        <p style="font-size:.88rem;color:#6b7280;margin:0;">
            Cédula: {{ $paciente->cedula }}
            @if($paciente->telefono) · <i class="fas fa-phone" style="color:#22c55e;"></i> {{ $paciente->telefono }} @endif
        </p>
    </div>
    <a href="javascript:history.back()" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

@if(empty($archivos))
<div class="ex-empty">
    <i class="fas fa-folder-open"></i>
    <p>Este paciente aún no ha subido ningún documento.</p>
    <p style="font-size:.82rem;color:#9ca3af;">Los documentos aparecerán aquí cuando el paciente los suba desde su portal.</p>
</div>
@else
<div class="ex-grid">
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
    <div class="ex-card">
        <div class="ex-card__icon" style="color:{{ $color }};">
            <i class="{{ $icono }}"></i>
        </div>
        <div class="ex-card__body">
            <p class="ex-card__name">{{ $arch['descripcion'] }}</p>
            <p class="ex-card__meta">{{ strtoupper($arch['extension']) }} · {{ $mb }}</p>
            <p class="ex-card__date"><i class="fas fa-clock"></i> {{ $fecha }}</p>
        </div>
        <a href="{{ route('medico.examenes.descargar', [$paciente->cedula, $arch['id']]) }}"
           class="ex-btn-dl" title="Descargar">
            <i class="fas fa-download"></i>
        </a>
    </div>
    @endforeach
</div>
@endif

@endsection

@push('styles')
<style>
.btn-volver { display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
              background:#f3f4f6; border-radius:8px; color:#374151; text-decoration:none;
              font-size:.85rem; font-weight:600; transition:background .2s; }
.btn-volver:hover { background:#e5e7eb; }

.ex-empty { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:60px 20px;
            text-align:center; color:#9ca3af; margin-top:20px; }
.ex-empty i { font-size:3rem; display:block; margin-bottom:14px; color:#d1d5db; }
.ex-empty p { margin:4px 0; font-size:.9rem; }

.ex-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
           gap:12px; margin-top:20px; }
.ex-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px;
           display:flex; align-items:center; gap:14px; transition:box-shadow .2s; }
.ex-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.08); }
.ex-card__icon { font-size:2.2rem; flex-shrink:0; }
.ex-card__body { flex:1; min-width:0; }
.ex-card__name { font-weight:600; font-size:.9rem; color:#1a2e44; margin:0 0 3px;
                 white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ex-card__meta { font-size:.75rem; color:#6b7280; margin:0 0 2px; }
.ex-card__date { font-size:.72rem; color:#9ca3af; margin:0; }
.ex-card__date i { margin-right:3px; }
.ex-btn-dl { width:36px; height:36px; border-radius:8px; background:#eff6ff; color:#0078d7;
             display:flex; align-items:center; justify-content:center; text-decoration:none;
             font-size:.85rem; transition:all .2s; flex-shrink:0; }
.ex-btn-dl:hover { background:#0078d7; color:#fff; }
</style>
@endpush
