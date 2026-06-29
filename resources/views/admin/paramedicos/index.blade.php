@extends('layouts.admin')

@section('titulo', 'Paramédicos')
@section('seccion_titulo', 'Gestión de Paramédicos')
@section('volver', route('admin.dashboard'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                <i class="fas fa-user-md"></i> Paramédicos
            </h1>
            <p class="page-header__sub">{{ $paramedicos->count() }} paramédico(s) registrados</p>
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

    {{-- ═══ BÚSQUEDA + AGREGAR ═══ --}}
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.paramedicos.index') }}" class="search-form">
            <input type="text" name="busqueda" class="search-input"
                   placeholder="Buscar por nombre, apellido o cédula..."
                   value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Buscar</button>
            @if($busqueda)
            <a href="{{ route('admin.paramedicos.index') }}" class="btn-clear">
                <i class="fas fa-times"></i> Limpiar
            </a>
            @endif
        </form>

        <a href="{{ route('admin.paramedicos.create') }}" class="btn-add-big">
            <i class="fas fa-plus-circle"></i> Agregar Nuevo Paramédico
        </a>
    </div>

    @if($busqueda)
    <p class="search-info"><i class="fas fa-search"></i> Resultados para "{{ $busqueda }}" — {{ $paramedicos->count() }} resultado(s)</p>
    @endif

    {{-- ═══ GRID DE PARAMÉDICOS ═══ --}}
    @if($paramedicos->isEmpty())
    <div class="empty-state">
        <i class="fas fa-user-md"></i>
        @if($busqueda)
            <p>No se encontraron paramédicos para "{{ $busqueda }}"</p>
            <a href="{{ route('admin.paramedicos.index') }}" class="btn-clear">
                <i class="fas fa-arrow-left"></i> Ver todos
            </a>
        @else
            <p>No hay paramédicos registrados</p>
        @endif
    </div>
    @else
    <div class="medicos-grid">
        @foreach($paramedicos as $paramedico)
        @php
            $nombreCompleto = trim("{$paramedico->nombres} {$paramedico->apellido1} " . ($paramedico->apellido2 ?? ''));
            $activo = ($paramedico->estado ?? 'activo') === 'activo';
        @endphp
        <div class="medico-card {{ $activo ? 'medico-card--activo' : 'medico-card--inactivo' }}">
            <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
            </span>

            <div class="medico-card__icon"><i class="fas fa-user-md"></i></div>
            <h3 class="medico-card__nombre">{{ $nombreCompleto }}</h3>
            <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $paramedico->cedula ?: 'Sin cédula' }}</p>
            <p class="medico-card__cedula"><i class="fas fa-envelope"></i> {{ $paramedico->correo ?: 'Sin correo' }}</p>

            @if($paramedico->anios_experiencia)
            <p class="medico-card__experiencia"><i class="fas fa-briefcase"></i> {{ $paramedico->anios_experiencia }} años exp.</p>
            @endif

            <div class="medico-card__actions">
                <a href="{{ route('admin.paramedicos.edit', $paramedico->cedula) }}" class="action-btn action-btn--edit">
                    <i class="fas fa-edit"></i> Editar
                </a>

                <form method="POST" action="{{ route('admin.paramedicos.destroy', $paramedico->cedula) }}"
                      data-nombre="{{ $nombreCompleto }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="action-btn action-btn--delete"
                            onclick="confirmarEliminarParamedico(this)">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
@endpush

@push('scripts')
<script>
function confirmarEliminarParamedico(btn) {
    const form = btn.closest('form');
    const nombre = form.dataset.nombre;
    Swal.fire({
        title: '¿Eliminar paramédico?',
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