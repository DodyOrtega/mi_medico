@extends('layouts.admin')
@section('titulo', 'Backup & Restauración')
@section('seccion_titulo', 'Backup & Restauración')

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-database"></i> Backup & Restauración</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:8px;">

        {{-- DESCARGAR BACKUP --}}
        <div class="form-card" style="display:flex;flex-direction:column;gap:16px;">
            <h3 class="form-card__seccion" style="margin-bottom:0;">
                <i class="fas fa-download"></i> Crear Backup
            </h3>
            <p style="color:#64748b;font-size:.9rem;line-height:1.6;">
                Genera un archivo <strong>.sql</strong> con toda la estructura y datos de la base de datos.
                El archivo se descarga directamente a tu computadora.
            </p>
            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 16px;font-size:.85rem;color:#166534;">
                <i class="fas fa-info-circle"></i>
                Guarda el archivo en un lugar seguro fuera del servidor para poder restaurarlo si es necesario.
            </div>
            <div style="margin-top:auto;">
                <a href="{{ route('admin.backup.descargar') }}"
                   class="btn-success"
                   style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                    <i class="fas fa-download"></i> Descargar Backup Ahora
                </a>
            </div>
        </div>

        {{-- RESTAURAR BACKUP --}}
        <div class="form-card" style="display:flex;flex-direction:column;gap:16px;">
            <h3 class="form-card__seccion" style="margin-bottom:0;">
                <i class="fas fa-upload"></i> Restaurar Backup
            </h3>
            <p style="color:#64748b;font-size:.9rem;line-height:1.6;">
                Sube un archivo <strong>.sql</strong> generado previamente desde este sistema.
                <strong style="color:#dc2626;">Esto reemplazará todos los datos actuales.</strong>
            </p>
            <div style="background:#fff7ed;border:1px solid #fdba74;border-radius:8px;padding:12px 16px;font-size:.85rem;color:#9a3412;">
                <i class="fas fa-triangle-exclamation"></i>
                Esta acción no se puede deshacer. Asegúrate de tener un backup reciente antes de restaurar.
            </div>
            <form method="POST" action="{{ route('admin.backup.restaurar') }}"
                  enctype="multipart/form-data" id="formRestaura"
                  style="display:flex;flex-direction:column;gap:12px;margin-top:auto;">
                @csrf
                <div class="form-group" style="margin:0;">
                    <label style="font-weight:600;font-size:.875rem;">Seleccionar archivo .sql</label>
                    <input type="file" name="backup_file" accept=".sql" required
                           style="margin-top:6px;width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;background:#f9fafb;">
                </div>
                <button type="button" class="action-btn action-btn--delete"
                        style="display:inline-flex;align-items:center;gap:8px;"
                        onclick="confirmarRestaura()">
                    <i class="fas fa-upload"></i> Restaurar Base de Datos
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function confirmarRestaura() {
    Swal.fire({
        title: '¿Restaurar base de datos?',
        html: 'Esta acción <strong>reemplazará todos los datos actuales</strong> con los del archivo seleccionado.<br><br>¿Estás seguro de continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-upload"></i> Sí, restaurar',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('formRestaura').submit();
        }
    });
}
</script>
@endsection
