@extends('layouts.medico')
@section('titulo', 'Historial Clínico')
@section('seccion_titulo', 'Historial Clínico')
@section('volver', route('medico.dashboard'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-clock-rotate-left"></i> Historial Clínico</h1>
            <p class="page-header__sub">{{ $historial->count() }} registro(s)</p>
        </div>
    </div>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('medico.historial.index') }}" class="reportes-filtro">
        <div class="form-group">
            <label>Desde</label>
            <input type="date" name="desde" value="{{ $desde }}">
        </div>
        <div class="form-group">
            <label>Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}">
        </div>
        <div class="form-group" style="flex:1; min-width:180px;">
            <label>Buscar</label>
            <input type="text" name="busqueda" class="search-input" placeholder="Paciente o diagnóstico..." value="{{ $busqueda }}">
        </div>
        <button type="submit" class="btn-search" style="align-self:flex-end;">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </form>

    @if($historial->isEmpty())
    <div class="empty-state">
        <i class="fas fa-file-medical"></i>
        <p>No hay registros en este período.</p>
    </div>
    @else
    <div class="historial-lista">
        @foreach($historial as $h)
        <div class="historial-item">

            {{-- Cabecera: fecha + tipo --}}
            <div class="historial-item__header">
                <span class="historial-item__fecha">
                    <i class="fas fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($h->fecha_hora)->format('d/m/Y H:i') }}
                </span>
                <span class="historial-item__tipo">{{ ucfirst($h->tipo_cita) }}</span>
            </div>

            {{-- Paciente --}}
            <p class="historial-item__profesional">
                <i class="fas fa-user"></i>
                <a href="{{ route('medico.pacientes.show', $h->cedula_paciente) }}" style="color:#0078d7;text-decoration:none;">
                    {{ $h->nombres }} {{ $h->apellido1 }}{{ $h->apellido2 ? ' '.$h->apellido2 : '' }}
                </a>
                <small style="color:#aaa;">· {{ $h->cedula_paciente }}</small>
            </p>

            {{-- Campos clínicos --}}
            @if($h->motivo_consulta)
            <p class="historial-item__campo"><strong>Motivo:</strong> {{ $h->motivo_consulta }}</p>
            @endif
            @if($h->diagnostico)
            <p class="historial-item__campo"><strong>Diagnóstico:</strong> {{ $h->diagnostico }}</p>
            @endif
            @if($h->receta)
            <p class="historial-item__campo"><strong>Receta:</strong> {{ $h->receta }}</p>
            @endif
            @if($h->examen_laboratorio)
            <p class="historial-item__campo"><strong>Laboratorio:</strong> {{ $h->examen_laboratorio }}</p>
            @endif
            @if($h->examen_imagenes)
            <p class="historial-item__campo"><strong>Imágenes:</strong> {{ $h->examen_imagenes }}</p>
            @endif

            {{-- Signos vitales --}}
            @if($h->presion || $h->frecuencia_cardiaca || $h->temperatura || $h->peso || $h->altura)
            <div class="historial-item__signos">
                @if($h->presion)             <span><i class="fas fa-heart-pulse"></i> {{ $h->presion }}</span>                   @endif
                @if($h->frecuencia_cardiaca) <span><i class="fas fa-heartbeat"></i> {{ $h->frecuencia_cardiaca }} lpm</span>     @endif
                @if($h->temperatura)         <span><i class="fas fa-temperature-half"></i> {{ $h->temperatura }}°C</span>        @endif
                @if($h->peso)                <span><i class="fas fa-weight-scale"></i> {{ $h->peso }} kg</span>                  @endif
                @if($h->altura)              <span><i class="fas fa-ruler-vertical"></i> {{ $h->altura }} m</span>               @endif
            </div>
            @endif

        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/citas.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/reportes.css') }}">
@endpush