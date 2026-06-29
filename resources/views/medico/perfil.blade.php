@extends('layouts.medico')
@section('titulo', 'Mi Perfil')
@section('seccion_titulo', 'Mi Perfil')
@section('volver', route('medico.dashboard'))

@section('contenido')
<div class="medicos-page">
    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-edit"></i> Mi Perfil</h1>
    </div>

    @if(session('exito'))<div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>@endif
    @if(session('error'))<div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    <form method="POST" action="{{ route('medico.perfil.actualizar') }}" class="form-card" id="formGuardar">
        @csrf
        @method('PUT')

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula</label>
                <input type="text" value="{{ $persona->cedula }}" disabled>
            </div>
            <div class="form-group">
                <label>Nombres *</label>
                <input type="text" name="nombres" required value="{{ old('nombres', $persona->nombres) }}">
            </div>
            <div class="form-group">
                <label>Apellido 1 *</label>
                <input type="text" name="apellido1" required value="{{ old('apellido1', $persona->apellido1) }}">
            </div>
            <div class="form-group">
                <label>Apellido 2</label>
                <input type="text" name="apellido2" value="{{ old('apellido2', $persona->apellido2) }}">
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="correo" required value="{{ old('correo', $persona->correo) }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" maxlength="10" value="{{ old('telefono', $persona->telefono) }}">
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
        <div class="form-card__nota">
            <i class="fas fa-info-circle"></i>
            Deja estos campos vacíos si no deseas cambiar tu contraseña.
        </div>
        <div class="form-grid" style="margin-top:16px;">
            <div class="form-group">
                <label>Contraseña actual</label>
                <input type="password" name="current_password" placeholder="Tu contraseña actual">
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="new_password" placeholder="Mínimo 8 caracteres">
            </div>
            <div class="form-group">
                <label>Confirmar nueva contraseña</label>
                <input type="password" name="confirm_password" placeholder="Repite la nueva contraseña">
            </div>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('medico.dashboard') }}" class="btn-secondary">
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
        text: 'Se actualizarán los datos de tu perfil.',
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
