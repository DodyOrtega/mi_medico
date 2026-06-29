@extends('layouts.paciente')

@section('titulo', 'Agendar Cita')
@section('seccion_titulo', 'Agendar Cita')
@section('volver', route('paciente.citas'))

@section('contenido')

    <div class="page-header">
        <div>
            <h2 class="page-header__title"><i class="fas fa-calendar-plus"></i> Agendar Cita</h2>
            <p class="page-header__sub">Selecciona médico, fecha y horario disponible</p>
        </div>
        <a href="{{ route('paciente.citas') }}" class="btn-ver"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="paciente-section">
        <form method="POST" action="{{ route('paciente.citas.guardar') }}" id="formAgendar">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <div class="info-block">
                    <label class="info-label">Médico <span style="color:#dc3545">*</span></label>
                    <select name="medico" id="selectMedico" class="form-select" required onchange="cargarHorarios()">
                        <option value="">— Selecciona un médico —</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->cedula }}" {{ old('medico') == $m->cedula ? 'selected' : '' }}>
                                Dr. {{ $m->nombres }} {{ $m->apellido1 }}
                                @if($m->especialidades) — {{ $m->especialidades }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="info-block">
                    <label class="info-label">Tipo de Cita <span style="color:#dc3545">*</span></label>
                    <select name="tipo_cita" class="form-select" required>
                        <option value="presencial" {{ old('tipo_cita') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="virtual"    {{ old('tipo_cita') == 'virtual'    ? 'selected' : '' }}>Teleconsulta (Virtual)</option>
                    </select>
                </div>

                <div class="info-block" style="grid-column:1/-1;">
                    <label class="info-label">Fecha <span style="color:#dc3545">*</span></label>
                    <input type="date" name="fecha" id="inputFecha" class="form-select"
                           min="{{ today()->format('Y-m-d') }}"
                           value="{{ old('fecha') }}" required onchange="cargarHorarios()">
                </div>

            </div>

            {{-- GRILLA DE HORARIOS --}}
            <div class="info-block" style="margin-top:16px;">
                <label class="info-label">Horario <span style="color:#dc3545">*</span></label>
                <input type="hidden" name="hora" id="horaSeleccionada" value="{{ old('hora') }}">
                <div id="horariosGrid" class="agenda-grid">
                    <p style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px 0;margin:0;font-size:.875rem;">
                        <i class="fas fa-calendar-day"></i> Selecciona un médico y una fecha
                    </p>
                </div>
            </div>

            <div class="info-block" style="margin-top:4px;">
                <label class="info-label">Motivo de consulta <span style="color:#dc3545">*</span></label>
                <textarea name="motivo" class="form-select" rows="3" maxlength="200"
                          placeholder="Describe brevemente el motivo de tu consulta..." required>{{ old('motivo') }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                <a href="{{ route('paciente.citas') }}" class="btn-accion btn-accion--back" style="padding:10px 20px;">Cancelar</a>
                <button type="submit" id="btnConfirmar" class="btn-paciente-primary" disabled>
                    <i class="fas fa-calendar-check"></i> Confirmar Cita
                </button>
            </div>
        </form>
    </div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
<style>
.form-select {
    width: 100%; padding: 9px 12px;
    border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .875rem; color: #374151;
    background: #fff; transition: border-color .2s;
}
.form-select:focus { outline: none; border-color: #0078d7; box-shadow: 0 0 0 3px rgba(0,120,215,.1); }
</style>
@endpush

@push('scripts')
<script>
function cargarHorarios() {
    const medico = document.getElementById('selectMedico').value;
    const fecha  = document.getElementById('inputFecha').value;
    const grid   = document.getElementById('horariosGrid');

    document.getElementById('horaSeleccionada').value = '';
    document.getElementById('btnConfirmar').disabled = true;

    if (!medico || !fecha) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px 0;margin:0;font-size:.875rem;"><i class="fas fa-calendar-day"></i> Selecciona un médico y una fecha</p>';
        return;
    }

    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px 0;margin:0;font-size:.875rem;"><i class="fas fa-spinner fa-spin"></i> Cargando horarios...</p>';

    fetch(`{{ route('paciente.ajax.horarios') }}?medico=${medico}&fecha=${fecha}`)
        .then(r => r.json())
        .then(slots => {
            if (!slots.length) {
                grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px 0;margin:0;font-size:.875rem;"><i class="fas fa-calendar-xmark"></i> El médico no tiene horarios para esta fecha</p>';
                return;
            }
            grid.innerHTML = slots.map(s => {
                const hora5 = s.hora.substring(0, 5);
                const libre  = s.estado === 'disponible';
                return `<div class="horario-card ${s.estado}"
                             ${libre ? `onclick="seleccionarHora('${s.hora}', this)"` : ''}
                             title="${libre ? 'Disponible — haz clic para seleccionar' : 'Ocupado'}">
                    <span class="horario-card__hora">${hora5}</span>
                    <span class="horario-card__estado">
                        ${libre
                            ? '<i class="fas fa-check"></i> Libre'
                            : '<i class="fas fa-user"></i> Ocupado'}
                    </span>
                </div>`;
            }).join('');
        })
        .catch(() => {
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#dc3545;padding:20px 0;margin:0;font-size:.875rem;"><i class="fas fa-exclamation-circle"></i> Error al cargar los horarios</p>';
        });
}

function seleccionarHora(hora, card) {
    document.querySelectorAll('#horariosGrid .horario-card.seleccionado')
            .forEach(c => c.classList.remove('seleccionado'));
    card.classList.add('seleccionado');
    document.getElementById('horaSeleccionada').value = hora;
    document.getElementById('btnConfirmar').disabled = false;
}
</script>
@endpush
