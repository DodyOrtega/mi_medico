@extends('layouts.admin')

@section('titulo', 'Detalle del Paciente')
@section('seccion_titulo', 'Detalle del Paciente')
@section('volver', route('admin.pacientes.index'))

@section('contenido')

@php
    $nombreCompleto = trim("{$paciente->nombres} {$paciente->apellido1} " . ($paciente->apellido2 ?? ''));
    $activo = ($paciente->estado ?? 'activo') === 'activo';

    $ubicacion = array_filter([
        $paciente->direccion_1, $paciente->direccion_2, $paciente->barrio,
        $paciente->nombre_parroquia, $paciente->nombre_canton, $paciente->nombre_provincia,
    ]);
    $direccionCompleta = $ubicacion ? implode(', ', $ubicacion) : 'No registrada';
@endphp

<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user"></i> Detalle del Paciente</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('admin.pacientes.edit', $paciente->cedula) }}" class="btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form method="POST" action="{{ route('admin.pacientes.destroy', $paciente->cedula) }}" id="form-del-detalle">
                @csrf @method('DELETE')
                <button type="button"
                        class="btn-secondary"
                        style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"
                        onclick="
                            Swal.fire({
                                title:'¿Eliminar paciente?',
                                html:'Se eliminará permanentemente a <strong>{{ addslashes($nombreCompleto) }}</strong> con todas sus citas e historial.<br><br><strong style=\'color:#dc2626\'>Esta acción no se puede deshacer.</strong>',
                                icon:'warning',
                                showCancelButton:true,
                                confirmButtonText:'Sí, eliminar',
                                cancelButtonText:'Cancelar',
                                confirmButtonColor:'#dc2626',
                                focusCancel:true
                            }).then(r=>{ if(r.isConfirmed) document.getElementById('form-del-detalle').submit(); });
                        ">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="detalle-card">

        <div class="detalle-card__header">
            <div class="detalle-card__avatar"><i class="fas fa-user"></i></div>
            <div>
                <h2 class="detalle-card__nombre">{{ $nombreCompleto }}</h2>
                <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
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
                <span class="detalle-item__value">{{ $edad !== null ? "$edad años" : 'No registrada' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-tint"></i> Tipo de sangre</span>
                <span class="detalle-item__value">{{ $paciente->tipo_sangre ?: 'Desconocido' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-id-badge"></i> Afiliación</span>
                <span class="detalle-item__value">{{ $paciente->tipo_afiliacion ?: 'No registrada' }}</span>
            </div>
            <div class="detalle-item" style="grid-column: 1 / -1;">
                <span class="detalle-item__label"><i class="fas fa-location-dot"></i> Ubicación</span>
                <span class="detalle-item__value">{{ $direccionCompleta }}</span>
            </div>
        </div>

        {{-- ═══ MÉDICO ASIGNADO ═══ --}}
        <h3 class="detalle-card__subtitulo"><i class="fas fa-user-md"></i> Médico Asignado</h3>
        @if($medicoAsignado)
        @php
            $esFutura = \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->gt(now());
        @endphp
        <div style="display:flex;align-items:center;gap:14px;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;">
            <div style="width:42px;height:42px;border-radius:50%;background:#0078d7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user-md" style="color:#fff;font-size:1.1rem;"></i>
            </div>
            <div>
                <p style="margin:0;font-weight:700;color:#1a2e44;font-size:.95rem;">
                    Dr. {{ $medicoAsignado->nombres }} {{ $medicoAsignado->apellido1 }}
                </p>
                @if($medicoAsignado->especialidad)
                <p style="margin:2px 0 0;font-size:.8rem;color:#0078d7;">{{ $medicoAsignado->especialidad }}</p>
                @endif
                <p style="margin:4px 0 0;font-size:.75rem;color:#64748b;">
                    @if($esFutura)
                        <i class="fas fa-calendar-check" style="color:#16a34a;"></i>
                        Próxima cita: {{ \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->format('d/m/Y H:i') }}
                    @else
                        <i class="fas fa-clock" style="color:#f59e0b;"></i>
                        Última atención: {{ \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
        </div>
        @else
        <p class="detalle-empty">Sin médico asignado.</p>
        @endif

        {{-- ═══ INFORMACIÓN CLÍNICA ═══ --}}
        <h3 class="detalle-card__subtitulo"><i class="fas fa-notes-medical"></i> Información Clínica</h3>
        <div class="detalle-grid">
            <div class="detalle-item">
                <span class="detalle-item__label">Alergias</span>
                <span class="detalle-item__value">{{ $paciente->alergias ?: 'Ninguna registrada' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Antecedentes</span>
                <span class="detalle-item__value">{{ $paciente->antecedentes ?: 'Ninguno registrado' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label">Medicación habitual</span>
                <span class="detalle-item__value">{{ $paciente->medicacion_habitual ?: 'Ninguna' }}</span>
            </div>
            @if($paciente->gestante)
            <div class="detalle-item">
                <span class="detalle-item__label">Gestante</span>
                <span class="detalle-item__value">Sí — {{ $paciente->semanas_gestacion ?? '?' }} semanas</span>
            </div>
            @endif
            @if($paciente->discapacidad)
            <div class="detalle-item">
                <span class="detalle-item__label">Discapacidad</span>
                <span class="detalle-item__value">{{ $paciente->tipo_discapacidad ?: 'Sí, sin especificar' }}</span>
            </div>
            @endif
        </div>

        {{-- ═══ CONTACTOS DE EMERGENCIA ═══ --}}
        <h3 class="detalle-card__subtitulo"><i class="fas fa-phone-volume"></i> Contactos de Emergencia</h3>
        @if($contactos->isEmpty())
        <p class="detalle-empty">Sin contactos de emergencia registrados.</p>
        @else
        <div class="detalle-tags">
            @foreach($contactos as $contacto)
            <span class="detalle-tag">
                {{ $contacto->nombre }} ({{ $contacto->parentesco }}) — {{ $contacto->telefono }}
                @if($contacto->es_principal) <small>· Principal</small> @endif
            </span>
            @endforeach
        </div>
        @endif

        {{-- ═══ HISTORIAL CLÍNICO ═══ --}}
        <h3 class="detalle-card__subtitulo"><i class="fas fa-clock-rotate-left"></i> Historial de Atenciones</h3>
        @if($historial->isEmpty())
        <p class="detalle-empty">Sin atenciones registradas.</p>
        @else
        <div class="historial-lista">
            @foreach($historial as $atencion)
            <div class="historial-item">
                <div class="historial-item__header">
                    <span class="historial-item__fecha">
                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($atencion->fecha_hora)->format('d/m/Y H:i') }}
                    </span>
                    <span class="historial-item__tipo">
                        {{ $atencion->tipo_profesional === 'Paramedico' ? 'Atención inicial' : 'Consulta médica' }}
                        ({{ $atencion->tipo_cita }})
                    </span>
                </div>
                <p class="historial-item__profesional">
                    <i class="fas fa-user-md"></i> Dr. {{ $atencion->profesional_nombres }} {{ $atencion->profesional_apellido1 }}
                </p>
                @if($atencion->motivo_consulta)
                <p class="historial-item__campo"><strong>Motivo:</strong> {{ $atencion->motivo_consulta }}</p>
                @endif
                @if($atencion->diagnostico)
                <p class="historial-item__campo"><strong>Diagnóstico:</strong> {{ $atencion->diagnostico }}</p>
                @endif
                @if($atencion->receta)
                <p class="historial-item__campo"><strong>Receta:</strong> {{ $atencion->receta }}</p>
                @endif
                @if($atencion->presion || $atencion->frecuencia_cardiaca || $atencion->temperatura)
                <div class="historial-item__signos">
                    @if($atencion->presion) <span><i class="fas fa-heart-pulse"></i> {{ $atencion->presion }}</span> @endif
                    @if($atencion->frecuencia_cardiaca) <span><i class="fas fa-heartbeat"></i> {{ $atencion->frecuencia_cardiaca }} lpm</span> @endif
                    @if($atencion->temperatura) <span><i class="fas fa-temperature-half"></i> {{ $atencion->temperatura }}°C</span> @endif
                    @if($atencion->peso) <span><i class="fas fa-weight-scale"></i> {{ $atencion->peso }} kg</span> @endif
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