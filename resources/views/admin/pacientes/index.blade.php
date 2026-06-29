@extends('layouts.admin')

@section('titulo', 'Pacientes')
@section('seccion_titulo', 'Gestión de Pacientes')
@section('volver', route('admin.dashboard'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                <i class="fas fa-users"></i> Pacientes
            </h1>
            <p class="page-header__sub">{{ $pacientes->count() }} paciente(s) registrados</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Panel
        </a>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    {{-- ═══ BÚSQUEDA ═══ --}}
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.pacientes.index') }}" class="search-form">
            <input type="text" name="busqueda" class="search-input"
                   placeholder="Buscar por nombre, apellido o cédula..."
                   value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Buscar</button>
            @if($busqueda)
            <a href="{{ route('admin.pacientes.index') }}" class="btn-clear">
                <i class="fas fa-times"></i> Limpiar
            </a>
            @endif
        </form>
    </div>

    @if($busqueda)
    <p class="search-info"><i class="fas fa-search"></i> Resultados para "{{ $busqueda }}" — {{ $pacientes->count() }} resultado(s)</p>
    @endif

    {{-- ═══ GRID DE PACIENTES ═══ --}}
    @if($pacientes->isEmpty())
    <div class="empty-state">
        <i class="fas fa-users"></i>
        @if($busqueda)
            <p>No se encontraron pacientes para "{{ $busqueda }}"</p>
            <a href="{{ route('admin.pacientes.index') }}" class="btn-clear">
                <i class="fas fa-arrow-left"></i> Ver todos
            </a>
        @else
            <p>No hay pacientes registrados</p>
        @endif
    </div>
    @else
    <div class="medicos-grid">
        @foreach($pacientes as $paciente)
        @php
            $nombreCompleto = trim("{$paciente->nombres} {$paciente->apellido1} " . ($paciente->apellido2 ?? ''));
            $activo = ($paciente->estado ?? 'activo') === 'activo';
            $tieneCitas = $paciente->citas_pendientes > 0;
        @endphp
        <div class="medico-card paciente-card {{ $activo ? 'medico-card--activo' : 'medico-card--inactivo' }}">
            <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
            </span>

            <div class="medico-card__icon"><i class="fas fa-user"></i></div>
            <h3 class="medico-card__nombre">{{ $nombreCompleto }}</h3>
            <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $paciente->cedula }}</p>

            @if($tieneCitas)
            <span class="paciente-card__citas">
                <i class="fas fa-calendar-check"></i> {{ $paciente->citas_pendientes }} cita(s) pendiente(s)
            </span>
            @endif

            <div class="medico-card__actions">
                <a href="{{ route('admin.pacientes.show', $paciente->cedula) }}" class="action-btn action-btn--view">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('admin.pacientes.edit', $paciente->cedula) }}" class="action-btn action-btn--edit">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>

            <div class="medico-card__actions" style="margin-top: 8px;">
                <button type="button" class="action-btn reagendar-btn"
                        data-cedula="{{ $paciente->cedula }}"
                        data-nombre="{{ $nombreCompleto }}"
                        data-tiene-citas="{{ $tieneCitas ? '1' : '0' }}"
                        data-medico="{{ $paciente->medico_actual_nombre ?? '' }}"
                        onclick="abrirModalReagendar(this)">
                    <i class="fas fa-exchange-alt"></i> Reagendar
                </button>

                <form method="POST" action="{{ route('admin.pacientes.toggle-estado', $paciente->cedula) }}"
                      data-nombre="{{ $nombreCompleto }}" data-activo="{{ $activo ? '1' : '0' }}">
                    @csrf
                    <button type="button" class="action-btn {{ $activo ? 'action-btn--delete' : 'action-btn--add' }}"
                            onclick="confirmarToggleEstado(this)">
                        <i class="fas fa-{{ $activo ? 'ban' : 'check' }}"></i> {{ $activo ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
            </div>

            <div class="medico-card__actions" style="margin-top: 6px;">
                <form method="POST" action="{{ route('admin.pacientes.destroy', $paciente->cedula) }}"
                      style="width:100%;" class="form-eliminar-paciente">
                    @csrf @method('DELETE')
                    <button type="button"
                            class="action-btn"
                            style="width:100%;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"
                            data-nombre="{{ $nombreCompleto }}"
                            onclick="confirmarEliminarPaciente(this)">
                        <i class="fas fa-trash-alt"></i> Eliminar paciente
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ═══ MODAL: REAGENDAR PACIENTE ═══ --}}
<div id="modalReagendar" class="modal-overlay">
    <div class="modal-box" style="max-width:620px; padding:0; display:flex; flex-direction:column; max-height:88vh; overflow:hidden;">

        {{-- Header --}}
        <div style="flex-shrink:0; display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid #e8edf2;">
            <h2 style="margin:0; font-size:1.1rem; color:#2c3e50; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exchange-alt" style="color:#f59e0b"></i> Reagendar Paciente
            </h2>
            <button type="button" class="modal-box__close" onclick="cerrarModalReagendar()">&times;</button>
        </div>

        {{-- Cuerpo con scroll --}}
        <div style="flex:1; min-height:0; overflow-y:auto; padding:20px 24px;">
            <form method="POST" action="{{ route('admin.pacientes.reagendar') }}" id="formReagendar">
                @csrf
                <input type="hidden" name="cedula_paciente" id="paciente_cedula">
                <input type="hidden" name="fecha" id="fecha">
                <input type="hidden" name="hora" id="horaSeleccionada">

                <div id="infoPaciente" class="reagendar-info" style="margin-bottom:14px;"></div>

                <div id="warningCitas" class="alert alert--warning" style="display:none; margin-bottom:14px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="warningTexto"></span>
                </div>

                <div class="form-grid" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label>Especialidad *</label>
                        <select id="especialidad" class="form-select" required onchange="cargarMedicosPorEspecialidad()">
                            <option value="">-- Seleccione --</option>
                            @foreach($especialidades as $esp)
                            <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Médico *</label>
                        <select name="cedula_medico_nuevo" id="medico" class="form-select" required disabled onchange="cargarDiasDisponibles()">
                            <option value="">-- Primero especialidad --</option>
                        </select>
                        <span id="loadingMedicos" style="display:none; font-size:.8rem; color:#0078d7;">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </span>
                    </div>
                </div>

                <div id="diasSection" style="display:none; margin-bottom:16px;">
                    <p style="font-size:.82rem; font-weight:700; color:#475569; margin:0 0 8px; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-calendar-week"></i> Días con disponibilidad
                    </p>
                    <div id="diasContainer" class="dias-disponibles-grid"></div>
                </div>

                <div id="agendaSection" style="display:none; margin-bottom:16px;">
                    <p style="font-size:.82rem; font-weight:700; color:#475569; margin:0 0 8px; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-clock"></i> Horarios del día
                    </p>
                    <div id="infoMedicoContainer" style="margin-bottom:8px;"></div>
                    <div id="agendaContainer" class="agenda-grid"></div>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:.82rem;font-weight:700;color:#475569;display:block;margin-bottom:8px;">
                        <i class="fas fa-stethoscope"></i> Tipo de cita *
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;cursor:pointer;font-size:.85rem;font-weight:600;color:#6b7280;transition:all .2s;" id="lbl_presencial">
                            <input type="radio" name="tipo_cita" value="presencial" checked
                                   onchange="resaltarTipo()" style="accent-color:#0078d7;">
                            <i class="fas fa-hospital-user"></i> Presencial
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;cursor:pointer;font-size:.85rem;font-weight:600;color:#6b7280;transition:all .2s;" id="lbl_virtual">
                            <input type="radio" name="tipo_cita" value="virtual"
                                   onchange="resaltarTipo()" style="accent-color:#0078d7;">
                            <i class="fas fa-video"></i> Virtual
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Motivo de la reagendación</label>
                    <textarea name="motivo_reagendacion" rows="2" placeholder="Ej: Cambio de médico, etc."></textarea>
                </div>
            </form>
        </div>

        {{-- Footer siempre visible --}}
        <div style="flex-shrink:0; display:flex; justify-content:flex-end; gap:10px; padding:14px 24px; border-top:1px solid #e8edf2; background:#fff; border-radius:0 0 18px 18px;">
            <button type="button" class="btn-secondary" onclick="cerrarModalReagendar()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" form="formReagendar" class="btn-success" id="btnConfirmar" disabled>
                <i class="fas fa-exchange-alt"></i> Confirmar
            </button>
        </div>

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
<style>
.dias-disponibles-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}
.dia-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 64px;
    padding: 8px 4px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    transition: all .15s;
    user-select: none;
}
.dia-card:not(.dia-card--lleno):hover {
    border-color: #2563eb;
    background: #eff6ff;
}
.dia-card--seleccionado {
    border-color: #2563eb !important;
    background: #2563eb !important;
    color: #fff !important;
}
.dia-card--seleccionado .dia-card__semana,
.dia-card--seleccionado .dia-card__mes,
.dia-card--seleccionado .dia-card__libres {
    color: #fff !important;
}
.dia-card--lleno {
    opacity: .45;
    cursor: not-allowed;
}
.dia-card__semana {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: .05em;
}
.dia-card__num {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1.1;
    color: #1e293b;
}
.dia-card--seleccionado .dia-card__num { color: #fff; }
.dia-card__mes {
    font-size: .65rem;
    color: #64748b;
    text-transform: uppercase;
}
.dia-card__libres {
    font-size: .65rem;
    margin-top: 4px;
    color: #16a34a;
    font-weight: 600;
}
.dia-card--lleno .dia-card__libres { color: #dc3545; }
</style>
@endpush

@push('scripts')
<script>
let pacienteActual = null;

function abrirModalReagendar(btn) {
    const cedula     = btn.dataset.cedula;
    const nombre     = btn.dataset.nombre;
    const tieneCitas = btn.dataset.tieneCitas === '1';
    const medicoNombre = btn.dataset.medico;

    pacienteActual = cedula;
    document.getElementById('paciente_cedula').value = cedula;

    let infoHtml = `<strong>Paciente:</strong> ${nombre} &nbsp;·&nbsp; <strong>Cédula:</strong> ${cedula}`;
    if (medicoNombre) {
        infoHtml += `<br><span style="color:#0078d7;margin-top:4px;display:block;">
            <i class="fas fa-user-md"></i> <strong>Médico actual:</strong> Dr. ${medicoNombre}
        </span>`;
    }
    document.getElementById('infoPaciente').innerHTML = infoHtml;

    document.getElementById('especialidad').value = '';
    document.getElementById('medico').innerHTML = '<option value="">-- Primero seleccione una especialidad --</option>';
    document.getElementById('medico').disabled = true;
    document.getElementById('fecha').value = '';
    document.getElementById('diasSection').style.display = 'none';
    document.getElementById('diasContainer').innerHTML = '';
    document.getElementById('agendaSection').style.display = 'none';
    document.getElementById('horaSeleccionada').value = '';
    document.getElementById('btnConfirmar').disabled = true;

    const warning = document.getElementById('warningCitas');
    const warningTexto = document.getElementById('warningTexto');
    if (!tieneCitas) {
        warning.style.display = 'flex';
        warningTexto.textContent = 'Este paciente no tiene citas futuras programadas.';
        document.getElementById('btnConfirmar').disabled = true;
    } else {
        warning.style.display = 'flex';
        warningTexto.textContent = medicoNombre
            ? `Las citas futuras con Dr. ${medicoNombre} serán transferidas al médico y horario seleccionados.`
            : 'Las citas futuras serán transferidas al médico y horario seleccionados.';
    }

    document.getElementById('modalReagendar').style.display = 'flex';
}

function cerrarModalReagendar() {
    document.getElementById('modalReagendar').style.display = 'none';
    document.getElementById('formReagendar').reset();
    document.getElementById('diasSection').style.display = 'none';
    document.getElementById('diasContainer').innerHTML = '';
    document.getElementById('agendaSection').style.display = 'none';
}

function resaltarTipo() {
    const activo  = { border: '2px solid #0078d7', background: '#eff6ff', color: '#0078d7' };
    const inactivo = { border: '2px solid #e5e7eb', background: '', color: '#6b7280' };
    const esPres = document.querySelector('input[name="tipo_cita"]:checked')?.value === 'presencial';
    Object.assign(document.getElementById('lbl_presencial').style, esPres ? activo : inactivo);
    Object.assign(document.getElementById('lbl_virtual').style,    esPres ? inactivo : activo);
}
// Aplicar estilos iniciales
document.addEventListener('DOMContentLoaded', resaltarTipo);

function cargarMedicosPorEspecialidad() {
    const especialidadId = document.getElementById('especialidad').value;
    const medicoSelect = document.getElementById('medico');
    const loading = document.getElementById('loadingMedicos');
    const agendaSection = document.getElementById('agendaSection');

    if (!especialidadId) {
        medicoSelect.innerHTML = '<option value="">-- Primero seleccione una especialidad --</option>';
        medicoSelect.disabled = true;
        agendaSection.style.display = 'none';
        return;
    }

    loading.style.display = 'inline';
    medicoSelect.disabled = true;

    fetch(`/admin/pacientes/ajax/medicos-por-especialidad/${especialidadId}`)
        .then(r => r.json())
        .then(data => {
            medicoSelect.innerHTML = '<option value="">-- Seleccione un médico --</option>';
            data.forEach(m => {
                medicoSelect.innerHTML += `<option value="${m.cedula}">Dr. ${m.nombres} ${m.apellido1}</option>`;
            });
            medicoSelect.disabled = false;
            loading.style.display = 'none';
            agendaSection.style.display = 'none';
            document.getElementById('diasSection').style.display = 'none';
            document.getElementById('diasContainer').innerHTML = '';
            document.getElementById('fecha').value = '';
        })
        .catch(() => {
            loading.style.display = 'none';
            Swal.fire('Error', 'No se pudieron cargar los médicos', 'error');
        });
}

function cargarDiasDisponibles() {
    const medicoId = document.getElementById('medico').value;
    const diasSection = document.getElementById('diasSection');
    const diasContainer = document.getElementById('diasContainer');
    const agendaSection = document.getElementById('agendaSection');

    document.getElementById('fecha').value = '';
    document.getElementById('horaSeleccionada').value = '';
    document.getElementById('btnConfirmar').disabled = true;
    agendaSection.style.display = 'none';

    if (!medicoId) {
        diasSection.style.display = 'none';
        return;
    }

    diasSection.style.display = 'block';
    diasContainer.innerHTML = '<div style="text-align:center;padding:12px;color:#0078d7;"><i class="fas fa-spinner fa-spin"></i> Cargando días...</div>';

    fetch(`/admin/pacientes/ajax/dias-disponibles?medico=${medicoId}`)
        .then(r => r.json())
        .then(dias => {
            diasContainer.innerHTML = '';
            if (!dias.length) {
                diasContainer.innerHTML = '<p style="color:#888;font-size:.85rem;text-align:center;padding:8px;">Sin días disponibles en los próximos 14 días.</p>';
                return;
            }
            dias.forEach(dia => {
                const lleno = dia.disponibles === 0;
                const card = document.createElement('div');
                card.className = 'dia-card' + (lleno ? ' dia-card--lleno' : '');
                card.innerHTML = `
                    <div class="dia-card__semana">${dia.dia_semana}</div>
                    <div class="dia-card__num">${dia.dia_num}</div>
                    <div class="dia-card__mes">${dia.mes}</div>
                    <div class="dia-card__libres">${lleno ? 'Sin espacio' : `${dia.disponibles} libre${dia.disponibles !== 1 ? 's' : ''}`}</div>
                `;
                if (!lleno) {
                    card.style.cursor = 'pointer';
                    card.onclick = () => seleccionarDia(dia.fecha, card);
                }
                diasContainer.appendChild(card);
            });
        })
        .catch(() => {
            diasContainer.innerHTML = '<p style="color:#dc3545;font-size:.85rem;text-align:center;">Error al cargar días.</p>';
        });
}

function seleccionarDia(fecha, card) {
    document.querySelectorAll('.dia-card').forEach(c => c.classList.remove('dia-card--seleccionado'));
    card.classList.add('dia-card--seleccionado');
    document.getElementById('fecha').value = fecha;
    document.getElementById('horaSeleccionada').value = '';
    document.getElementById('btnConfirmar').disabled = true;
    cargarAgendaCompleta();
}

function cargarAgendaCompleta() {
    const medicoId = document.getElementById('medico').value;
    const fecha = document.getElementById('fecha').value;
    const agendaSection = document.getElementById('agendaSection');
    const btnConfirmar = document.getElementById('btnConfirmar');

    if (!medicoId || !fecha) {
        agendaSection.style.display = 'none';
        btnConfirmar.disabled = true;
        return;
    }

    fetch(`/admin/pacientes/ajax/agenda-disponible?medico=${medicoId}&fecha=${fecha}`)
        .then(r => r.json())
        .then(data => {
            mostrarAgenda(data);
            agendaSection.style.display = 'block';
            btnConfirmar.disabled = true;
        })
        .catch(() => Swal.fire('Error', 'No se pudo cargar la agenda', 'error'));
}

function mostrarAgenda(data) {
    const infoContainer = document.getElementById('infoMedicoContainer');
    const agendaContainer = document.getElementById('agendaContainer');
    const horaActual = data.hora_servidor;
    const fechaActual = data.fecha_servidor;
    const fechaSeleccionada = document.getElementById('fecha').value;

    infoContainer.innerHTML = `
        <div class="reagendar-medico-info">
            <i class="fas fa-user-md"></i>
            <div>
                <div style="font-weight:600;">Dr. ${data.medico?.nombres ?? ''} ${data.medico?.apellido1 ?? ''}</div>
                <div style="color:#0078d7; font-size:.8rem;">${data.medico?.especialidad ?? 'Médico'}</div>
            </div>
        </div>`;

    agendaContainer.innerHTML = '';

    if (!data.horarios || data.horarios.length === 0) {
        agendaContainer.innerHTML = '<div style="text-align:center; padding:15px; color:#888;">No hay horarios disponibles</div>';
        return;
    }

    if (!data.horarios || data.horarios.length === 0) {
        agendaContainer.innerHTML = '<div style="text-align:center;padding:20px;color:#94a3b8;font-size:.9rem;"><i class="fas fa-calendar-times" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>Este médico no tiene horarios para ese día.</div>';
        return;
    }

    data.horarios.forEach(horario => {
        const card = document.createElement('div');
        const horaPasada = fechaSeleccionada === fechaActual && horario.hora_completa < horaActual;
        const estado = horaPasada ? 'pasado' : horario.estado;

        card.className = `horario-card ${estado}`;

        let icono, texto;
        if (horaPasada)                     { icono = 'fa-clock';       texto = 'Pasada';   }
        else if (horario.estado === 'ocupado') { icono = 'fa-lock';       texto = 'Ocupado';  }
        else                                { icono = 'fa-check-circle'; texto = 'Libre';    }

        card.innerHTML = `
            <div class="horario-card__hora">${horario.hora}</div>
            <span class="horario-card__estado"><i class="fas ${icono}"></i> ${texto}</span>
        `;

        if (horario.estado === 'disponible' && !horaPasada) {
            card.onclick = (e) => seleccionarHorario(horario.hora, e);
        }

        agendaContainer.appendChild(card);
    });
}

function seleccionarHorario(hora, event) {
    document.querySelectorAll('.horario-card').forEach(c => c.classList.remove('seleccionado'));
    event.currentTarget.classList.add('seleccionado');
    document.getElementById('horaSeleccionada').value = hora + ':00';
    document.getElementById('btnConfirmar').disabled = false;
}

document.getElementById('formReagendar').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const medico = document.getElementById('medico').value;
    const hora = document.getElementById('horaSeleccionada').value;
    if (!medico) { Swal.fire('Atención', 'Seleccione un médico', 'warning'); return; }
    if (!hora)   { Swal.fire('Atención', 'Seleccione una hora disponible', 'warning'); return; }
    Swal.fire({
        title: '¿Confirmar reagendar?',
        text: 'Se asignará la nueva cita al paciente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reagendar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

function confirmarToggleEstado(btn) {
    const form = btn.closest('form');
    const nombre = form.dataset.nombre;
    const activo = form.dataset.activo === '1';
    const accion = activo ? 'desactivar' : 'activar';
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} paciente?`,
        html: `¿Estás seguro de ${accion} a <strong>${nombre}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: activo ? '#dc3545' : '#28a745',
    }).then(result => { if (result.isConfirmed) form.submit(); });
}

function confirmarEliminarPaciente(btn) {
    const form   = btn.closest('form');
    const nombre = btn.dataset.nombre;
    Swal.fire({
        title: '¿Eliminar paciente?',
        html: `Se eliminará permanentemente a <strong>${nombre}</strong> con todas sus citas e historial.<br><br><strong style="color:#dc2626">Esta acción no se puede deshacer.</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        focusCancel: true,
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
}

window.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) e.target.style.display = 'none';
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
});
</script>
@endpush