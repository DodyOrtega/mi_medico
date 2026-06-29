@extends('layouts.medico')
@section('titulo', 'Nueva Cita')
@section('seccion_titulo', 'Registrar Consulta')
@section('volver', route('medico.citas.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-stethoscope"></i> Registrar Consulta</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    @if($pacienteSeleccionado)
    <div class="paciente-resumen">
        <div class="paciente-resumen__avatar"><i class="fas fa-user"></i></div>
        <div>
            <strong>{{ $pacienteSeleccionado->nombres }} {{ $pacienteSeleccionado->apellido1 }}</strong>
            <span>Cédula: {{ $pacienteSeleccionado->cedula }} · Sangre: {{ $pacienteSeleccionado->tipo_sangre }}</span>
            @if($pacienteSeleccionado->alergias)
            <span class="alerta-alergia"><i class="fas fa-triangle-exclamation"></i> Alergias: {{ $pacienteSeleccionado->alergias }}</span>
            @endif
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('medico.citas.guardar') }}" class="form-card" id="formGuardar">
        @csrf
        @if($slotAgenda)
        <input type="hidden" name="id_agenda" value="{{ $slotAgenda->id_agenda }}">
        @endif

        <h3 class="form-card__seccion"><i class="fas fa-calendar"></i> Datos de la Cita</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Paciente *</label>
                <select name="cedula_paciente" required>
                    <option value="">Seleccione...</option>
                    @foreach($pacientes as $pac)
                    <option value="{{ $pac->cedula }}"
                        {{ ($pacienteSeleccionado && $pacienteSeleccionado->cedula === $pac->cedula) ? 'selected' : '' }}>
                        {{ $pac->nombres }} {{ $pac->apellido1 }} ({{ $pac->cedula }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de cita *</label>
                <select name="tipo_cita" required>
                    <option value="presencial">Presencial</option>
                    <option value="virtual">Virtual</option>
                </select>
            </div>
            <div class="form-group">
                <label>Fecha y hora *</label>
                <input type="datetime-local" name="fecha_hora" required
                       value="{{ $slotAgenda ? $slotAgenda->fecha . 'T' . substr($slotAgenda->hora, 0, 5) : now()->format('Y-m-d\TH:i') }}">
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-heart-pulse"></i> Signos Vitales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Presión arterial</label>
                <input type="text" name="presion" placeholder="Ej: 120/80" value="{{ old('presion') }}">
            </div>
            <div class="form-group">
                <label>Frecuencia cardíaca (lpm)</label>
                <input type="number" name="frecuencia_cardiaca" placeholder="Ej: 72" value="{{ old('frecuencia_cardiaca') }}">
            </div>
            <div class="form-group">
                <label>Temperatura (°C)</label>
                <input type="number" step="0.1" name="temperatura" placeholder="Ej: 36.5" value="{{ old('temperatura') }}">
            </div>
            <div class="form-group">
                <label>Peso (kg)</label>
                <input type="number" step="0.1" name="peso" placeholder="Ej: 70" value="{{ old('peso') }}">
            </div>
            <div class="form-group">
                <label>Altura (cm)</label>
                <input type="number" step="0.1" name="altura" placeholder="Ej: 170" value="{{ old('altura') }}">
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-notes-medical"></i> Consulta</h3>
        <div class="form-group form-group--full">
            <label>Motivo de consulta *</label>
            <textarea name="motivo_consulta" rows="2" required placeholder="Describe el motivo...">{{ old('motivo_consulta') }}</textarea>
        </div>
        <div class="form-group form-group--full">
            <label>Diagnóstico</label>
            <textarea name="diagnostico" rows="3" placeholder="Diagnóstico médico...">{{ old('diagnostico') }}</textarea>
        </div>
        <div class="form-group form-group--full">
            <label>Receta / Tratamiento</label>
            <textarea name="receta" rows="4" placeholder="Medicamentos, dosis, indicaciones...">{{ old('receta') }}</textarea>
        </div>
        <div class="form-group form-group--full">
            <label>Exámenes de laboratorio</label>
            <textarea name="examen_laboratorio" rows="2" placeholder="Exámenes solicitados...">{{ old('examen_laboratorio') }}</textarea>
        </div>
        <div class="form-group form-group--full">
            <label>Exámenes de imágenes</label>
            <textarea name="examen_imagenes" rows="2" placeholder="Radiografías, ecografías...">{{ old('examen_imagenes') }}</textarea>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('medico.citas.index') }}" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn-success">
                <i class="fas fa-save"></i> Guardar Consulta
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/citas.css') }}">
@endpush

@push('scripts')
<script>
document.getElementById('formGuardar').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Guardar consulta?',
        text: 'Se registrará la consulta en el historial del paciente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});
</script>
@endpush