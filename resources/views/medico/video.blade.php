@extends('layouts.medico')
@section('titulo', 'Videollamadas')
@section('seccion_titulo', 'Videollamadas')
@section('volver', route('medico.dashboard'))

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-video"></i> Teleconsultas de hoy</h1>
            <p class="page-header__sub">{{ now()->isoFormat('dddd D [de] MMMM') }} — {{ $pacientes->count() }} cita(s) virtual(es)</p>
        </div>
    </div>

    @if($pacientes->isEmpty())
    <div class="medico-empty" style="padding:80px 20px;">
        <i class="fas fa-video-slash" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:16px;"></i>
        <p style="color:#9ca3af;font-size:1rem;">No tienes videoconsultas programadas para hoy.</p>
    </div>
    @else
    <div class="vc-grid">
        @foreach($pacientes as $p)
        @php
            $ini1    = strtoupper(substr($p->nombres,  0, 1));
            $ini2    = strtoupper(substr($p->apellido1, 0, 1));
            $horaFmt = \Carbon\Carbon::parse($p->fecha_hora)->format('H:i');
            $horaIso = \Carbon\Carbon::parse($p->fecha_hora)->format('Y-m-d\TH:i:s');
        @endphp
        <div class="vc-card">

            {{-- Franja de hora --}}
            <div class="vc-card__hora-bar">
                <i class="fas fa-clock"></i> {{ $horaFmt }}
            </div>

            {{-- Avatar + datos --}}
            <div class="vc-card__body">
                <div class="vc-avatar">{{ $ini1 }}{{ $ini2 }}</div>
                <div>
                    <div class="vc-card__nombre">{{ $p->nombres }} {{ $p->apellido1 }}</div>
                    <div class="vc-card__cedula"><i class="fas fa-id-card"></i> {{ $p->cedula }}</div>
                </div>
            </div>

            {{-- Botón llamar --}}
            <button class="vc-btn-llamar"
                    data-hora-cita="{{ $horaIso }}"
                    data-cedula="{{ $p->cedula }}"
                    data-nombre="{{ addslashes($p->nombres . ' ' . $p->apellido1) }}"
                    onclick="iniciarLlamada(this)">
                <i class="fas fa-video"></i>
                <span class="vc-btn__txt">Llamar</span>
            </button>

        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<style>
.vc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
    margin-top: 8px;
}
.vc-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    display: flex;
    flex-direction: column;
}
.vc-card__hora-bar {
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.vc-card__body {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 16px 12px;
}
.vc-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    color: #fff; font-size: 1.1rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.vc-card__nombre { font-weight: 700; font-size: .95rem; color: #1a2e44; }
.vc-card__cedula { font-size: .75rem; color: #9ca3af; margin-top: 2px; }

.vc-btn-llamar {
    margin: 0 16px 16px;
    padding: 11px;
    border: none; border-radius: 10px;
    font-weight: 700; font-size: .88rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
}
.vc-btn-llamar.activo {
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    color: #fff;
}
.vc-btn-llamar.activo:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(124,58,237,.4);
}
.vc-btn-llamar.inactivo {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}
.vc-btn-llamar:disabled { opacity: .7; }
</style>
@endpush

@push('scripts')
<script>
function verificarBotones() {
    const ahora = Date.now();
    document.querySelectorAll('.vc-btn-llamar').forEach(btn => {
        const horaCita = new Date(btn.dataset.horaCita).getTime();
        const activo   = ahora >= horaCita;
        btn.classList.toggle('activo',   activo);
        btn.classList.toggle('inactivo', !activo);
        btn.disabled = !activo;
        if (!activo) {
            const min = Math.ceil((horaCita - ahora) / 60000);
            btn.querySelector('.vc-btn__txt').textContent =
                min < 60 ? `Disponible en ${min} min` : `Disponible en ${Math.ceil(min/60)}h`;
        } else {
            btn.querySelector('.vc-btn__txt').textContent = 'Llamar';
        }
    });
}

async function iniciarLlamada(btn) {
    if (btn.disabled) return;
    const cedulaPaciente = btn.dataset.cedula;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Conectando...';

    const resp = await fetch('/video/iniciar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ cedula_paciente: cedulaPaciente })
    }).then(r => r.json()).catch(() => null);

    if (!resp || !resp.room) {
        btn.disabled = false;
        verificarBotones();
        alert('Error al iniciar la llamada.');
        return;
    }
    window.location.href = '/video/sala/' + resp.room;
}

document.addEventListener('DOMContentLoaded', function () {
    verificarBotones();
    setInterval(verificarBotones, 30 * 1000);
});
</script>
@endpush
