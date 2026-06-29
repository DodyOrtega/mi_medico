@extends('layouts.medico')
@section('titulo', 'Mis Pacientes')
@section('seccion_titulo', 'Mis Pacientes')
@section('volver', route('medico.dashboard'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-users"></i> Mis Pacientes</h1>
            <p class="page-header__sub">{{ $pacientes->count() }} paciente(s) atendidos</p>
        </div>
        <a href="{{ route('medico.pacientes.create') }}" class="btn-add-big">
            <i class="fas fa-user-plus"></i> Nuevo Paciente
        </a>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <div class="toolbar">
        <form method="GET" action="{{ route('medico.pacientes.index') }}" class="search-form">
            <input type="text" name="busqueda" class="search-input"
                   placeholder="Buscar por nombre o cédula..." value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Buscar</button>
            @if($busqueda)
            <a href="{{ route('medico.pacientes.index') }}" class="btn-clear">
                <i class="fas fa-times"></i> Limpiar
            </a>
            @endif
        </form>
    </div>

    @if($pacientes->isEmpty())
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>{{ $busqueda ? "No se encontraron resultados para \"{$busqueda}\"" : 'Aún no tienes pacientes registrados.' }}</p>
    </div>
    @else
    <div class="medicos-grid">
        @foreach($pacientes as $pac)
        @php
            $nombre = trim("{$pac->nombres} {$pac->apellido1} " . ($pac->apellido2 ?? ''));
            $activo = ($pac->estado ?? 'activo') === 'activo';
        @endphp
        <div class="medico-card {{ $activo ? 'medico-card--activo' : 'medico-card--inactivo' }}">
            <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i>
                {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
            </span>

            <div class="medico-card__icon"><i class="fas fa-user"></i></div>
            <h3 class="medico-card__nombre">{{ $nombre }}</h3>
            <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $pac->cedula }}</p>
            <p class="medico-card__cedula"><i class="fas fa-tint"></i> {{ $pac->tipo_sangre ?? 'Desconocido' }}</p>

            @if($pac->citas_pendientes > 0)
            <span class="paciente-card__citas">
                <i class="fas fa-calendar-check"></i> {{ $pac->citas_pendientes }} cita(s) pendiente(s)
            </span>
            @endif

            @if($pac->ultima_cita)
            <p class="medico-card__experiencia">
                <i class="fas fa-clock"></i> Última: {{ \Carbon\Carbon::parse($pac->ultima_cita)->format('d/m/Y') }}
            </p>
            @endif

            <div class="medico-card__actions">
                <a href="{{ route('medico.pacientes.show', $pac->cedula) }}" class="action-btn action-btn--view">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('medico.citas.agendar', ['paciente' => $pac->cedula]) }}" class="action-btn action-btn--add">
                    <i class="fas fa-calendar-plus"></i> Agendar
                </a>
                <a href="{{ route('medico.citas.nueva', ['paciente' => $pac->cedula]) }}" class="action-btn action-btn--add">
                    <i class="fas fa-plus"></i> Cita
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
@endpush