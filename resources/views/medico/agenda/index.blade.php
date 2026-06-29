@extends('layouts.medico')
@section('titulo', 'Mi Agenda')
@section('seccion_titulo', 'Mi Agenda')
@section('volver', route('medico.dashboard'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title">
            <i class="fas fa-calendar-alt"></i>
            {{ ucfirst($mesNombre) }} {{ $anio }}
        </h1>
        <a href="{{ route('medico.agenda.gestionar') }}" class="btn-primary">
            <i class="fas fa-cog"></i> Gestionar Agenda
        </a>
    </div>

    {{-- Navegación mes --}}
    <div class="agenda-nav">
        <a href="{{ route('medico.agenda.index', ['mes' => $mesAnterior, 'anio' => $anioAnt]) }}" class="btn-secondary">
            <i class="fas fa-chevron-left"></i> Anterior
        </a>
        <span class="agenda-nav__titulo">{{ ucfirst($mesNombre) }} {{ $anio }}</span>
        <a href="{{ route('medico.agenda.index', ['mes' => $mesSiguiente, 'anio' => $anioSig]) }}" class="btn-secondary">
            Siguiente <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    {{-- Calendario --}}
    <div class="calendario-wrap">
        <div class="calendario-header">
            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
            <div class="calendario-header__dia">{{ $dia }}</div>
            @endforeach
        </div>

        <div class="calendario-grid">
            {{-- Espacios vacíos antes del primer día --}}
            @for($i = 0; $i < $iniciaSemana; $i++)
            <div class="calendario-dia calendario-dia--vacio"></div>
            @endfor

            {{-- Días del mes --}}
            @for($d = 1; $d <= $diasEnMes; $d++)
            @php
                $fechaStr    = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                $esHoy       = $fechaStr === $hoy;
                $citas       = $citasPorFecha[$fechaStr] ?? [];
                $disponibles = $disponiblesPorFecha[$fechaStr] ?? 0;
            @endphp
            <div class="calendario-dia {{ $esHoy ? 'calendario-dia--hoy' : '' }} {{ count($citas) > 0 ? 'calendario-dia--con-citas' : '' }}">
                <span class="calendario-dia__num">{{ $d }}</span>
                @foreach(array_slice($citas, 0, 2) as $cita)
                <div class="calendario-cita calendario-cita--{{ $cita->tipo_cita ?? 'presencial' }}">
                    {{ substr($cita->fecha_hora, 11, 5) }}
                    {{ $cita->nombres ? Str::limit($cita->nombres, 8) : '' }}
                </div>
                @endforeach
                @if(count($citas) > 2)
                <div class="calendario-mas">+{{ count($citas) - 2 }} más</div>
                @endif
                @if($disponibles > 0)
                <div class="calendario-disponibles">
                    {{ $disponibles }} libre{{ $disponibles != 1 ? 's' : '' }}
                </div>
                @endif
            </div>
            @endfor
        </div>
    </div>

    {{-- Próximas citas --}}
    @if($proximasCitas->isNotEmpty())
    <div class="medico-section">
        <h2 class="medico-section__title">
            <i class="fas fa-clock"></i> Próximas citas
        </h2>
        <div class="citas-hoy-lista">
            @foreach($proximasCitas as $cita)
            <div class="cita-item">
                <div class="cita-item__hora">
                    {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d/m') }}<br>
                    <small>{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}</small>
                </div>
                <div class="cita-item__info">
                    <span class="cita-item__paciente">{{ $cita->nombres }} {{ $cita->apellido1 }}</span>
                    <span class="cita-item__tipo">{{ ucfirst($cita->tipo_cita) }}</span>
                </div>
                <span class="cita-item__badge cita-item__badge--{{ $cita->tipo_cita }}">
                    {{ ucfirst($cita->tipo_cita) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/agenda.css') }}">
<style>
.calendario-disponibles {
    font-size: .62rem;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 4px;
    padding: 1px 5px;
    margin-top: 2px;
    text-align: center;
}
</style>
@endpush