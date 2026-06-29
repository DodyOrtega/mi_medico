@extends('layouts.admin')

@section('titulo', 'Agregar Paramédico')
@section('seccion_titulo', 'Agregar Paramédico')
@section('volver', route('admin.paramedicos.index'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-plus"></i> Agregar Nuevo Paramédico</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.paramedicos.store') }}" class="form-card" id="formGuardar">
        @csrf

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula *</label>
                <input type="text" name="cedula" maxlength="10" required placeholder="10 dígitos" value="{{ old('cedula') }}">
            </div>
            <div class="form-group">
                <label>Tipo de identificación *</label>
                <select name="tipo_cedula" required>
                    <option value="cedula">Cédula</option>
                    <option value="pasaporte">Pasaporte</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" name="nombres" required value="{{ old('nombres') }}">
            </div>
            <div class="form-group">
                <label>Apellido 1 *</label>
                <input type="text" name="apellido1" required value="{{ old('apellido1') }}">
            </div>
            <div class="form-group">
                <label>Apellido 2</label>
                <input type="text" name="apellido2" value="{{ old('apellido2') }}">
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="correo" required value="{{ old('correo') }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" maxlength="10" value="{{ old('telefono') }}">
            </div>
            <div class="form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nac" value="{{ old('fecha_nac') }}">
            </div>
            <div class="form-group">
                <label>Estado civil</label>
                <select name="estado_civil">
                    <option value="S">Soltero/a</option>
                    <option value="C">Casado/a</option>
                    <option value="V">Viudo/a</option>
                    <option value="D">Divorciado/a</option>
                </select>
            </div>
            <div class="form-group">
                <label>Género</label>
                <select name="genero">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label>Años de experiencia</label>
                <input type="number" name="anios_experiencia" min="0" max="50" value="{{ old('anios_experiencia') }}">
            </div>
        </div>

        <div class="form-card__nota">
            <i class="fas fa-info-circle"></i>
            La contraseña temporal del paramédico será su número de cédula. Se recomienda que la cambie en su primer acceso.
        </div>

        <div class="form-card__actions">
            <a href="{{ route('admin.paramedicos.index') }}" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn-success">
                <i class="fas fa-save"></i> Guardar Paramédico
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
        title: '¿Registrar paramédico?',
        text: 'Se creará el nuevo paramédico en el sistema.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});
</script>
@endpush