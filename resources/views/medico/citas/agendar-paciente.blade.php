@extends('layouts.medico')
@section('titulo', 'Agendar cita a paciente')
@section('seccion_titulo', 'Agendar cita')
@section('volver', route('medico.pacientes.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                <i class="fas fa-calendar-plus"></i>
                Agendar cita —
                <span style="color:#0078d7;">
                    {{ strtoupper($pacienteSeleccionado->apellido1 ?? '') }},
                    {{ $pacienteSeleccionado->nombres ?? '' }}
                </span>
            </h1>
            <p class="page-header__sub">Selecciona día y horario disponible</p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('medico.citas.agendar.guardar') }}" id="formAgendar">
        @csrf
        <input type="hidden" name="cedula_paciente" value="{{ $pacienteSeleccionado->cedula ?? '' }}">
        <input type="hidden" name="fecha" id="fechaInput">
        <input type="hidden" name="hora"  id="horaInput">

        <div class="ap-grid">

            {{-- ── Columna izquierda: detalles ── --}}
            <div class="ap-col">

                {{-- Detalles de la cita --}}
                <div class="ap-card">
                    <h3 class="ap-card__title"><i class="fas fa-notes-medical"></i> Detalles</h3>

                    <div class="form-group">
                        <label class="ap-label">Tipo de cita *</label>
                        <div class="ap-tipo-grid">
                            <label class="ap-tipo-opt">
                                <input type="radio" name="tipo_cita" value="presencial"
                                       {{ old('tipo_cita','presencial') === 'presencial' ? 'checked' : '' }} required>
                                <span><i class="fas fa-hospital-user"></i> Presencial</span>
                            </label>
                            <label class="ap-tipo-opt">
                                <input type="radio" name="tipo_cita" value="virtual"
                                       {{ old('tipo_cita') === 'virtual' ? 'checked' : '' }}>
                                <span><i class="fas fa-video"></i> Virtual</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="ap-label">Motivo de consulta *</label>
                        <textarea name="motivo" rows="3" class="ap-textarea" required
                                  placeholder="Ej: Control mensual, revisión de resultados..."
                                  minlength="3" maxlength="200">{{ old('motivo') }}</textarea>
                    </div>
                </div>

                {{-- Resumen de la selección --}}
                <div class="ap-card" id="resumenCard" style="display:none;">
                    <h3 class="ap-card__title"><i class="fas fa-check-circle" style="color:#22c55e;"></i> Selección</h3>
                    <p class="ap-resumen-linea"><i class="fas fa-calendar"></i> <span id="resumenFecha">—</span></p>
                    <p class="ap-resumen-linea"><i class="fas fa-clock"></i>    <span id="resumenHora">—</span></p>
                </div>

                {{-- Botón confirmar --}}
                <button type="submit" class="ap-btn-confirm" id="btnConfirmar" disabled>
                    <i class="fas fa-calendar-check"></i> Confirmar cita
                </button>
                <p id="btnHint" class="ap-hint">Selecciona un día y hora para continuar</p>

            </div>

            {{-- ── Columna derecha: calendario ── --}}
            <div class="ap-col">

                {{-- Días disponibles --}}
                <div class="ap-card">
                    <h3 class="ap-card__title"><i class="fas fa-calendar-week"></i> Días con disponibilidad</h3>
                    <div id="diasLoading" class="ap-loading">
                        <i class="fas fa-spinner fa-spin"></i> Cargando días...
                    </div>
                    <div id="diasContainer" class="ap-dias-grid"></div>
                    <p id="diasVacio" class="ap-empty" style="display:none;">
                        <i class="fas fa-calendar-xmark"></i>
                        No tienes horarios disponibles en los próximos 30 días.<br>
                        <a href="{{ route('medico.agenda.gestionar') }}" class="ap-link">
                            <i class="fas fa-calendar-plus"></i> Gestionar agenda
                        </a>
                    </p>
                </div>

                {{-- Horarios --}}
                <div class="ap-card" id="horariosSection" style="display:none;">
                    <h3 class="ap-card__title">
                        <i class="fas fa-clock"></i> Horarios disponibles
                        <span id="fechaSelLabel" class="ap-fecha-badge"></span>
                    </h3>
                    <div id="horariosLoading" class="ap-loading" style="display:none;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando horarios...
                    </div>
                    <div id="horariosContainer" class="ap-horarios-grid"></div>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<style>
.ap-grid { display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start; }
@media(max-width:768px){ .ap-grid { grid-template-columns:1fr; } }
.ap-col { display:flex; flex-direction:column; gap:16px; }

.ap-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px;
           padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.ap-card__title { font-size:.92rem; font-weight:700; color:#1a2e44;
                  display:flex; align-items:center; gap:8px; margin:0 0 16px; }
.ap-card__title i { color:#0078d7; }

.ap-label   { font-size:.82rem; font-weight:600; color:#374151; display:block; margin-bottom:6px; }
.ap-textarea { width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;
               font-size:.88rem; resize:vertical; font-family:inherit; color:#1a2e44; box-sizing:border-box; }
.ap-textarea:focus { outline:none; border-color:#0078d7; box-shadow:0 0 0 3px rgba(0,120,215,.1); }

.ap-tipo-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.ap-tipo-opt input { display:none; }
.ap-tipo-opt span { display:flex; align-items:center; justify-content:center; gap:8px;
                    padding:10px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer;
                    font-size:.85rem; font-weight:600; color:#6b7280; transition:all .2s; }
.ap-tipo-opt input:checked + span { border-color:#0078d7; background:#eff6ff; color:#0078d7; }
.ap-tipo-opt span:hover { border-color:#0078d7; color:#0078d7; }

.ap-resumen-linea { display:flex; align-items:center; gap:10px; font-size:.87rem;
                    color:#374151; margin:6px 0; }
.ap-resumen-linea i { color:#0078d7; width:16px; }

.ap-dias-grid { display:flex; flex-wrap:wrap; gap:8px; }
.ap-dia-pill { display:flex; flex-direction:column; align-items:center; padding:10px 14px;
               border:2px solid #e5e7eb; border-radius:12px; cursor:pointer; min-width:64px;
               transition:all .2s; background:#fff; }
.ap-dia-pill__sem  { font-size:.68rem; color:#9ca3af; text-transform:uppercase; font-weight:600; }
.ap-dia-pill__num  { font-size:1.3rem; font-weight:800; color:#1a2e44; line-height:1.1; }
.ap-dia-pill__mes  { font-size:.68rem; color:#6b7280; }
.ap-dia-pill__slots{ font-size:.65rem; color:#22c55e; font-weight:700; margin-top:3px; }
.ap-dia-pill:hover  { border-color:#0078d7; background:#f0f7ff; }
.ap-dia-pill.activo { border-color:#0078d7; background:#0078d7; }
.ap-dia-pill.activo .ap-dia-pill__sem,
.ap-dia-pill.activo .ap-dia-pill__num,
.ap-dia-pill.activo .ap-dia-pill__mes,
.ap-dia-pill.activo .ap-dia-pill__slots { color:#fff; }

.ap-fecha-badge { background:#eff6ff; color:#0078d7; font-size:.75rem; font-weight:600;
                  padding:2px 10px; border-radius:20px; margin-left:8px; }
.ap-horarios-grid { display:flex; flex-wrap:wrap; gap:8px; }
.ap-slot { padding:9px 18px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer;
           font-size:.88rem; font-weight:600; color:#374151; transition:all .2s; background:#fff; }
.ap-slot:hover   { border-color:#0078d7; color:#0078d7; background:#f0f7ff; }
.ap-slot.activo  { border-color:#0078d7; background:#0078d7; color:#fff; }
.ap-slot.ocupado { border-color:#f3f4f6; background:#f9fafb; color:#d1d5db;
                   cursor:not-allowed; text-decoration:line-through; }

.ap-btn-confirm { width:100%; padding:14px; background:linear-gradient(135deg,#22c55e,#16a34a);
                  color:#fff; border:none; border-radius:10px; font-weight:700; font-size:.95rem;
                  cursor:pointer; transition:all .2s; display:flex; align-items:center;
                  justify-content:center; gap:10px; }
.ap-btn-confirm:disabled { background:#d1d5db; color:#9ca3af; cursor:not-allowed; }
.ap-btn-confirm:not(:disabled):hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(34,197,94,.35); }

.ap-hint    { font-size:.78rem; color:#9ca3af; text-align:center; margin:4px 0 0; }
.ap-loading { color:#6b7280; font-size:.85rem; display:flex; align-items:center; gap:8px; padding:8px 0; }
.ap-empty   { font-size:.85rem; color:#9ca3af; text-align:center; padding:24px 0; line-height:1.8; }
.ap-empty i { display:block; font-size:2rem; margin-bottom:8px; color:#d1d5db; }
.ap-link    { color:#0078d7; text-decoration:none; font-weight:600; }
.ap-link:hover { text-decoration:underline; }
</style>
@endpush

@push('scripts')
<script>
const URL_DIAS     = "{{ route('medico.ajax.dias') }}";
const URL_HORARIOS = "{{ route('medico.ajax.horarios') }}";

let fechaSel = null;
let horaSel  = null;

document.addEventListener('DOMContentLoaded', cargarDias);

async function cargarDias() {
    const cont    = document.getElementById('diasContainer');
    const vacio   = document.getElementById('diasVacio');
    const loading = document.getElementById('diasLoading');

    loading.style.display = 'flex';
    cont.innerHTML = '';
    vacio.style.display = 'none';

    try {
        const res  = await fetch(URL_DIAS);
        const dias = await res.json();
        loading.style.display = 'none';

        if (!dias.length) { vacio.style.display = 'block'; return; }

        dias.forEach(d => {
            const pill = document.createElement('div');
            pill.className = 'ap-dia-pill';
            pill.dataset.fecha = d.fecha;
            pill.innerHTML = `
                <span class="ap-dia-pill__sem">${d.dia_semana}</span>
                <span class="ap-dia-pill__num">${d.dia_num}</span>
                <span class="ap-dia-pill__mes">${d.mes}</span>
                <span class="ap-dia-pill__slots">${d.disponibles} libre${d.disponibles !== 1 ? 's' : ''}</span>
            `;
            pill.addEventListener('click', () => seleccionarDia(pill, d.fecha, `${d.dia_semana} ${d.dia_num} ${d.mes}`));
            cont.appendChild(pill);
        });
    } catch (e) {
        loading.style.display = 'none';
        cont.innerHTML = '<p style="color:#dc2626;font-size:.85rem;"><i class="fas fa-exclamation-circle"></i> Error al cargar días.</p>';
    }
}

async function seleccionarDia(pill, fecha, etiqueta) {
    document.querySelectorAll('.ap-dia-pill').forEach(p => p.classList.remove('activo'));
    pill.classList.add('activo');

    fechaSel = fecha;
    horaSel  = null;
    document.getElementById('fechaInput').value = fecha;
    document.getElementById('horaInput').value  = '';
    document.getElementById('fechaSelLabel').textContent = etiqueta;

    actualizarBoton();
    await cargarHorarios(fecha);
}

async function cargarHorarios(fecha) {
    const sec     = document.getElementById('horariosSection');
    const cont    = document.getElementById('horariosContainer');
    const loading = document.getElementById('horariosLoading');

    sec.style.display     = 'block';
    cont.innerHTML        = '';
    loading.style.display = 'flex';

    try {
        const res      = await fetch(`${URL_HORARIOS}?fecha=${fecha}`);
        const horarios = await res.json();
        loading.style.display = 'none';

        if (!horarios.length) {
            cont.innerHTML = '<p style="color:#9ca3af;font-size:.85rem;">Sin horarios disponibles para este día.</p>';
            return;
        }

        horarios.forEach(h => {
            const btn    = document.createElement('button');
            btn.type     = 'button';
            const ocupado = h.estado === 'ocupado';
            btn.className = 'ap-slot' + (ocupado ? ' ocupado' : '');
            btn.textContent = h.hora;
            if (ocupado) {
                btn.title    = h.paciente ? `Ocupado — ${h.paciente}` : 'Ocupado';
                btn.disabled = true;
            } else {
                btn.addEventListener('click', () => seleccionarHora(btn, h.hora));
            }
            cont.appendChild(btn);
        });
    } catch (e) {
        loading.style.display = 'none';
        cont.innerHTML = '<p style="color:#dc2626;font-size:.85rem;"><i class="fas fa-exclamation-circle"></i> Error al cargar horarios.</p>';
    }
}

function seleccionarHora(btn, hora) {
    document.querySelectorAll('.ap-slot').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    horaSel = hora;
    document.getElementById('horaInput').value = hora;
    actualizarBoton();
}

function actualizarBoton() {
    const btn     = document.getElementById('btnConfirmar');
    const hint    = document.getElementById('btnHint');
    const resumen = document.getElementById('resumenCard');
    const listo   = fechaSel && horaSel;

    btn.disabled          = !listo;
    hint.style.display    = listo ? 'none' : 'block';
    resumen.style.display = listo ? 'block' : 'none';

    if (listo) {
        document.getElementById('resumenFecha').textContent = document.getElementById('fechaSelLabel').textContent;
        document.getElementById('resumenHora').textContent  = horaSel;
    }
}
</script>
@endpush
