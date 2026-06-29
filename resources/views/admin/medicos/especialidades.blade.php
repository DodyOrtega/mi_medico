@extends('layouts.admin')

@section('titulo', 'Especialidades Médicas')
@section('seccion_titulo', 'Especialidades Médicas')
@section('volver', route('admin.dashboard'))

@section('contenido')

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                <i class="fas fa-stethoscope"></i> Especialidades Médicas
            </h1>
            <p class="page-header__sub">Gestiona las especialidades y accede a los médicos de cada una</p>
        </div>
        <button type="button" class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
            <i class="fas fa-plus"></i> Nueva Especialidad
        </button>
    </div>

    @if(session('exito'))
    <div class="alert alert--success">
        <i class="fas fa-check-circle"></i> {{ session('exito') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert--error">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
    @endif

    @if($especialidades->isEmpty())
    <div class="empty-state">
        <i class="fas fa-stethoscope"></i>
        <p>Aún no hay especialidades registradas.</p>
    </div>
    @else
    <div class="especialidades-grid">
        @foreach($especialidades as $esp)
        <div class="especialidad-card">
            <div class="especialidad-card__icon">
                <i class="fas fa-{{ $esp->icono }}"></i>
            </div>
            <h3 class="especialidad-card__title">{{ $esp->nombre }}</h3>
            <p class="especialidad-card__formacion">
                {{ $esp->formacion_academica ?: 'Sin formación especificada' }}
            </p>
            <span class="especialidad-card__contador">
                <i class="fas fa-user-md"></i> {{ $esp->total_medicos }} médico(s)
            </span>

            <div class="especialidad-card__actions">
                <div class="action-row">
                    <a href="{{ route('admin.medicos.create', ['especialidad_id' => $esp->id_especialidad]) }}" class="action-btn action-btn--add">
                        <i class="fas fa-plus"></i> Agregar Médico
                    </a>
                    <a href="{{ route('admin.medicos.index', ['especialidad_id' => $esp->id_especialidad]) }}" class="action-btn action-btn--view">
                        <i class="fas fa-eye"></i> Ver Médicos
                    </a>
                </div>
                <div class="action-row">
                    <button type="button" class="action-btn action-btn--edit"
                            onclick='editarEspecialidad({{ $esp->id_especialidad }}, @json($esp->nombre), @json($esp->formacion_academica))'>
                        <i class="fas fa-edit"></i> Editar
                    </button>

                    <form method="POST" action="{{ route('admin.especialidades.destroy', $esp->id_especialidad) }}"
                          data-nombre="{{ $esp->nombre }}" data-medicos="{{ $esp->total_medicos }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="action-btn action-btn--delete"
                                onclick="confirmarEliminarEspecialidad(this)"
                                @if($esp->total_medicos > 0) disabled title="No se puede eliminar: tiene médicos asociados" @endif>
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ═══ MODAL: NUEVA ESPECIALIDAD ═══ --}}
<div id="addModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-box__header">
            <h2><i class="fas fa-plus-circle" style="color:#28a745"></i> Nueva Especialidad</h2>
            <button type="button" class="modal-box__close" onclick="document.getElementById('addModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.especialidades.store') }}" id="addForm">
            @csrf
            <div class="form-group">
                <label>Nombre de la Especialidad *</label>
                <input type="text" name="nombre_especialidad" required placeholder="Ej: Cardiología, Pediatría...">
            </div>
            <div class="form-group">
                <label>Formación Académica (opcional)</label>
                <input type="text" name="formacion_academica" placeholder="Ej: 4 años de residencia">
            </div>
            <div class="modal-box__actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Agregar</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: EDITAR ESPECIALIDAD ═══ --}}
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-box__header">
            <h2><i class="fas fa-edit" style="color:#f59e0b"></i> Editar Especialidad</h2>
            <button type="button" class="modal-box__close" onclick="document.getElementById('editModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nombre de la Especialidad *</label>
                <input type="text" name="nombre_especialidad" id="edit_nombre" required>
            </div>
            <div class="form-group">
                <label>Formación Académica (opcional)</label>
                <input type="text" name="formacion_academica" id="edit_formacion">
            </div>
            <div class="modal-box__actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
@endpush

@push('scripts')
<script>
document.getElementById('addForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Agregar especialidad?',
        text: 'Se registrará la nueva especialidad.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Guardar cambios?',
        text: 'Se actualizará la especialidad.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

function editarEspecialidad(id, nombre, formacion) {
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_formacion').value = formacion || '';
    document.getElementById('editForm').action = `/admin/especialidades/${id}`;
    document.getElementById('editModal').style.display = 'flex';
}

function confirmarEliminarEspecialidad(btn) {
    const form = btn.closest('form');
    const nombre = form.dataset.nombre;
    const totalMedicos = parseInt(form.dataset.medicos);
    if (totalMedicos > 0) return;
    Swal.fire({
        title: '¿Eliminar especialidad?',
        html: `¿Estás seguro de eliminar <strong>${nombre}</strong>?<br><span style="color:#888;font-size:.85rem">Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(result => { if (result.isConfirmed) form.submit(); });
}

window.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }
});
</script>
@endpush