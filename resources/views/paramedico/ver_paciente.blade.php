@extends('layouts.paramedico')
@section('titulo','Detalle del Paciente')
@section('seccion_titulo','Detalle del Paciente')
@section('volver', route('paramedico.pacientes.index'))

@section('contenido')
@php
    $nombre = trim("{$paciente->nombres} {$paciente->apellido1} " . ($paciente->apellido2 ?? ''));
    $activo = ($paciente->estado ?? 'activo') === 'activo';
@endphp

<div class="medicos-page">
    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user"></i> {{ $nombre }}</h1>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('paramedico.atencion.index', ['cedula_buscar' => $paciente->cedula]) }}" class="btn-primary">
                <i class="fas fa-stethoscope"></i> Atender
            </a>
            <a href="{{ route('paramedico.citas.agendar', ['paciente' => $paciente->cedula]) }}" class="btn-success">
                <i class="fas fa-calendar-plus"></i> Agendar Cita
            </a>
        </div>
    </div>

    <div class="detalle-card">
        <div class="detalle-card__header">
            <div class="detalle-card__avatar"><i class="fas fa-user"></i></div>
            <div>
                <h2 class="detalle-card__nombre">{{ $nombre }}</h2>
                <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}" style="position:static;display:inline-flex;">
                    <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
                </span>
            </div>
        </div>

        <div class="detalle-grid">
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-id-card"></i> Cédula</span>
                <span class="detalle-item__value">{{ $paciente->cedula }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-envelope"></i> Correo</span>
                <span class="detalle-item__value">{{ $paciente->correo }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="detalle-item__value">{{ $paciente->telefono ?: 'No registrado' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-cake-candles"></i> Edad</span>
                <span class="detalle-item__value">{{ $edad !== null ? "{$edad} años" : 'No registrada' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-tint"></i> Tipo de sangre</span>
                <span class="detalle-item__value">{{ $paciente->tipo_sangre ?? 'Desconocido' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-map-marker-alt"></i> Ubicación</span>
                <span class="detalle-item__value">
                    {{ implode(', ', array_filter([$paciente->nombre_parroquia, $paciente->nombre_canton, $paciente->nombre_provincia])) ?: 'No registrada' }}
                </span>
            </div>
        </div>

        <h3 class="detalle-card__subtitulo"><i class="fas fa-notes-medical"></i> Datos Clínicos</h3>
        <div class="detalle-grid">
            <div class="detalle-item">
                <span class="detalle-item__label">Alergias</span>
                <span class="detalle-item__value">{{ $paciente->alergias ?: 'Ninguna' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Antecedentes</span>
                <span class="detalle-item__value">{{ $paciente->antecedentes ?: 'Ninguno' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Medicación habitual</span>
                <span class="detalle-item__value">{{ $paciente->medicacion_habitual ?: 'Ninguna' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Discapacidad</span>
                <span class="detalle-item__value">
                    @if($paciente->discapacidad)
                        Sí{{ $paciente->tipo_discapacidad ? ' — ' . $paciente->tipo_discapacidad : '' }}
                    @else
                        No
                    @endif
                </span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Gestante</span>
                <span class="detalle-item__value">
                    {{ $paciente->gestante ? 'Sí' . ($paciente->semanas_gestacion ? ' (' . $paciente->semanas_gestacion . ' sem.)' : '') : 'No' }}
                </span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Hábitos</span>
                <span class="detalle-item__value">
                    {{ collect(['Fumador' => $paciente->fumador, 'Alcohol' => $paciente->consume_alcohol])->filter()->keys()->implode(', ') ?: 'Ninguno' }}
                </span>
            </div>
        </div>

        <h3 class="detalle-card__subtitulo"><i class="fas fa-phone-volume"></i> Contacto de Emergencia</h3>
        @if($contactos->isEmpty())
            <p class="detalle-empty">Sin contactos registrados.</p>
        @else
            <div class="detalle-tags">
                @foreach($contactos as $c)
                <span class="detalle-tag">{{ $c->nombre }} ({{ $c->parentesco }}) — {{ $c->telefono }}</span>
                @endforeach
            </div>
        @endif

        <h3 class="detalle-card__subtitulo"><i class="fas fa-clock-rotate-left"></i> Historial Completo de Atenciones</h3>
        @if($historial->isEmpty())
            <p class="detalle-empty">Sin atenciones registradas.</p>
        @else
        <div class="historial-lista">
            @foreach($historial as $h)
            @php
                $esPropio     = $h->cedula_medico === $paramedicoCedula;
                $esParamedico = ($h->tipo_profesional ?? '') === 'Paramedico';
                $profNombre   = trim("{$h->prof_nombres} {$h->prof_apellido1}");
                $prefijo      = $esParamedico ? 'Lic.' : 'Dr.';
            @endphp
            <div class="historial-item {{ $esPropio ? 'historial-item--propio' : ($esParamedico ? 'historial-item--paramedico' : 'historial-item--otro') }}">
                <div class="historial-item__header">
                    <span class="historial-item__fecha">
                        <i class="fas fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($h->fecha_hora)->format('d/m/Y H:i') }}
                    </span>
                    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                        <span class="historial-item__tipo">{{ ucfirst($h->tipo_cita ?? 'presencial') }}</span>
                        @if($esPropio)
                            <span class="historial-badge historial-badge--propio">
                                <i class="fas fa-kit-medical"></i> Tu atención
                            </span>
                        @elseif($esParamedico)
                            <span class="historial-badge historial-badge--paramedico">
                                <i class="fas fa-kit-medical"></i> Lic. {{ $profNombre }}
                            </span>
                        @else
                            <span class="historial-badge historial-badge--otro">
                                <i class="fas fa-stethoscope"></i> Dr. {{ $profNombre }}
                            </span>
                        @endif
                    </div>
                </div>
                @if($h->motivo_consulta)
                    <p class="historial-item__campo"><strong>Motivo:</strong> {{ $h->motivo_consulta }}</p>
                @endif
                @if($h->diagnostico)
                    <p class="historial-item__campo"><strong>Diagnóstico:</strong> {{ $h->diagnostico }}</p>
                @endif
                @if($h->receta)
                    <p class="historial-item__campo"><strong>Receta:</strong> {{ $h->receta }}</p>
                @endif
                @if($h->presion || $h->temperatura || $h->peso)
                <div class="historial-item__signos">
                    @if($h->presion)<span><i class="fas fa-heart-pulse"></i> {{ $h->presion }}</span>@endif
                    @if($h->frecuencia_cardiaca)<span><i class="fas fa-heartbeat"></i> {{ $h->frecuencia_cardiaca }} lpm</span>@endif
                    @if($h->temperatura)<span><i class="fas fa-temperature-half"></i> {{ $h->temperatura }}°C</span>@endif
                    @if($h->peso)<span><i class="fas fa-weight-scale"></i> {{ $h->peso }} kg</span>@endif
                    @if($h->altura)<span><i class="fas fa-ruler-vertical"></i> {{ number_format($h->altura * 100) }} cm</span>@endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
@endpush