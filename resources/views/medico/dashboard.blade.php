@extends('layouts.medico')

@section('titulo', 'Mi Panel – Médico')
@section('seccion_titulo', 'Panel del Médico')

@section('contenido')

{{-- BIENVENIDA --}}
<section class="medico-welcome">
    <h1 class="medico-welcome__title">
        Bienvenido, Dr. {{ session('usuario_nombre') }}
    </h1>
    <p class="medico-welcome__sub">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</section>

{{-- STATS --}}
<div class="medico-stats">
    <div class="medico-stat medico-stat--blue">
        <i class="fas fa-calendar-day"></i>
        <div>
            <span class="medico-stat__num" id="statCitasHoy">{{ $stats['citas_hoy'] }}</span>
            <span class="medico-stat__label">Citas hoy</span>
        </div>
    </div>
    <div class="medico-stat medico-stat--green">
        <i class="fas fa-calendar-alt"></i>
        <div>
            <span class="medico-stat__num" id="statCitasMes">{{ $stats['citas_mes'] }}</span>
            <span class="medico-stat__label">Citas este mes</span>
        </div>
    </div>
    <div class="medico-stat medico-stat--purple">
        <i class="fas fa-users"></i>
        <div>
            <span class="medico-stat__num" id="statPacientes">{{ $stats['total_pacientes'] }}</span>
            <span class="medico-stat__label">Pacientes atendidos</span>
        </div>
    </div>
    <div class="medico-stat medico-stat--orange">
        <i class="fas fa-clock"></i>
        <div>
            <span class="medico-stat__num" id="statPendientes">{{ $stats['pendientes'] }}</span>
            <span class="medico-stat__label">Citas pendientes</span>
        </div>
    </div>
</div>

{{-- CITAS DE HOY --}}
<section class="medico-section">
    <h2 class="medico-section__title">
        <i class="fas fa-list-check"></i> Citas de hoy
    </h2>
    <div id="citasHoyContenido">
        @if($citasHoy->isEmpty())
        <div class="medico-empty">
            <i class="fas fa-calendar-xmark"></i>
            <p>No tienes citas programadas para hoy.</p>
        </div>
        @else
        <div class="citas-hoy-lista">
            @foreach($citasHoy as $cita)
            @php $horaIso = \Carbon\Carbon::parse($cita->fecha_hora)->format('Y-m-d\TH:i:s'); @endphp
            <div class="cita-item">
                <div class="cita-item__hora">
                    {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}
                </div>
                <div class="cita-item__info">
                    <span class="cita-item__paciente">
                        {{ $cita->nombres }} {{ $cita->apellido1 }}
                    </span>
                    <span class="cita-item__tipo">
                        <i class="fas fa-{{ $cita->tipo_cita === 'virtual' ? 'video' : 'hospital' }}"></i>
                        {{ ucfirst($cita->tipo_cita) }}
                        @if($cita->motivo_consulta)
                            — {{ Str::limit($cita->motivo_consulta, 40) }}
                        @endif
                    </span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <span class="cita-item__badge cita-item__badge--{{ $cita->tipo_cita }}">
                        {{ ucfirst($cita->tipo_cita) }}
                    </span>
                    @if($cita->tipo_cita === 'virtual')
                    <a href="{{ route('medico.video.index') }}"
                       class="btn-video-dash"
                       data-hora-cita="{{ $horaIso }}">
                        <i class="fas fa-video"></i>
                        <span class="btn-video-dash__txt">Videollamada</span>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- MÓDULOS --}}
<section class="medico-section">
    <h2 class="medico-section__title">
        <i class="fas fa-th-large"></i> Módulos
    </h2>
    <div class="medico-modulos">
        @foreach($modulos as $modulo)
        <a href="{{ $modulo['ruta'] }}" class="medico-modulo medico-modulo--{{ $modulo['color'] }}">
            <i class="fas fa-{{ $modulo['icono'] }}"></i>
            <h3>{{ $modulo['titulo'] }}</h3>
            <p>{{ $modulo['descripcion'] }}</p>
        </a>
        @endforeach
    </div>
</section>

@endsection

@push('styles')
<style>
.btn-video-dash {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; font-size: .8rem;
    font-weight: 700; text-decoration: none; transition: all .2s;
    white-space: nowrap;
}
.btn-video-dash--activo {
    background: #7c3aed; color: #fff;
}
.btn-video-dash--activo:hover {
    background: #6d28d9; transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(124,58,237,.35);
}
.btn-video-dash--inactivo {
    background: #f3f4f6; color: #9ca3af; pointer-events: none;
}
</style>
@endpush

@push('scripts')
@if($showWelcome)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: '¡Bienvenido!',
        text: 'Sesión iniciada correctamente, Dr. {{ session("usuario_nombre") }}',
        icon: 'success',
        confirmButtonColor: '#0078d7',
        timer: 4000,
        timerProgressBar: true,
    });
});
</script>
@endif

<script>
const URL_VIDEO = "{{ route('medico.video.index') }}";

function verificarBotonesVideo() {
    const ahora = Date.now();
    document.querySelectorAll('.btn-video-dash').forEach(btn => {
        const horaCita = new Date(btn.dataset.horaCita).getTime();
        const activo   = ahora >= horaCita;
        btn.classList.toggle('btn-video-dash--activo',   activo);
        btn.classList.toggle('btn-video-dash--inactivo', !activo);
        if (activo) {
            btn.removeAttribute('tabindex');
            btn.style.pointerEvents = '';
            btn.querySelector('.btn-video-dash__txt').textContent = 'Videollamada';
        } else {
            btn.style.pointerEvents = 'none';
            const minutos = Math.ceil((horaCita - ahora) / 60000);
            const etiqueta = minutos < 60
                ? `En ${minutos} min`
                : `En ${Math.ceil(minutos / 60)}h`;
            btn.querySelector('.btn-video-dash__txt').textContent = etiqueta;
        }
    });
}

function renderCitasHoy(citas) {
    const contenedor = document.getElementById('citasHoyContenido');
    if (!citas.length) {
        contenedor.innerHTML = `
            <div class="medico-empty">
                <i class="fas fa-calendar-xmark"></i>
                <p>No tienes citas programadas para hoy.</p>
            </div>`;
        return;
    }
    contenedor.innerHTML = '<div class="citas-hoy-lista">' +
        citas.map(c => {
            const hora    = c.fecha_hora.substring(11, 16);
            const horaIso = c.fecha_hora.replace(' ', 'T');
            const icono   = c.tipo_cita === 'virtual' ? 'video' : 'hospital';
            const tipo    = c.tipo_cita.charAt(0).toUpperCase() + c.tipo_cita.slice(1);
            const motivo  = c.motivo_consulta
                ? ' — ' + c.motivo_consulta.substring(0, 40) + (c.motivo_consulta.length > 40 ? '...' : '')
                : '';
            const btnVideo = c.tipo_cita === 'virtual'
                ? `<a href="${URL_VIDEO}" class="btn-video-dash" data-hora-cita="${horaIso}">
                       <i class="fas fa-video"></i>
                       <span class="btn-video-dash__txt">Videollamada</span>
                   </a>`
                : '';
            return `<div class="cita-item">
                <div class="cita-item__hora">${hora}</div>
                <div class="cita-item__info">
                    <span class="cita-item__paciente">${c.nombres} ${c.apellido1}</span>
                    <span class="cita-item__tipo"><i class="fas fa-${icono}"></i> ${tipo}${motivo}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <span class="cita-item__badge cita-item__badge--${c.tipo_cita}">${tipo}</span>
                    ${btnVideo}
                </div>
            </div>`;
        }).join('') +
    '</div>';
    verificarBotonesVideo();
}

function actualizarDashboard() {
    fetch('{{ route("medico.dashboard.data") }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('statCitasHoy').textContent   = data.stats.citas_hoy;
            document.getElementById('statCitasMes').textContent   = data.stats.citas_mes;
            document.getElementById('statPacientes').textContent  = data.stats.total_pacientes;
            document.getElementById('statPendientes').textContent = data.stats.pendientes;
            renderCitasHoy(data.citasHoy);
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', function () {
    verificarBotonesVideo();
    setInterval(verificarBotonesVideo, 30 * 1000);
    setInterval(actualizarDashboard,  15 * 60 * 1000);
});
</script>
@endpush
