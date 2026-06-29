@extends('layouts.paramedico')
@section('titulo','Primera Atención')
@section('seccion_titulo','Primera Atención')
@section('volver', route('paramedico.pacientes.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-stethoscope"></i> Primera Atención</h1>
            @if(!$paciente)
                <p class="page-header__sub">Selecciona un paciente para registrar su atención</p>
            @endif
        </div>
        @if($paciente)
            <a href="{{ route('paramedico.pacientes.index') }}" class="btn-secondary" style="font-size:.85rem;">
                <i class="fas fa-arrow-left"></i> Volver a mis pacientes
            </a>
        @endif
    </div>

    @if(session('error'))
        <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    @if($paciente)

        {{-- ── Resumen del paciente ── --}}
        <div class="paciente-resumen" style="margin-bottom:20px;">
            <div class="paciente-resumen__avatar"><i class="fas fa-user"></i></div>
            <div>
                <strong>{{ $paciente->nombres }} {{ $paciente->apellido1 }}</strong>
                <span>Cédula: {{ $paciente->cedula }} · Sangre: {{ $paciente->tipo_sangre ?? 'Desconocido' }}</span>
                @if($paciente->alergias)
                    <span class="alerta-alergia"><i class="fas fa-triangle-exclamation"></i> Alergias: {{ $paciente->alergias }}</span>
                @endif
            </div>
        </div>

        {{-- ── Formulario de atención ── --}}
        <form method="POST" action="{{ route('paramedico.atencion.guardar') }}" class="form-card" id="formGuardar">
            @csrf
            <input type="hidden" name="cedula_paciente" value="{{ $paciente->cedula }}">

            <h3 class="form-card__seccion"><i class="fas fa-heart-pulse"></i> Signos Vitales</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Presión arterial</label>
                    <input type="text" name="presion" placeholder="120/80">
                </div>
                <div class="form-group">
                    <label>Frecuencia cardíaca (lpm)</label>
                    <input type="number" name="frecuencia_cardiaca" placeholder="72">
                </div>
                <div class="form-group">
                    <label>Temperatura (°C)</label>
                    <input type="number" step="0.1" name="temperatura" placeholder="36.5">
                </div>
                <div class="form-group">
                    <label>Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" placeholder="70">
                </div>
                <div class="form-group">
                    <label>Altura (cm)</label>
                    <input type="number" step="0.1" name="altura" placeholder="170">
                </div>
            </div>

            <h3 class="form-card__seccion"><i class="fas fa-notes-medical"></i> Atención</h3>
            <div class="form-group form-group--full">
                <label>Motivo *</label>
                <textarea name="motivo_consulta" rows="2" required placeholder="Motivo de la atención..."></textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Observaciones / Diagnóstico inicial</label>
                <textarea name="diagnostico" rows="3" placeholder="Observaciones del paramédico..."></textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Indicaciones / Tratamiento inicial</label>
                <textarea name="receta" rows="2" placeholder="Indicaciones inmediatas..."></textarea>
            </div>

            <div class="form-card__actions">
                <a href="{{ route('paramedico.pacientes.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <a href="{{ route('paramedico.citas.agendar', ['paciente' => $paciente->cedula]) }}" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Agendar con médico
                </a>
                <button type="submit" class="btn-success">
                    <i class="fas fa-save"></i> Registrar Atención
                </button>
            </div>
        </form>

    @else

        {{-- ── Lista de pacientes en cards ── --}}
        @if($pacientes->isEmpty())
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No tienes pacientes registrados aún.</p>
                <a href="{{ route('paramedico.pacientes.crear') }}" class="btn-primary" style="margin-top:12px;">
                    <i class="fas fa-user-plus"></i> Registrar paciente
                </a>
            </div>
        @else
            <div class="medicos-grid">
                @foreach($pacientes as $p)
                <div class="medico-card medico-card--activo">
                    <div class="medico-card__icon"><i class="fas fa-user"></i></div>
                    <h3 class="medico-card__nombre">{{ $p->nombres }} {{ $p->apellido1 }}</h3>
                    <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $p->cedula }}</p>
                    <p class="medico-card__cedula">
                        <i class="fas fa-tint"></i> {{ $p->tipo_sangre ?? 'Desconocido' }}
                    </p>
                    @if($p->alergias)
                        <p class="medico-card__cedula" style="color:#dc3545;font-size:.78rem;">
                            <i class="fas fa-triangle-exclamation"></i> {{ Str::limit($p->alergias, 28) }}
                        </p>
                    @endif
                    <div class="medico-card__actions">
                        <a href="{{ route('paramedico.atencion.index') }}?cedula={{ $p->cedula }}"
                           class="action-btn action-btn--edit">
                            <i class="fas fa-stethoscope"></i> Atender
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/citas.css') }}">
@endpush

@push('scripts')
<script>
const formAtencion = document.getElementById('formGuardar');
if (formAtencion) {
    formAtencion.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Registrar atención?',
            text: 'Se guardará la atención en el historial del paciente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
}
</script>
@endpush
