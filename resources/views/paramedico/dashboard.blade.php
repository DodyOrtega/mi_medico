@extends('layouts.paramedico')
@section('titulo', 'Panel Paramédico')
@section('seccion_titulo', 'Panel Paramédico')

@section('contenido')

<section class="medico-welcome">
    <h1 class="medico-welcome__title">Bienvenido, {{ session('usuario_nombre') }}</h1>
    <p class="medico-welcome__sub">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
</section>

<div class="medico-stats">
    <div class="medico-stat medico-stat--blue">
        <i class="fas fa-calendar-day"></i>
        <div><span class="medico-stat__num">{{ $stats['atenciones_hoy'] }}</span><span class="medico-stat__label">Atenciones hoy</span></div>
    </div>
    <div class="medico-stat medico-stat--green">
        <i class="fas fa-calendar-alt"></i>
        <div><span class="medico-stat__num">{{ $stats['atenciones_mes'] }}</span><span class="medico-stat__label">Este mes</span></div>
    </div>
    <div class="medico-stat medico-stat--purple">
        <i class="fas fa-users"></i>
        <div><span class="medico-stat__num">{{ $stats['total_pacientes'] }}</span><span class="medico-stat__label">Pacientes</span></div>
    </div>
    <div class="medico-stat medico-stat--orange">
        <i class="fas fa-triangle-exclamation"></i>
        <div><span class="medico-stat__num">{{ $stats['emergencias_hoy'] }}</span><span class="medico-stat__label">Emergencias hoy</span></div>
    </div>
</div>

@if($citasHoy->isNotEmpty())
<section class="medico-section">
    <h2 class="medico-section__title"><i class="fas fa-list-check"></i> Atenciones de hoy</h2>
    <div class="citas-hoy-lista">
        @foreach($citasHoy as $cita)
        <div class="cita-item">
            <div class="cita-item__hora">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}</div>
            <div class="cita-item__info">
                <span class="cita-item__paciente">{{ $cita->nombres }} {{ $cita->apellido1 }}</span>
                <span class="cita-item__tipo">{{ $cita->motivo_consulta ? Str::limit($cita->motivo_consulta, 40) : ucfirst($cita->tipo_cita) }}</span>
            </div>
            <span class="cita-item__badge cita-item__badge--{{ $cita->tipo_cita }}">{{ ucfirst($cita->tipo_cita) }}</span>
        </div>
        @endforeach
    </div>
</section>
@endif

<section class="medico-section">
    <h2 class="medico-section__title"><i class="fas fa-th-large"></i> Módulos</h2>
    <div class="medico-modulos">
        @foreach($modulos as $m)
        <a href="{{ $m['ruta'] }}" class="medico-modulo medico-modulo--{{ $m['color'] }}">
            <i class="fas fa-{{ $m['icono'] }}"></i>
            <h3>{{ $m['titulo'] }}</h3>
            <p>{{ $m['descripcion'] }}</p>
        </a>
        @endforeach
    </div>
</section>

@endsection

@push('scripts')
@if($showWelcome)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ title: '¡Bienvenido!', text: 'Sesión iniciada, {{ session("usuario_nombre") }}', icon: 'success', confirmButtonColor: '#0078d7', timer: 4000, timerProgressBar: true });
});
</script>
@endif
@endpush