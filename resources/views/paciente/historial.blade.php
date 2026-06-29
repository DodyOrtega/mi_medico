@extends('layouts.paciente')

@section('titulo', 'Mi Historial')
@section('seccion_titulo', 'Mi Historial')
@section('volver', route('paciente.dashboard'))

@section('contenido')

@php
    $generoTexto = match($pacienteInfo->genero ?? '') {
        'M'    => 'Masculino',
        'F'    => 'Femenino',
        'Otro' => 'Otro',
        default => '—'
    };
    $estadoCivilTexto = match($pacienteInfo->estado_civil ?? '') {
        'S' => 'Soltero/a',
        'C' => 'Casado/a',
        'V' => 'Viudo/a',
        'D' => 'Divorciado/a',
        default => '—'
    };
    $edad = $pacienteInfo->fecha_nac
        ? \Carbon\Carbon::parse($pacienteInfo->fecha_nac)->age . ' años'
        : '—';
@endphp

<div class="page-header">
    <div>
        <h2 class="page-header__title"><i class="fas fa-chart-line"></i> Mi Historial Médico</h2>
        <p class="page-header__sub">Consultas, diagnósticos y evolución de tu salud</p>
    </div>
    <a href="{{ route('paciente.dashboard') }}" class="btn-ver">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

{{-- ═══ INFORMACIÓN GENERAL ═══ --}}
<div class="paciente-section perfil-card">

    {{-- Avatar + nombre + chips rápidos --}}
    <div class="perfil-hero">
        <div class="perfil-avatar">
            {{ strtoupper(substr($pacienteInfo->nombres, 0, 1) . substr($pacienteInfo->apellido1, 0, 1)) }}
        </div>
        <div class="perfil-hero__info">
            <div class="perfil-hero__nombre">
                {{ trim($pacienteInfo->nombres . ' ' . $pacienteInfo->apellido1 . ' ' . ($pacienteInfo->apellido2 ?? '')) }}
            </div>
            <div class="perfil-hero__cedula"><i class="fas fa-id-badge"></i> {{ $pacienteInfo->cedula }}</div>
            <div class="perfil-chips">
                <span class="perfil-chip perfil-chip--blue"><i class="fas fa-birthday-cake"></i> {{ $edad }}</span>
                <span class="perfil-chip perfil-chip--teal"><i class="fas fa-{{ $pacienteInfo->genero === 'F' ? 'venus' : 'mars' }}"></i> {{ $generoTexto }}</span>
                <span class="perfil-chip perfil-chip--purple"><i class="fas fa-heart"></i> {{ $estadoCivilTexto }}</span>
                @if($pacienteInfo->tipo_sangre)
                <span class="perfil-chip perfil-chip--red"><i class="fas fa-droplet"></i> {{ $pacienteInfo->tipo_sangre }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Subsecciones --}}
    <div class="perfil-secciones">

        {{-- Personal --}}
        <div class="perfil-grupo">
            <div class="perfil-grupo__titulo"><i class="fas fa-user-circle"></i> Personal</div>
            <div class="perfil-fila-grid">
                <div class="perfil-fila">
                    <span class="perfil-fila__icono" style="background:#e8f4fb;color:#0078d7;"><i class="fas fa-globe"></i></span>
                    <div>
                        <div class="perfil-fila__label">Nacionalidad</div>
                        <div class="perfil-fila__val">{{ $pacienteInfo->nacionalidad ?? '—' }}</div>
                    </div>
                </div>
                @if($pacienteInfo->telefono)
                <div class="perfil-fila">
                    <span class="perfil-fila__icono" style="background:#e6f9f0;color:#00875a;"><i class="fas fa-phone"></i></span>
                    <div>
                        <div class="perfil-fila__label">Teléfono</div>
                        <div class="perfil-fila__val">{{ $pacienteInfo->telefono }}</div>
                    </div>
                </div>
                @endif
                @if($pacienteInfo->direccion_1 || $pacienteInfo->barrio)
                <div class="perfil-fila">
                    <span class="perfil-fila__icono" style="background:#fff4e6;color:#f59e0b;"><i class="fas fa-map-marker-alt"></i></span>
                    <div>
                        <div class="perfil-fila__label">Dirección</div>
                        <div class="perfil-fila__val">{{ implode(', ', array_filter([$pacienteInfo->direccion_1, $pacienteInfo->barrio])) }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Médico --}}
        <div class="perfil-grupo">
            <div class="perfil-grupo__titulo"><i class="fas fa-notes-medical"></i> Salud</div>
            <div class="perfil-fila-grid">
                <div class="perfil-fila">
                    <span class="perfil-fila__icono" style="background:#fdecea;color:#dc3545;"><i class="fas fa-shield-heart"></i></span>
                    <div>
                        <div class="perfil-fila__label">Afiliación</div>
                        <div class="perfil-fila__val">
                            {{ $pacienteInfo->tipo_afiliacion ?? '—' }}
                            @if($pacienteInfo->numero_afiliado)
                                <small class="hist-sub">· Nro: {{ $pacienteInfo->numero_afiliado }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                @if($pacienteInfo->medicacion_habitual)
                <div class="perfil-fila perfil-fila--full">
                    <span class="perfil-fila__icono" style="background:#f0ebff;color:#6f42c1;"><i class="fas fa-pills"></i></span>
                    <div>
                        <div class="perfil-fila__label">Medicación habitual</div>
                        <div class="perfil-fila__val">{{ $pacienteInfo->medicacion_habitual }}</div>
                    </div>
                </div>
                @endif
                @if($pacienteInfo->alergias)
                <div class="perfil-fila perfil-fila--full perfil-fila--alerta">
                    <span class="perfil-fila__icono" style="background:#fdecea;color:#dc3545;"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <div class="perfil-fila__label" style="color:#dc3545;">Alergias</div>
                        <div class="perfil-fila__val">{{ $pacienteInfo->alergias }}</div>
                    </div>
                </div>
                @endif
                @if($pacienteInfo->antecedentes)
                <div class="perfil-fila perfil-fila--full">
                    <span class="perfil-fila__icono" style="background:#e8f4fb;color:#0078d7;"><i class="fas fa-clipboard-list"></i></span>
                    <div>
                        <div class="perfil-fila__label">Antecedentes</div>
                        <div class="perfil-fila__val">{{ $pacienteInfo->antecedentes }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Badges de condiciones --}}
    @if($pacienteInfo->discapacidad || $pacienteInfo->gestante || $pacienteInfo->fumador || $pacienteInfo->consume_alcohol)
    <div class="hist-badges" style="margin-top:18px; padding-top:16px; border-top:1px solid #f0f4f8;">
        @if($pacienteInfo->discapacidad)
            <span class="badge-virtual"><i class="fas fa-wheelchair"></i> Discapacidad: {{ $pacienteInfo->tipo_discapacidad }}</span>
        @endif
        @if($pacienteInfo->gestante)
            <span class="badge-virtual"><i class="fas fa-baby"></i> Gestante — {{ $pacienteInfo->semanas_gestacion }} semanas</span>
        @endif
        @if($pacienteInfo->fumador)
            <span class="badge-presencial"><i class="fas fa-smoking"></i> Fumador</span>
        @endif
        @if($pacienteInfo->consume_alcohol)
            <span class="badge-presencial"><i class="fas fa-wine-glass"></i> Consume alcohol</span>
        @endif
    </div>
    @endif
</div>

{{-- ═══ CONTACTOS DE EMERGENCIA ═══ --}}
@if($contactos->isNotEmpty())
<div class="paciente-section">
    <h3 class="paciente-section__title"><i class="fas fa-phone-alt"></i> Contactos de Emergencia</h3>
    <div class="hist-contactos">
        @foreach($contactos as $c)
        <div class="hist-contacto-card">
            <div class="hist-contacto-card__avatar">
                {{ strtoupper(substr($c->nombre, 0, 1)) }}
            </div>
            <div class="hist-contacto-card__info">
                <div class="hist-contacto-card__nombre">{{ $c->nombre }}</div>
                @if($c->parentesco)
                    <div class="hist-contacto-card__parentesco"><i class="fas fa-users"></i> {{ $c->parentesco }}</div>
                @endif
                <div class="hist-contacto-card__tel"><i class="fas fa-phone"></i> {{ $c->telefono }}</div>
            </div>
            @if($c->es_principal)
                <span class="badge-virtual hist-contacto-card__principal"><i class="fas fa-star"></i> Principal</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ HISTORIAL DE CONSULTAS ═══ --}}
<div class="paciente-section">
    <h3 class="paciente-section__title">
        <i class="fas fa-history"></i> Consultas atendidas
        <span class="hist-count">{{ $historial->count() }}</span>
    </h3>

    @if($historial->isEmpty())
        <div class="paciente-empty">
            <i class="fas fa-clipboard-list" style="color:#dee2e6;"></i>
            <p>No tienes consultas registradas aún.</p>
        </div>
    @else
        <div class="hist-consultas">
            @foreach($historial as $h)
            <div class="hist-consulta-card">

                {{-- Cabecera --}}
                <div class="cita-card__header">
                    <div class="cita-medico" style="margin:0;">
                        <div class="cita-medico__avatar">
                            {{ strtoupper(substr($h->medico_nombres,0,1) . substr($h->medico_apellido1,0,1)) }}
                        </div>
                        <div>
                            <div class="cita-medico__nombre">Dr. {{ $h->medico_nombres }} {{ $h->medico_apellido1 }}</div>
                            @if($h->especialidad)
                                <div class="cita-medico__esp"><i class="fas fa-stethoscope"></i> {{ $h->especialidad }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="hist-consulta-card__meta">
                        <span class="{{ $h->tipo_cita === 'virtual' ? 'badge-virtual' : 'badge-presencial' }}">
                            <i class="fas fa-{{ $h->tipo_cita === 'virtual' ? 'video' : 'hospital' }}"></i>
                            {{ $h->tipo_cita === 'virtual' ? 'Teleconsulta' : 'Presencial' }}
                        </span>
                        <span class="cita-card__fecha">
                            <i class="fas fa-clock"></i>
                            {{ \Carbon\Carbon::parse($h->fecha_hora)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>

                {{-- Cuerpo --}}
                <div class="cita-card__body">

                    {{-- Campos clínicos --}}
                    <div class="hist-campos-grid">
                        @if($h->motivo_consulta)
                        <div class="info-block">
                            <span class="info-label">Motivo</span>
                            <div class="info-text">{{ $h->motivo_consulta }}</div>
                        </div>
                        @endif
                        @if($h->diagnostico)
                        <div class="info-block">
                            <span class="info-label">Diagnóstico</span>
                            <div class="info-text">{{ $h->diagnostico }}</div>
                        </div>
                        @endif
                        @if($h->receta)
                        <div class="info-block hist-full">
                            <span class="info-label">Receta / Tratamiento</span>
                            <div class="info-text info-text--receta">{{ $h->receta }}</div>
                        </div>
                        @endif
                        @if($h->examen_laboratorio)
                        <div class="info-block">
                            <span class="info-label">Laboratorio solicitado</span>
                            <div class="info-text">{{ $h->examen_laboratorio }}</div>
                        </div>
                        @endif
                        @if($h->examen_imagenes)
                        <div class="info-block">
                            <span class="info-label">Imágenes solicitadas</span>
                            <div class="info-text">{{ $h->examen_imagenes }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Signos vitales --}}
                    @if($h->presion || $h->frecuencia_cardiaca || $h->temperatura || $h->peso || $h->altura)
                    <div class="hist-signos-titulo">
                        <i class="fas fa-heart-pulse"></i> Signos vitales
                    </div>
                    <div class="signos-grid">
                        @if($h->presion)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-heart-pulse"></i></div>
                            <div class="signo-item__val">{{ $h->presion }}</div>
                            <div class="signo-item__label">Presión<br>arterial</div>
                        </div>
                        @endif
                        @if($h->frecuencia_cardiaca)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-heartbeat"></i></div>
                            <div class="signo-item__val">{{ $h->frecuencia_cardiaca }}</div>
                            <div class="signo-item__label">Frec.<br>cardíaca (lpm)</div>
                        </div>
                        @endif
                        @if($h->temperatura)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-temperature-half"></i></div>
                            <div class="signo-item__val">{{ $h->temperatura }}°C</div>
                            <div class="signo-item__label">Temperatura</div>
                        </div>
                        @endif
                        @if($h->peso)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-weight-scale"></i></div>
                            <div class="signo-item__val">{{ $h->peso }} kg</div>
                            <div class="signo-item__label">Peso</div>
                        </div>
                        @endif
                        @if($h->altura)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-ruler-vertical"></i></div>
                            <div class="signo-item__val">{{ $h->altura }} m</div>
                            <div class="signo-item__label">Altura</div>
                        </div>
                        @endif
                    </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="cita-card__footer">
                    <a href="{{ route('paciente.citas.detalle', $h->id_cita) }}" class="btn-ver">
                        <i class="fas fa-eye"></i> Ver detalle completo
                    </a>
                </div>

            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
/* ── Info text alergia ── */
.info-text--alergia { border-left: 3px solid #dc3545; }
.hist-sub { color: #888; font-size: .78rem; margin-left: 4px; }

/* ── Perfil hero ── */
.perfil-card { overflow: hidden; }

.perfil-hero {
    display: flex; align-items: center; gap: 24px;
    background: linear-gradient(135deg, #0078d7, #005fa3);
    margin: -24px -28px 24px;
    padding: 28px 32px;
    border-radius: 16px 16px 0 0;
}
.perfil-avatar {
    width: 72px; height: 72px; flex-shrink: 0;
    background: rgba(255,255,255,.25);
    border: 3px solid rgba(255,255,255,.5);
    border-radius: 50%;
    color: #fff; font-size: 1.6rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.perfil-hero__info { flex: 1; }
.perfil-hero__nombre { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
.perfil-hero__cedula { font-size: .82rem; color: rgba(255,255,255,.75); display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
.perfil-chips { display: flex; flex-wrap: wrap; gap: 7px; }
.perfil-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px;
    font-size: .76rem; font-weight: 600;
    background: rgba(255,255,255,.18); color: #fff;
}
.perfil-chip i { font-size: .8rem; }

/* ── Subsecciones ── */
.perfil-secciones { display: flex; flex-direction: column; gap: 20px; }
.perfil-grupo__titulo {
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; font-weight: 700; color: #0078d7;
    text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: 12px;
}
.perfil-grupo__titulo i { font-size: .9rem; }

.perfil-fila-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
}
.perfil-fila {
    display: flex; align-items: flex-start; gap: 12px;
    background: #f8fafc; border-radius: 10px; padding: 12px 14px;
    border: 1px solid #f0f4f8;
    transition: border-color .15s;
}
.perfil-fila:hover { border-color: #dbeafe; }
.perfil-fila--full { grid-column: 1 / -1; }
.perfil-fila--alerta { border-left: 3px solid #dc3545; background: #fff8f8; }
.perfil-fila--alerta:hover { border-color: #dc3545; border-left-color: #dc3545; }

.perfil-fila__icono {
    width: 36px; height: 36px; flex-shrink: 0; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
}
.perfil-fila__label {
    font-size: .7rem; font-weight: 700; color: #aaa;
    text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px;
}
.perfil-fila__val { font-size: .875rem; color: #374151; font-weight: 500; }

/* ── Badges condiciones ── */
.hist-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }

/* ── Contactos ── */
.hist-contactos { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
.hist-contacto-card {
    display: flex; align-items: center; gap: 14px;
    background: #f8fafc; border-radius: 12px; padding: 14px 16px;
    border: 1px solid #e5e7eb;
}
.hist-contacto-card__avatar {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #0078d7, #005fa3);
    color: #fff; font-weight: 700; font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
}
.hist-contacto-card__info { flex: 1; min-width: 0; }
.hist-contacto-card__nombre { font-weight: 700; color: #1a2e44; font-size: .9rem; }
.hist-contacto-card__parentesco { font-size: .78rem; color: #888; display: flex; align-items: center; gap: 4px; margin-top: 2px; }
.hist-contacto-card__tel { font-size: .82rem; color: #0078d7; display: flex; align-items: center; gap: 5px; margin-top: 4px; font-weight: 600; }
.hist-contacto-card__principal { align-self: flex-start; }

/* ── Contador en título ── */
.hist-count {
    background: #e8f4fb; color: #0078d7; font-size: .75rem;
    font-weight: 700; padding: 2px 10px; border-radius: 10px;
    margin-left: 6px;
}

/* ── Consultas ── */
.hist-consultas { display: flex; flex-direction: column; gap: 16px; }
.hist-consulta-card {
    border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden;
    transition: box-shadow .2s;
    border-top: 3px solid #0078d7;
}
.hist-consulta-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.09); }

.hist-consulta-card__meta {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}

/* ── Campos clínicos ── */
.hist-campos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

/* ── Título signos vitales ── */
.hist-signos-titulo {
    font-size: .78rem; font-weight: 700; color: #0078d7;
    text-transform: uppercase; letter-spacing: .05em;
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 10px;
    padding-top: 4px;
    border-top: 1px solid #f0f4f8;
}

@media (max-width: 640px) {
    .perfil-hero { flex-direction: column; align-items: flex-start; gap: 14px; padding: 20px; }
    .perfil-fila-grid { grid-template-columns: 1fr; }
    .hist-campos-grid { grid-template-columns: 1fr; }
    .hist-contactos { grid-template-columns: 1fr; }
}
</style>
@endpush
