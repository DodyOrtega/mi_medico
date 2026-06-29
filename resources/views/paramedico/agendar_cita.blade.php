{{-- ═══════════════════════════════════════════════
     ARCHIVO: agendar_cita.blade.php
     USO: resources/views/paramedico/agendar_cita.blade.php
════════════════════════════════════════════ --}}
@extends('layouts.paramedico')
@section('titulo','Agendar Cita')
@section('seccion_titulo','Agendar Cita con Médico')
@section('volver', route('paramedico.pacientes.index'))

@section('contenido')
<div class="medicos-page">
    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-calendar-plus"></i> Agendar Cita con Médico</h1>
    </div>

    @if(session('error'))<div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    @if($paciente)
    <div class="paciente-resumen" style="margin-bottom:20px;">
        <div class="paciente-resumen__avatar"><i class="fas fa-user"></i></div>
        <div>
            <strong>{{ $paciente->nombres }} {{ $paciente->apellido1 }}</strong>
            <span>Cédula: {{ $paciente->cedula }}</span>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('paramedico.citas.guardar') }}" class="form-card" id="formGuardar">
        @csrf
        <div class="form-grid">
            @if(!$paciente)
            <div class="form-group">
                <label>Cédula del paciente *</label>
                <input type="text" name="cedula_paciente" required maxlength="10" placeholder="Cédula..." value="{{ old('cedula_paciente') }}">
            </div>
            @else
            <input type="hidden" name="cedula_paciente" value="{{ $paciente->cedula }}">
            @endif

            <div class="form-group">
                <label>Especialidad *</label>
                <select id="especialidad" class="form-group input" required onchange="cargarMedicos()">
                    <option value="">Seleccione...</option>
                    @foreach($especialidades as $esp)
                    <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Médico *</label>
                <select name="cedula_medico" id="medico" required disabled onchange="cargarAgenda()">
                    <option value="">Primero seleccione especialidad</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha *</label>
                <input type="date" name="fecha" id="fecha" required min="{{ now()->format('Y-m-d') }}" onchange="cargarAgenda()">
            </div>

            <div class="form-group">
                <label>Tipo de cita *</label>
                <select name="tipo_cita" required>
                    <option value="presencial">Presencial</option>
                    <option value="virtual">Virtual</option>
                </select>
            </div>
        </div>

        <div id="agendaSection" style="display:none; margin:16px 0;">
            <h4 style="color:#2c3e50; margin-bottom:10px; font-size:1rem;"><i class="fas fa-clock"></i> Horarios disponibles</h4>
            <div id="agendaContainer" class="agenda-grid"></div>
            <input type="hidden" name="hora" id="horaSeleccionada">
        </div>

        <div class="form-group form-group--full">
            <label>Motivo de la cita *</label>
            <textarea name="motivo" rows="3" required placeholder="Describe el motivo..."></textarea>
        </div>

        <div class="form-card__actions">
            <a href="{{ route('paramedico.pacientes.index') }}" class="btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            <button type="submit" class="btn-success" id="btnGuardar" disabled><i class="fas fa-save"></i> Confirmar Cita</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/agenda.css') }}">
@endpush

@push('scripts')
<script>
document.getElementById('formGuardar').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Confirmar cita?',
        text: 'Se agendará la cita con el médico seleccionado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

function cargarMedicos() {
    const id = document.getElementById('especialidad').value;
    const sel = document.getElementById('medico');
    if (!id) return;
    sel.disabled = true;
    fetch(`/paramedico/ajax/medicos/${id}`)
        .then(r=>r.json())
        .then(data=>{
            sel.innerHTML = '<option value="">Seleccione médico...</option>';
            data.forEach(m => sel.innerHTML += `<option value="${m.cedula}">Dr. ${m.nombres} ${m.apellido1}</option>`);
            sel.disabled = false;
            document.getElementById('agendaSection').style.display = 'none';
        });
}
function cargarAgenda() {
    const medico = document.getElementById('medico').value;
    const fecha  = document.getElementById('fecha').value;
    if (!medico || !fecha) return;
    fetch(`/paramedico/ajax/agenda?medico=${medico}&fecha=${fecha}`)
        .then(r=>r.json())
        .then(data=>{
            const cont = document.getElementById('agendaContainer');
            cont.innerHTML = '';
            data.horarios.forEach(h=>{
                const d = document.createElement('div');
                d.className = `horario-card ${h.estado}`;
                d.innerHTML = `<div class="horario-hora">${h.hora}</div><span class="horario-estado">${h.estado==='disponible'?'LIBRE':'OCUPADO'}</span>`;
                if (h.estado === 'disponible') {
                    d.style.cursor = 'pointer';
                    d.onclick = ()=>{ document.querySelectorAll('.horario-card').forEach(c=>c.classList.remove('seleccionado')); d.classList.add('seleccionado'); document.getElementById('horaSeleccionada').value=h.hora; document.getElementById('btnGuardar').disabled=false; };
                }
                cont.appendChild(d);
            });
            document.getElementById('agendaSection').style.display = 'block';
        });
}
</script>
@endpush