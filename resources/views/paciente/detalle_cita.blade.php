@extends('layouts.paciente')

@section('titulo', 'Detalle de Cita')
@section('seccion_titulo', 'Detalle de Cita')
@section('volver', route('paciente.citas'))

@section('contenido')

    <div class="page-header">
        <div>
            <h2 class="page-header__title"><i class="fas fa-file-medical"></i> Cita #{{ $cita->id_cita }}</h2>
            <p class="page-header__sub">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('paciente.citas') }}" class="btn-ver"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="detalle-grid">
        <div class="detalle-col">

            <div class="detalle-card">
                <div class="detalle-card__header"><i class="fas fa-user-md"></i> Médico</div>
                <div class="detalle-card__body">
                    <div class="cita-medico" style="margin-bottom:14px;">
                        <div class="cita-medico__avatar" style="width:48px;height:48px;font-size:1rem;">
                            {{ strtoupper(substr($cita->medico_nombres,0,1).substr($cita->medico_apellido1,0,1)) }}
                        </div>
                        <div>
                            <div class="cita-medico__nombre" style="font-size:1rem;">Dr. {{ $cita->medico_nombres }} {{ $cita->medico_apellido1 }}</div>
                            @if($cita->especialidad)
                                <div class="cita-medico__esp"><i class="fas fa-stethoscope"></i> {{ $cita->especialidad }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="info-block">
                        <span class="info-label">Tipo</span>
                        <span class="{{ $cita->tipo_cita === 'virtual' ? 'badge-virtual' : 'badge-presencial' }}">
                            <i class="fas fa-{{ $cita->tipo_cita === 'virtual' ? 'video' : 'hospital' }}"></i>
                            {{ $cita->tipo_cita === 'virtual' ? 'Teleconsulta' : 'Presencial' }}
                        </span>
                    </div>
                    @if($cita->motivo_consulta)
                    <div class="info-block">
                        <span class="info-label">Motivo</span>
                        <div class="info-text">{{ $cita->motivo_consulta }}</div>
                    </div>
                    @endif
                </div>
            </div>

            @if($cita->diagnostico || $cita->receta || $cita->examen_laboratorio || $cita->examen_imagenes)
            <div class="detalle-card">
                <div class="detalle-card__header"><i class="fas fa-notes-medical"></i> Diagnóstico y Tratamiento</div>
                <div class="detalle-card__body">
                    @if($cita->diagnostico)
                    <div class="info-block">
                        <span class="info-label">Diagnóstico</span>
                        <div class="info-text">{{ $cita->diagnostico }}</div>
                    </div>
                    @endif
                    @if($cita->receta)
                    <div class="info-block">
                        <span class="info-label">Receta</span>
                        <div class="info-text info-text--receta">{{ $cita->receta }}</div>
                    </div>
                    @endif
                    @if($cita->examen_laboratorio)
                    <div class="info-block">
                        <span class="info-label">Laboratorio</span>
                        <div class="info-text">{{ $cita->examen_laboratorio }}</div>
                    </div>
                    @endif
                    @if($cita->examen_imagenes)
                    <div class="info-block">
                        <span class="info-label">Imágenes</span>
                        <div class="info-text">{{ $cita->examen_imagenes }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        <div class="detalle-col">

            @if($cita->presion || $cita->peso || $cita->temperatura || $cita->frecuencia_cardiaca)
            <div class="detalle-card">
                <div class="detalle-card__header"><i class="fas fa-heartbeat"></i> Signos Vitales</div>
                <div class="detalle-card__body">
                    <div class="signos-grid">
                        @if($cita->presion)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-tint"></i></div>
                            <div class="signo-item__val">{{ $cita->presion }}</div>
                            <div class="signo-item__label">Presión (mmHg)</div>
                        </div>
                        @endif
                        @if($cita->frecuencia_cardiaca)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-heartbeat"></i></div>
                            <div class="signo-item__val">{{ $cita->frecuencia_cardiaca }}</div>
                            <div class="signo-item__label">Frec. Cardiaca (lpm)</div>
                        </div>
                        @endif
                        @if($cita->temperatura)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-thermometer-half"></i></div>
                            <div class="signo-item__val">{{ $cita->temperatura }}°C</div>
                            <div class="signo-item__label">Temperatura</div>
                        </div>
                        @endif
                        @if($cita->peso)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-weight"></i></div>
                            <div class="signo-item__val">{{ $cita->peso }} kg</div>
                            <div class="signo-item__label">Peso</div>
                        </div>
                        @endif
                        @if($cita->altura)
                        <div class="signo-item">
                            <div class="signo-item__icon"><i class="fas fa-ruler-vertical"></i></div>
                            <div class="signo-item__val">{{ $cita->altura }} m</div>
                            <div class="signo-item__label">Altura</div>
                        </div>
                        @endif
                        @if($imc)
                        <div class="signo-item" style="background:#e8f4fb;">
                            <div class="signo-item__icon"><i class="fas fa-calculator"></i></div>
                            <div class="signo-item__val" style="color:#0078d7;">{{ $imc }}</div>
                            <div class="signo-item__label">IMC — {{ $clasifImc }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="detalle-card">
                <div class="detalle-card__header"><i class="fas fa-cogs"></i> Acciones</div>
                <div class="detalle-card__body acciones-col">
                    @if($cita->tipo_cita === 'virtual')
                        <a href="{{ route('paciente.teleconsultas') }}" class="btn-accion btn-accion--video">
                            <i class="fas fa-video"></i> Unirse a Videollamada
                        </a>
                    @endif
                    @if(\Carbon\Carbon::parse($cita->fecha_hora) >= now())
                        <button class="btn-accion btn-accion--cancelar" onclick="confirmarCancelacion({{ $cita->id_cita }})">
                            <i class="fas fa-times-circle"></i> Cancelar Cita
                        </button>
                    @endif
                    <a href="{{ route('paciente.citas') }}" class="btn-accion btn-accion--back">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script>
function confirmarCancelacion(id) {
    Swal.fire({
        title: '¿Cancelar esta cita?', text: 'No se puede deshacer.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('{{ route("paciente.citas.cancelar") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_cita: id, is_agenda: false })
        }).then(r => r.json()).then(data => {
            Swal.fire(data.success ? 'Cancelada' : 'Error', data.message, data.success ? 'success' : 'error')
                .then(() => { if (data.success) window.location.href = '{{ route("paciente.citas") }}'; });
        });
    });
}
</script>
@endpush