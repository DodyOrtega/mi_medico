@extends('layouts.admin')

@section('titulo', 'Editar Médico')
@section('seccion_titulo', 'Editar Médico')
@section('volver', route('admin.medicos.index'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-edit"></i> Editar Médico</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.medicos.update', $medico->cedula) }}" class="form-card" id="formGuardar">
        @csrf
        @method('PUT')
        <input type="hidden" name="especialidad_id" value="{{ request('especialidad_id') }}">

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula</label>
                <input type="text" value="{{ $medico->cedula }}" disabled>
            </div>
            <div class="form-group">
                <label>Tipo de identificación *</label>
                <select name="tipo_cedula" required>
                    <option value="cedula" {{ $medico->tipo_cedula === 'cedula' ? 'selected' : '' }}>Cédula</option>
                    <option value="pasaporte" {{ $medico->tipo_cedula === 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" name="nombres" required value="{{ old('nombres', $medico->nombres) }}">
            </div>
            <div class="form-group">
                <label>Apellido 1 *</label>
                <input type="text" name="apellido1" required value="{{ old('apellido1', $medico->apellido1) }}">
            </div>
            <div class="form-group">
                <label>Apellido 2</label>
                <input type="text" name="apellido2" value="{{ old('apellido2', $medico->apellido2) }}">
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="correo" required value="{{ old('correo', $medico->correo) }}">
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="contrasena" placeholder="Dejar vacío para mantener la actual">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" maxlength="10" value="{{ old('telefono', $medico->telefono) }}">
            </div>
            <div class="form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nac" value="{{ old('fecha_nac', $medico->fecha_nac) }}">
            </div>
            <div class="form-group">
                <label>Estado civil</label>
                <select name="estado_civil">
                    @foreach(['S' => 'Soltero/a', 'C' => 'Casado/a', 'V' => 'Viudo/a', 'D' => 'Divorciado/a'] as $val => $label)
                    <option value="{{ $val }}" {{ $medico->estado_civil === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Género</label>
                <select name="genero">
                    @foreach(['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $val => $label)
                    <option value="{{ $val }}" {{ $medico->genero === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="activo" {{ $medico->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ $medico->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-stethoscope"></i> Datos Profesionales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Años de experiencia</label>
                <input type="number" name="anios_experiencia" min="0" max="50" value="{{ old('anios_experiencia', $medico->anios_experiencia) }}">
            </div>
        </div>

        <div class="form-group form-group--full">
            <label>Especialidades</label>
            <div class="checkbox-grid">
                @foreach($especialidades as $esp)
                <label class="checkbox-item">
                    <input type="checkbox" name="especialidades[]" value="{{ $esp->id_especialidad }}"
                           {{ in_array($esp->id_especialidad, $especialidadesAsignadas) ? 'checked' : '' }}>
                    {{ $esp->nombre }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('admin.medicos.index', request('especialidad_id') ? ['especialidad_id' => request('especialidad_id')] : []) }}" class="btn-secondary">
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
@endpush

@push('scripts')
<script>
document.getElementById('formGuardar').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Guardar cambios?',
        text: 'Se actualizarán los datos del médico.',
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