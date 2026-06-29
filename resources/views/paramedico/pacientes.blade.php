@extends('layouts.paramedico')
@section('titulo','Mis Pacientes')
@section('seccion_titulo','Mis Pacientes')
@section('volver', route('paramedico.dashboard'))
@section('contenido')
<div class="medicos-page">
    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-users"></i> Mis Pacientes</h1>
            <p class="page-header__sub">{{ $pacientes->count() }} paciente(s)</p>
        </div>
        <a href="{{ route('paramedico.pacientes.crear') }}" class="btn-add-big">
            <i class="fas fa-user-plus"></i> Nuevo Paciente
        </a>
    </div>

    @if(session('exito'))
        <div class="alert alert--success">
            <i class="fas fa-check-circle"></i> {{ session('exito') }}
        </div>
    @endif

    <div class="toolbar">
        <form method="GET" action="{{ route('paramedico.pacientes.index') }}" class="search-form">
            <input type="text" name="busqueda" class="search-input" placeholder="Buscar por nombre o cédula..." value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
            @if($busqueda)
                <a href="{{ route('paramedico.pacientes.index') }}" class="btn-clear"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    @if($pacientes->isEmpty())
        <div class="empty-state"><i class="fas fa-users"></i><p>Sin pacientes atendidos aún.</p></div>
    @else
        <div class="medicos-grid">
            @foreach($pacientes as $pac)
                @php $activo = ($pac->estado ?? 'activo') === 'activo'; @endphp
                <div class="medico-card {{ $activo ? 'medico-card--activo' : 'medico-card--inactivo' }}">
                    <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                        <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                    <div class="medico-card__icon"><i class="fas fa-user"></i></div>
                    <h3 class="medico-card__nombre">{{ $pac->nombres }} {{ $pac->apellido1 }}</h3>
                    <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $pac->cedula }}</p>
                    <p class="medico-card__cedula"><i class="fas fa-tint"></i> {{ $pac->tipo_sangre ?? 'Desconocido' }}</p>
                    @if($pac->ultima_atencion)
                        <p class="medico-card__experiencia"><i class="fas fa-clock"></i> Última: {{ \Carbon\Carbon::parse($pac->ultima_atencion)->format('d/m/Y') }}</p>
                    @endif
                    <div class="medico-card__actions">
                        <a href="{{ route('paramedico.pacientes.ver', $pac->cedula) }}" class="action-btn action-btn--view">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="{{ route('paramedico.citas.agendar', ['paciente'=>$pac->cedula]) }}" class="action-btn action-btn--add">
                            <i class="fas fa-calendar-plus"></i> Cita
                        </a>
                        <a href="{{ route('paramedico.atencion.index') }}?cedula={{ $pac->cedula }}" class="action-btn action-btn--edit">
                            <i class="fas fa-stethoscope"></i> Atender
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
