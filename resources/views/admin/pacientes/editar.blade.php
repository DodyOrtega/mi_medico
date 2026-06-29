@extends('layouts.admin')

@section('titulo', 'Editar Paciente')
@section('seccion_titulo', 'Editar Paciente')
@section('volver', route('admin.pacientes.index'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-edit"></i> Editar Paciente</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.pacientes.update', $paciente->cedula) }}" class="form-card" id="formGuardar">
        @csrf
        @method('PUT')

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula</label>
                <input type="text" value="{{ $paciente->cedula }}" disabled>
            </div>
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" name="nombres" required value="{{ old('nombres', $paciente->nombres) }}">
            </div>
            <div class="form-group">
                <label>Apellido 1 *</label>
                <input type="text" name="apellido1" required value="{{ old('apellido1', $paciente->apellido1) }}">
            </div>
            <div class="form-group">
                <label>Apellido 2</label>
                <input type="text" name="apellido2" value="{{ old('apellido2', $paciente->apellido2) }}">
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="correo" required value="{{ old('correo', $paciente->correo) }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" maxlength="10" value="{{ old('telefono', $paciente->telefono) }}">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="activo" {{ $paciente->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ $paciente->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-notes-medical"></i> Información Clínica</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Tipo de sangre</label>
                <select name="tipo_sangre">
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','Desconocido'] as $tipo)
                    <option value="{{ $tipo }}" {{ $paciente->tipo_sangre === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Barrio</label>
                <input type="text" name="barrio" value="{{ old('barrio', $paciente->barrio) }}">
            </div>
            <div class="form-group form-group--full">
                <label>Dirección</label>
                <input type="text" name="direccion_1" value="{{ old('direccion_1', $paciente->direccion_1) }}">
            </div>
            <div class="form-group form-group--full">
                <label>Alergias</label>
                <textarea name="alergias" rows="2">{{ old('alergias', $paciente->alergias) }}</textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Antecedentes</label>
                <textarea name="antecedentes" rows="2">{{ old('antecedentes', $paciente->antecedentes) }}</textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Medicación habitual</label>
                <textarea name="medicacion_habitual" rows="2">{{ old('medicacion_habitual', $paciente->medicacion_habitual) }}</textarea>
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-phone-volume"></i> Contacto de Emergencia Principal</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="contacto_nombre" value="{{ old('contacto_nombre', $contacto->nombre ?? '') }}">
            </div>
            <div class="form-group">
                <label>Parentesco</label>
                <input type="text" name="contacto_parentesco" value="{{ old('contacto_parentesco', $contacto->parentesco ?? '') }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="contacto_telefono" value="{{ old('contacto_telefono', $contacto->telefono ?? '') }}">
            </div>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('admin.pacientes.show', $paciente->cedula) }}" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn-success">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
@endpush

@push('scripts')
<script>
document.getElementById('formGuardar').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Guardar cambios?',
        text: 'Se actualizarán los datos del paciente.',
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