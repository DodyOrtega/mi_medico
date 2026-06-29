@extends('layouts.paciente')

@section('titulo', 'Teleconsultas')
@section('seccion_titulo', 'Teleconsultas')
@section('volver', route('paciente.dashboard'))

@section('contenido')


<div class="page-header">
    <div>
        <h2 class="page-header__title"><i class="fas fa-video"></i> Mis Teleconsultas</h2>
        <p class="page-header__sub">Historial de consultas virtuales</p>
    </div>
    <a href="{{ route('paciente.dashboard') }}" class="btn-ver"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="paciente-section">
    @if($teleconsultas->isEmpty())
        <div class="paciente-empty">
            <i class="fas fa-video" style="color:#dee2e6;"></i>
            <p>No tienes teleconsultas registradas.</p>
            <a href="{{ route('paciente.citas.agendar') }}" class="btn-paciente-primary">
                <i class="fas fa-calendar-plus"></i> Agendar Teleconsulta
            </a>
        </div>
    @else
        <div class="citas-grid">
            @foreach($teleconsultas as $t)
            <div class="cita-card">
                <div class="cita-card__header">
                    <span class="badge-virtual"><i class="fas fa-video"></i> Virtual</span>
                    <span class="cita-card__fecha">
                        <i class="fas fa-clock"></i>
                        {{ \Carbon\Carbon::parse($t->fecha_hora)->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="cita-card__body">
                    <div class="cita-medico">
                        <div class="cita-medico__avatar">
                            {{ strtoupper(substr($t->medico_nombres,0,1).substr($t->medico_apellido1,0,1)) }}
                        </div>
                        <div>
                            <div class="cita-medico__nombre">Dr. {{ $t->medico_nombres }} {{ $t->medico_apellido1 }}</div>
                            @if($t->especialidad)
                                <div class="cita-medico__esp"><i class="fas fa-stethoscope"></i> {{ $t->especialidad }}</div>
                            @endif
                        </div>
                    </div>
                    @if($t->motivo_consulta)
                        <div class="cita-motivo"><i class="fas fa-notes-medical"></i> {{ $t->motivo_consulta }}</div>
                    @endif
                </div>
                <div class="cita-card__footer">
                    <a href="{{ route('paciente.citas.detalle', $t->id_cita) }}" class="btn-ver">
                        <i class="fas fa-eye"></i> Ver Detalle
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

