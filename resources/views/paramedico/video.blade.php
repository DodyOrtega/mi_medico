@extends('layouts.paramedico')
@section('titulo', 'Videollamadas')
@section('seccion_titulo', 'Videollamadas')
@section('volver', route('paramedico.dashboard'))

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-video"></i> Videollamadas</h1>
            <p class="page-header__sub">{{ $pacientes->count() }} paciente(s) registrados</p>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="toolbar">
        <div class="search-form" style="max-width:400px;width:100%;">
            <input type="text" id="buscador" class="search-input"
                   placeholder="Buscar por nombre o cédula..."
                   oninput="filtrarPacientes(this.value)">
            <button class="btn-search" onclick="filtrarPacientes(document.getElementById('buscador').value)">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    @if($pacientes->isEmpty())
    <div class="medico-empty" style="padding:80px 20px;">
        <i class="fas fa-users" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:16px;"></i>
        <p style="color:#9ca3af;">No hay pacientes registrados.</p>
    </div>
    @else
    <div class="vc-grid" id="listaPacientes">
        @foreach($pacientes as $p)
        @php
            $ini1 = strtoupper(substr($p->nombres,  0, 1));
            $ini2 = strtoupper(substr($p->apellido1, 0, 1));
            $nombreCompleto = strtoupper("{$p->apellido1}") . ', ' . $p->nombres;
        @endphp
        <div class="vc-card"
             data-nombre="{{ strtolower($p->nombres . ' ' . $p->apellido1) }}"
             data-cedula="{{ $p->cedula }}">

            <div class="vc-card__body">
                <div class="vc-avatar vc-avatar--teal">{{ $ini1 }}{{ $ini2 }}</div>
                <div>
                    <div class="vc-card__nombre">{{ $nombreCompleto }}</div>
                    <div class="vc-card__cedula"><i class="fas fa-id-card"></i> {{ $p->cedula }}</div>
                </div>
            </div>

            <button class="vc-btn-llamar vc-btn-llamar--teal activo"
                    data-cedula="{{ $p->cedula }}"
                    onclick="iniciarLlamada(this)">
                <i class="fas fa-video"></i>
                <span>Llamar</span>
            </button>

        </div>
        @endforeach
    </div>

    <p id="sinResultados" class="medico-empty" style="display:none;padding:60px 20px;">
        <i class="fas fa-search" style="font-size:2rem;color:#d1d5db;display:block;margin-bottom:12px;"></i>
        Sin resultados para la búsqueda.
    </p>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<style>
.vc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    margin-top: 8px;
}
.vc-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,.05);
    display: flex;
    flex-direction: column;
}
.vc-card__body {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 16px 12px;
}
.vc-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    font-size: 1rem; font-weight: 800; color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.vc-avatar--teal { background: linear-gradient(135deg, #0e7490, #0891b2); }
.vc-card__nombre { font-weight: 700; font-size: .88rem; color: #1a2e44; }
.vc-card__cedula { font-size: .73rem; color: #9ca3af; margin-top: 2px; }

.vc-btn-llamar {
    margin: 0 16px 16px;
    padding: 10px;
    border: none; border-radius: 10px;
    font-weight: 700; font-size: .85rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
}
.vc-btn-llamar--teal.activo { background: linear-gradient(135deg,#0e7490,#0891b2); color:#fff; }
.vc-btn-llamar--teal.activo:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(14,116,144,.35); }

.vc-card.oculto { display: none; }
</style>
@endpush

@push('scripts')
<script>
function filtrarPacientes(q) {
    const term = q.toLowerCase().trim();
    let visibles = 0;
    document.querySelectorAll('#listaPacientes .vc-card').forEach(card => {
        const coincide = !term
            || card.dataset.nombre.includes(term)
            || card.dataset.cedula.includes(term);
        card.classList.toggle('oculto', !coincide);
        if (coincide) visibles++;
    });
    const sinRes = document.getElementById('sinResultados');
    if (sinRes) sinRes.style.display = visibles === 0 ? 'block' : 'none';
}

async function iniciarLlamada(btn) {
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
        btn.innerHTML = '<i class="fas fa-video"></i> <span>Llamar</span>';
        alert('Error al iniciar la llamada.');
        return;
    }
    window.location.href = '/video/sala/' + resp.room;
}
</script>
@endpush
