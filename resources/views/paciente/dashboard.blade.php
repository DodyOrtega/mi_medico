@extends('layouts.paciente')

@section('titulo', 'Mi Panel')
@section('seccion_titulo', 'Panel del Paciente')

@section('contenido')

    {{-- BIENVENIDA --}}
    <div class="paciente-welcome">
        <h2 class="paciente-welcome__title">Bienvenido, {{ $nombreCompleto }}</h2>
        <p class="paciente-welcome__sub">Tu salud en un solo lugar — Centro Médico Integral</p>
        <div class="paciente-welcome__badges">
            <span class="paciente-badge"><i class="fas fa-user"></i> Paciente</span>
            <span class="paciente-badge"><i class="fas fa-calendar-day"></i> {{ now()->format('d/m/Y') }}</span>
            <span class="paciente-badge"><i class="fas fa-clock"></i> {{ now()->format('H:i') }}</span>
            <span class="paciente-badge"><i class="fas fa-id-card"></i> {{ $persona->cedula }}</span>
            @if($paciente && $paciente->tipo_afiliacion)
                <span class="paciente-badge"><i class="fas fa-shield-alt"></i> {{ $paciente->tipo_afiliacion }}</span>
            @endif
        </div>
    </div>

    {{-- PRÓXIMA CITA --}}
    @if($proximaCita)
    <div class="paciente-proxima">
        <div>
            <h3 class="paciente-proxima__titulo">
                <i class="fas fa-calendar-check"></i> Próxima Cita
                <span class="paciente-proxima__badge">
                    <i class="fas fa-clock"></i>
                    {{ \Carbon\Carbon::parse($proximaCita->fecha_hora)->format('d/m/Y H:i') }}
                </span>
            </h3>
            <div class="paciente-proxima__detalle">
                <div class="paciente-proxima__item">
                    <i class="fas fa-user-md"></i>
                    Dr. {{ $proximaCita->medico_nombres }} {{ $proximaCita->medico_apellido1 }}
                    @if($proximaCita->especialidad) ({{ $proximaCita->especialidad }}) @endif
                </div>
                <div class="paciente-proxima__item">
                    <i class="fas fa-{{ $proximaCita->tipo_cita === 'virtual' ? 'video' : 'hospital' }}"></i>
                    {{ $proximaCita->tipo_cita === 'virtual' ? 'Teleconsulta' : 'Presencial' }}
                </div>
                @if($proximaCita->motivo_consulta)
                <div class="paciente-proxima__item">
                    <i class="fas fa-notes-medical"></i> {{ $proximaCita->motivo_consulta }}
                </div>
                @endif
            </div>
        </div>
        @if($proximaCita->tipo_cita === 'virtual')
            <a href="{{ route('paciente.teleconsultas') }}" class="btn-paciente-primary">
                <i class="fas fa-video"></i> Unirse a Videollamada
            </a>
        @else
            <a href="{{ route('paciente.citas.detalle', $proximaCita->id_cita) }}" class="btn-paciente-primary">
                <i class="fas fa-info-circle"></i> Ver Detalles
            </a>
        @endif
    </div>
    @else
    <div class="paciente-proxima paciente-proxima--vacia">
        <div>
            <p class="paciente-proxima__empty-title"><i class="fas fa-calendar-plus"></i> No tienes citas programadas</p>
            <p class="paciente-proxima__empty-sub">¿Necesitas atención médica? Agenda una cita ahora.</p>
            <a href="{{ route('paciente.citas.agendar') }}" class="btn-paciente-primary">
                <i class="fas fa-calendar-plus"></i> Agendar Cita
            </a>
        </div>
    </div>
    @endif

    {{-- MÓDULOS --}}
    <div class="paciente-section">
        <h3 class="paciente-section__title"><i class="fas fa-th-large"></i> Módulos</h3>
        <div class="paciente-modulos">
            <a href="{{ route('paciente.citas') }}" class="paciente-modulo paciente-modulo--blue">
                <i class="fas fa-calendar-alt"></i>
                <h3>Mis Citas</h3>
                <p>Consulta, reprograma o cancela tus citas médicas.</p>
            </a>
            <a href="{{ route('paciente.citas.agendar') }}" class="paciente-modulo paciente-modulo--green">
                <i class="fas fa-calendar-plus"></i>
                <h3>Agendar Cita</h3>
                <p>Reserva una consulta con el especialista que necesitas.</p>
            </a>
            <a href="{{ route('paciente.historial') }}" class="paciente-modulo paciente-modulo--teal">
                <i class="fas fa-chart-line"></i>
                <h3>Mi Historial</h3>
                <p>Diagnósticos previos, signos vitales y evolución de tu salud.</p>
            </a>
            <a href="{{ route('paciente.recetas') }}" class="paciente-modulo paciente-modulo--purple">
                <i class="fas fa-prescription-bottle"></i>
                <h3>Mis Recetas</h3>
                <p>Revisa todas tus recetas médicas y dosis recomendadas.</p>
            </a>
            <a href="{{ route('paciente.documentos') }}" class="paciente-modulo paciente-modulo--orange">
                <i class="fas fa-file-medical"></i>
                <h3>Mis Documentos</h3>
                <p>Exámenes médicos y resultados de laboratorio.</p>
            </a>
            <a href="{{ route('paciente.teleconsultas') }}" class="paciente-modulo paciente-modulo--red">
                <i class="fas fa-video"></i>
                <h3>Teleconsultas</h3>
                <p>Accede a tus videollamadas programadas con médicos.</p>
            </a>
        </div>
    </div>

    {{-- EXÁMENES RECIENTES --}}
    @if($examenes->isNotEmpty())
    <div class="paciente-section">
        <h3 class="paciente-section__title"><i class="fas fa-file-medical"></i> Exámenes Recientes</h3>
        <div class="paciente-table-wrap">
            <table class="paciente-table">
                <thead>
                    <tr>
                        <th>Tipo de Examen</th>
                        <th>Fecha</th>
                        <th>Resultado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examenes as $doc)
                    <tr>
                        <td>{{ $doc->tipo_examen }}</td>
                        <td>{{ \Carbon\Carbon::parse($doc->fecha)->format('d/m/Y') }}</td>
                        <td>
                            @if($doc->resultado)
                                <span class="badge-virtual"><i class="fas fa-file-pdf"></i> Disponible</span>
                            @else
                                <span style="color:#aaa;font-size:.82rem;">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if($doc->resultado)
                                <a href="{{ $doc->resultado }}" class="btn-ver" target="_blank">
                                    <i class="fas fa-download"></i> Ver
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- RECETAS RECIENTES --}}
    @if($recetas->isNotEmpty())
    <div class="paciente-section">
        <h3 class="paciente-section__title"><i class="fas fa-prescription"></i> Recetas Recientes</h3>
        <div class="paciente-table-wrap">
            <table class="paciente-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Médico</th>
                        <th>Diagnóstico</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recetas as $receta)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($receta->fecha_hora)->format('d/m/Y') }}</td>
                        <td>Dr. {{ $receta->medico_nombres }} {{ $receta->medico_apellido1 }}</td>
                        <td>{{ Str::limit($receta->diagnostico ?? '', 50) }}</td>
                        <td>
                            <a href="{{ route('paciente.recetas') }}" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
@if($showWelcome)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: '¡Bienvenido a Mi Médico!',
        html: `<div style="text-align:center;padding:10px;">
                 <i class="fas fa-user-circle" style="font-size:3.5rem;color:#0078d7;"></i>
                 <p style="font-size:1.1rem;font-weight:700;color:#1a2e44;margin:12px 0 4px;">{{ $nombreCompleto }}</p>
                 <p style="font-size:.85rem;color:#888;margin:0 0 14px;">Paciente</p>
                 <div style="background:#f0f7ff;border-radius:10px;padding:10px;font-size:.875rem;color:#0078d7;">
                   <i class="fas fa-calendar-check"></i>
                   {{ $proximaCita ? 'Tienes una cita próxima' : 'No tienes citas programadas' }}
                 </div>
               </div>`,
        icon: 'success',
        iconColor: '#0078d7',
        confirmButtonText: 'Ir al Panel',
        confirmButtonColor: '#0078d7',
        timer: 5000,
        timerProgressBar: true,
        allowOutsideClick: false,
        showClass: { popup: 'animate__animated animate__zoomIn' },
        hideClass: { popup: 'animate__animated animate__zoomOut' }
    });
});
</script>
@endif
@endpush