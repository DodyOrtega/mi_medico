@extends('layouts.admin')

@section('titulo', 'Médicos')
@section('seccion_titulo', $nombreEspecialidad)
@section('volver', route('admin.dashboard'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                <i class="fas fa-user-md"></i> {{ $nombreEspecialidad }}
            </h1>
            <p class="page-header__sub">{{ $medicos->count() }} médico(s) {{ $modoEspecialidad ? 'en esta especialidad' : 'registrados' }}</p>
        </div>
        <a href="{{ route('admin.especialidades.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Especialidades
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
        <form method="GET" action="{{ route('admin.medicos.index') }}" class="search-form">
            @if($modoEspecialidad)
                <input type="hidden" name="especialidad_id" value="{{ $especialidadId }}">
            @endif
            <input type="text" name="busqueda" class="search-input"
                   placeholder="Buscar por nombre, apellido o cédula..."
                   value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Buscar</button>
            @if($busqueda)
            <a href="{{ route('admin.medicos.index', $modoEspecialidad ? ['especialidad_id' => $especialidadId] : []) }}" class="btn-clear">
                <i class="fas fa-times"></i> Limpiar
            </a>
            @endif
        </form>

        <a href="{{ route('admin.medicos.create', $modoEspecialidad ? ['especialidad_id' => $especialidadId] : []) }}" class="btn-add-big">
            <i class="fas fa-plus-circle"></i> Agregar Nuevo Médico
        </a>
    </div>

    @if($busqueda)
    <p class="search-info"><i class="fas fa-search"></i> Resultados para "{{ $busqueda }}" — {{ $medicos->count() }} médico(s)</p>
    @endif

    {{-- ═══ GRID DE MÉDICOS ═══ --}}
    @if($medicos->isEmpty())
    <div class="empty-state">
        <i class="fas fa-user-md"></i>
        @if($busqueda)
            <p>No se encontraron médicos para "{{ $busqueda }}"</p>
            <a href="{{ route('admin.medicos.index', $modoEspecialidad ? ['especialidad_id' => $especialidadId] : []) }}" class="btn-clear">
                <i class="fas fa-arrow-left"></i> Ver todos
            </a>
        @else
            <p>{{ $modoEspecialidad ? "No hay médicos en {$nombreEspecialidad}" : 'No hay médicos registrados' }}</p>
        @endif
    </div>
    @else
    <div class="medicos-grid">
        @foreach($medicos as $medico)
        @php
            $nombreCompleto = trim("{$medico->nombres} {$medico->apellido1} " . ($medico->apellido2 ?? ''));
            $activo = ($medico->estado ?? 'activo') === 'activo';
        @endphp
        <div class="medico-card {{ $activo ? 'medico-card--activo' : 'medico-card--inactivo' }}">
            <span class="estado-badge {{ $activo ? 'estado-badge--activo' : 'estado-badge--inactivo' }}">
                <i class="fas fa-{{ $activo ? 'check-circle' : 'ban' }}"></i> {{ $activo ? 'ACTIVO' : 'INACTIVO' }}
            </span>

            <div class="medico-card__icon"><i class="fas fa-user-md"></i></div>
            <h3 class="medico-card__nombre">{{ $nombreCompleto }}</h3>
            <div class="medico-card__especialidad">
                <i class="fas fa-star"></i> {{ $medico->especialidades ?: 'Sin especialidad' }}
            </div>
            <p class="medico-card__cedula"><i class="fas fa-id-card"></i> {{ $medico->cedula }}</p>

            @if($medico->anios_experiencia)
            <p class="medico-card__experiencia"><i class="fas fa-briefcase"></i> {{ $medico->anios_experiencia }} años exp.</p>
            @endif

            <div class="medico-card__actions">
                <a href="{{ route('admin.medicos.show', $medico->cedula) }}" class="action-btn action-btn--view">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('admin.medicos.edit', $medico->cedula) }}{{ $modoEspecialidad ? '?especialidad_id=' . $especialidadId : '' }}" class="action-btn action-btn--edit">
                    <i class="fas fa-edit"></i> Editar
                </a>

                <form method="POST" action="{{ route('admin.medicos.destroy', $medico->cedula) }}"
                      data-nombre="{{ $nombreCompleto }}">
                    @csrf
                    @method('DELETE')
                    @if($modoEspecialidad)
                        <input type="hidden" name="especialidad_id" value="{{ $especialidadId }}">
                    @endif
                    <button type="button" class="action-btn action-btn--delete"
                            onclick="confirmarEliminarMedico(this)">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>

            <div class="medico-card__actions" style="margin-top:8px;">
                <form method="POST" action="{{ route('admin.medicos.toggle-estado', $medico->cedula) }}"
                      data-nombre="{{ $nombreCompleto }}" data-activo="{{ $activo ? '1' : '0' }}">
                    @csrf
                    <button type="button" class="action-btn {{ $activo ? 'action-btn--delete' : 'action-btn--add' }}"
                            onclick="confirmarToggleEstado(this)">
                        <i class="fas fa-{{ $activo ? 'ban' : 'check' }}"></i> {{ $activo ? 'Desactivar' : 'Activar' }}
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

function confirmarToggleEstado(btn) {
    const form = btn.closest('form');
    const nombre = form.dataset.nombre;
    const activo = form.dataset.activo === '1';
    const accion = activo ? 'desactivar' : 'activar';
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} médico?`,
        html: `¿Estás seguro de ${accion} a <strong>${nombre}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: activo ? '#dc3545' : '#28a745',
    }).then(result => { if (result.isConfirmed) form.submit(); });
}
</script>
@endpush