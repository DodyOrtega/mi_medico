@extends('layouts.medico')
@section('titulo', 'Nuevo Paciente')
@section('seccion_titulo', 'Nuevo Paciente')
@section('volver', route('medico.pacientes.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-user-plus"></i> Registrar Nuevo Paciente</h1>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('medico.pacientes.store') }}" class="form-card" id="formGuardar">
        @csrf

        <h3 class="form-card__seccion"><i class="fas fa-id-card"></i> Datos Personales</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Cédula *</label>
                <input type="text" name="cedula" maxlength="10" required value="{{ old('cedula') }}">
            </div>
            <div class="form-group">
                <label>Tipo *</label>
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
                <label>Fecha de nacimiento *</label>
                <input type="date" name="fecha_nac" required value="{{ old('fecha_nac') }}">
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
                <label>Nacionalidad</label>
                <input type="text" name="nacionalidad" value="{{ old('nacionalidad', 'Ecuatoriana') }}">
            </div>
            <div class="form-group">
                <label>Etnia</label>
                <select name="id_etnia" id="etnia_select" onchange="toggleNuevaEtnia()">
                    <option value="">Seleccione...</option>
                    @foreach($etnias as $etnia)
                    <option value="{{ $etnia->id_etnia }}">{{ $etnia->tipo_etnia }}</option>
                    @endforeach
                    <option value="nueva">+ Nueva etnia</option>
                </select>
            </div>
            <div class="form-group" id="nueva_etnia_wrap" style="display:none">
                <label>Nueva etnia</label>
                <input type="text" name="nueva_etnia" id="nueva_etnia"
                       placeholder="Escriba la nueva etnia"
                       oninput="this.value=this.value.toUpperCase()">
            </div>
            <div class="form-group">
                <label>Parroquia *</label>
                <select name="codigo_parroquia" required>
                    <option value="">Seleccione...</option>
                    @foreach($parroquias as $par)
                    <option value="{{ $par->codigo_parroquia }}">
                        {{ $par->nombre_provincia }} › {{ $par->nombre_canton }} › {{ $par->nombre_parroquia }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Barrio</label>
                <input type="text" name="barrio" value="{{ old('barrio') }}">
            </div>
            <div class="form-group form-group--full">
                <label>Dirección</label>
                <input type="text" name="direccion_1" value="{{ old('direccion_1') }}">
            </div>
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-notes-medical"></i> Información Clínica</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Tipo de sangre</label>
                <select name="tipo_sangre">
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','Desconocido'] as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group form-group--full">
                <label>Alergias</label>
                <textarea name="alergias" rows="2" placeholder="Ej: Penicilina, mariscos...">{{ old('alergias') }}</textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Antecedentes</label>
                <textarea name="antecedentes" rows="2" placeholder="Ej: Hipertensión, diabetes...">{{ old('antecedentes') }}</textarea>
            </div>
            <div class="form-group form-group--full">
                <label>Medicación habitual</label>
                <textarea name="medicacion_habitual" rows="2" placeholder="Ej: Losartán 50mg...">{{ old('medicacion_habitual') }}</textarea>
            </div>
        </div>

        <div class="checkbox-grid" style="margin: 16px 0;">
            <label class="checkbox-item">
                <input type="checkbox" name="fumador" value="1"> Fumador
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="consume_alcohol" value="1"> Consume alcohol
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="discapacidad" value="1" onchange="toggleDiscapacidad()"> Discapacidad
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="gestante" value="1" onchange="toggleGestante()"> Gestante
            </label>
        </div>

        <div id="discapacidad_wrap" style="display:none;" class="form-group">
            <label>Tipo de discapacidad</label>
            <input type="text" name="tipo_discapacidad" value="{{ old('tipo_discapacidad') }}">
        </div>

        <div id="gestante_wrap" style="display:none;" class="form-group">
            <label>Semanas de gestación</label>
            <input type="number" name="semanas_gestacion" min="1" max="45" value="{{ old('semanas_gestacion') }}">
        </div>

        <h3 class="form-card__seccion"><i class="fas fa-phone-volume"></i> Contacto de Emergencia</h3>
        <div class="form-grid">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="contacto_nombre" value="{{ old('contacto_nombre') }}">
            </div>
            <div class="form-group">
                <label>Parentesco</label>
                <input type="text" name="contacto_parentesco" value="{{ old('contacto_parentesco') }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="contacto_telefono" value="{{ old('contacto_telefono') }}">
            </div>
        </div>

        <div class="form-card__nota">
            <i class="fas fa-info-circle"></i>
            La contraseña temporal del paciente será su número de cédula.
        </div>

        <div class="form-card__actions">
            <a href="{{ route('medico.pacientes.index') }}" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn-success">
                <i class="fas fa-save"></i> Registrar Paciente
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
        title: '¿Registrar paciente?',
        text: 'Se creará el nuevo paciente en el sistema.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

function toggleNuevaEtnia() {
    const wrap = document.getElementById('nueva_etnia_wrap');
    wrap.style.display = document.getElementById('etnia_select').value === 'nueva' ? 'block' : 'none';
}
function toggleDiscapacidad() {
    document.getElementById('discapacidad_wrap').style.display =
        document.querySelector('[name=discapacidad]').checked ? 'block' : 'none';
}
function toggleGestante() {
    document.getElementById('gestante_wrap').style.display =
        document.querySelector('[name=gestante]').checked ? 'block' : 'none';
}
</script>
@endpush