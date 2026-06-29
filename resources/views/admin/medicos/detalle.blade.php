@extends('layouts.admin')

@section('titulo', 'Detalle del Médico')
@section('seccion_titulo', 'Detalle del Médico')
@section('volver', route('admin.medicos.index'))

@section('contenido')

@php
    $nombreCompleto = trim("{$medico->nombres} {$medico->apellido1} " . ($medico->apellido2 ?? ''));
    $activo = ($medico->estado ?? 'activo') === 'activo';
@endphp

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-user-md"></i> Detalle del Médico</h1>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.medicos.edit', $medico->cedula) }}" class="btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form method="POST" action="{{ route('admin.medicos.destroy', $medico->cedula) }}"
                  data-nombre="{{ $nombreCompleto }}">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmarEliminarMedico(this)"
                        style="background:#dc3545;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="detalle-card">

        <div class="detalle-card__header">
            <div class="detalle-card__avatar"><i class="fas fa-user-md"></i></div>
            <div>
                <h2 class="detalle-card__nombre">{{ $nombreCompleto }}</h2>
                <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                    <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
                </span>
            </div>
        </div>

        <div class="detalle-grid">
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-id-card"></i> Cédula</span>
                <span class="detalle-item__value">{{ $medico->cedula }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-envelope"></i> Correo</span>
                <span class="detalle-item__value">{{ $medico->correo }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="detalle-item__value">{{ $medico->telefono ?: 'No registrado' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-cake-candles"></i> Fecha de nacimiento</span>
                <span class="detalle-item__value">{{ $medico->fecha_nac ? \Carbon\Carbon::parse($medico->fecha_nac)->format('d/m/Y') : 'No registrada' }}</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-briefcase"></i> Años de experiencia</span>
                <span class="detalle-item__value">{{ $medico->anios_experiencia ?? 0 }} años</span>
            </div>
            <div class="detalle-item">
                <span class="detalle-item__label"><i class="fas fa-calendar-check"></i> Citas atendidas</span>
                <span class="detalle-item__value">{{ $totalCitas }}</span>
            </div>
        </div>

        <h3 class="detalle-card__subtitulo"><i class="fas fa-star"></i> Especialidades</h3>
        @if($especialidades->isEmpty())
        <p class="detalle-empty">Sin especialidades registradas.</p>
        @else
        <div class="detalle-tags">
            @foreach($especialidades as $esp)
            <span class="detalle-tag">
                {{ $esp->nombre }}
                @if($esp->registro_senescyt)
                    <small>· SENESCYT: {{ $esp->registro_senescyt }}</small>
                @endif
            </span>
            @endforeach
        </div>
        @endif

        <h3 class="detalle-card__subtitulo"><i class="fas fa-graduation-cap"></i> Títulos Académicos</h3>
        @if($titulos->isEmpty())
        <p class="detalle-empty">Sin títulos registrados.</p>
        @else
        <ul class="detalle-lista">
            @foreach($titulos as $titulo)
            <li>
                <i class="fas fa-certificate"></i> {{ $titulo->nombre_titulo }}
                <small>({{ \Carbon\Carbon::parse($titulo->fecha_registro)->format('d/m/Y') }})</small>
            </li>
            @endforeach
        </ul>
        @endif

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
@endpush

@push('scripts')
<script>
function confirmarEliminarMedico(btn) {
    const form = btn.closest('form');
    const nombre = form.dataset.nombre;
    Swal.fire({
        title: '¿Eliminar médico?',
        html: `¿Estás seguro de eliminar a <strong>${nombre}</strong>?<br><span style="color:#888;font-size:.85rem">Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(result => { if (result.isConfirmed) form.submit(); });
}
</script>
@endpush