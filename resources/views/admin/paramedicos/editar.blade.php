@extends('layouts.admin')

@section('titulo', 'Editar Paramédico')
@section('seccion_titulo', 'Editar Paramédico')
@section('volver', route('admin.paramedicos.index'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-edit"></i> Editar Paramédico</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.paramedicos.update', $paramedico->cedula) }}" class="form-card" id="formGuardar">
        @csrf
        @method('PUT')

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula</label>
                <input type="text" value="{{ $paramedico->cedula }}" disabled>
            </div>
            <div class="form-group">
                <label>Tipo de identificación *</label>
                <select name="tipo_cedula" required>
                    <option value="cedula" {{ $paramedico->tipo_cedula === 'cedula' ? 'selected' : '' }}>Cédula</option>
                    <option value="pasaporte" {{ $paramedico->tipo_cedula === 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" name="nombres" required value="{{ old('nombres', $paramedico->nombres) }}">
            </div>
            <div class="form-group">
                <label>Apellido 1 *</label>
                <input type="text" name="apellido1" required value="{{ old('apellido1', $paramedico->apellido1) }}">
            </div>
            <div class="form-group">
                <label>Apellido 2</label>
                <input type="text" name="apellido2" value="{{ old('apellido2', $paramedico->apellido2) }}">
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="correo" required value="{{ old('correo', $paramedico->correo) }}">
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="contrasena" placeholder="Dejar vacío para mantener la actual">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" maxlength="10" value="{{ old('telefono', $paramedico->telefono) }}">
            </div>
            <div class="form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nac" value="{{ old('fecha_nac', $paramedico->fecha_nac) }}">
            </div>
            <div class="form-group">
                <label>Estado civil</label>
                <select name="estado_civil">
                    @foreach(['S' => 'Soltero/a', 'C' => 'Casado/a', 'V' => 'Viudo/a', 'D' => 'Divorciado/a'] as $val => $label)
                    <option value="{{ $val }}" {{ $paramedico->estado_civil === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Género</label>
                <select name="genero">
                    @foreach(['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $val => $label)
                    <option value="{{ $val }}" {{ $paramedico->genero === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="activo" {{ $paramedico->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ $paramedico->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="form-group">
                <label>Años de experiencia</label>
                <input type="number" name="anios_experiencia" min="0" max="50" value="{{ old('anios_experiencia', $paramedico->anios_experiencia) }}">
            </div>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('admin.paramedicos.index') }}" class="btn-secondary">
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
        text: 'Se actualizarán los datos del paramédico.',
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