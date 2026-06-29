@extends('layouts.paciente')
@section('titulo', 'Mi Perfil')
@section('seccion_titulo', 'Mi Perfil')
@section('volver', route('paciente.dashboard'))

@section('contenido')
<div class="medicos-page">
    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-edit"></i> Mi Perfil</h1>
    </div>

    @if(session('success'))<div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    {{-- MÉDICO ASIGNADO --}}
    <div class="paciente-section" style="margin-bottom:0;">
        <h3 class="paciente-section__title"><i class="fas fa-user-md"></i> Mi Médico</h3>
        @if($medicoAsignado)
        @php $esFutura = \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->gt(now()); @endphp
        <div style="display:flex;align-items:center;gap:14px;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;">
            <div style="width:44px;height:44px;border-radius:50%;background:#0078d7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user-md" style="color:#fff;font-size:1.2rem;"></i>
            </div>
            <div>
                <p style="margin:0;font-weight:700;color:#1a2e44;font-size:1rem;">
                    Dr. {{ $medicoAsignado->nombres }} {{ $medicoAsignado->apellido1 }}
                </p>
                @if($medicoAsignado->especialidad)
                <p style="margin:2px 0 0;font-size:.82rem;color:#0078d7;">{{ $medicoAsignado->especialidad }}</p>
                @endif
                <p style="margin:4px 0 0;font-size:.78rem;color:#64748b;">
                    @if($esFutura)
                        <i class="fas fa-calendar-check" style="color:#16a34a;"></i>
                        Próxima cita: {{ \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->format('d/m/Y H:i') }}
                    @else
                        <i class="fas fa-clock" style="color:#f59e0b;"></i>
                        Última atención: {{ \Carbon\Carbon::parse($medicoAsignado->fecha_hora)->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
        </div>
        @else
        <p style="color:#94a3b8;font-size:.875rem;padding:10px 0;margin:0;">
            <i class="fas fa-user-slash"></i> No tienes médico asignado aún.
        </p>
        @endif
    </div>

    <form method="POST" action="{{ route('paciente.perfil.actualizar') }}" class="form-card">
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
            <div class="form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nac" value="{{ old('fecha_nac', $persona->fecha_nac) }}">
            </div>
            <div class="form-group">
                <label>Estado civil</label>
                <select name="estado_civil">
                    @foreach(['S'=>'Soltero/a','C'=>'Casado/a','V'=>'Viudo/a','D'=>'Divorciado/a'] as $val => $label)
                        <option value="{{ $val }}" {{ old('estado_civil', $persona->estado_civil) == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
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
                <input type="password" name="new_password_confirmation" placeholder="Repite la nueva contraseña">
            </div>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('paciente.dashboard') }}" class="btn-secondary">
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
