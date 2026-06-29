@extends('layouts.paciente')

@section('titulo', 'Mis Citas')
@section('seccion_titulo', 'Mis Citas')
@section('volver', route('paciente.dashboard'))

@section('contenido')

    <div class="page-header">
        <div>
            <h2 class="page-header__title"><i class="fas fa-calendar-alt"></i> Mis Citas Pendientes</h2>
            <p class="page-header__sub">Citas programadas a partir de hoy</p>
        </div>
        <a href="{{ route('paciente.citas.agendar') }}" class="btn-paciente-primary">
            <i class="fas fa-calendar-plus"></i> Nueva Cita
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    @if($citas->isEmpty())
        <div class="paciente-section">
            <div class="paciente-empty">
                <i class="fas fa-calendar-times" style="color:#dee2e6;"></i>
                <p>No tienes citas pendientes</p>
                <a href="{{ route('paciente.citas.agendar') }}" class="btn-paciente-primary">
                    <i class="fas fa-calendar-plus"></i> Agendar Cita
                </a>
            </div>
        </div>
    @else
        <div class="citas-grid">
            @foreach($citas as $cita)
            <div class="cita-card">
                <div class="cita-card__header">
                    <span class="{{ $cita->tipo_cita === 'virtual' ? 'badge-virtual' : 'badge-presencial' }}">
                        <i class="fas fa-{{ $cita->tipo_cita === 'virtual' ? 'video' : 'hospital' }}"></i>
                        {{ $cita->tipo_cita === 'virtual' ? 'Teleconsulta' : 'Presencial' }}
                    </span>
                    <span class="cita-card__fecha">
                        <i class="fas fa-clock"></i>
                        {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div class="cita-card__body">
                    <div class="cita-medico">
                        <div class="cita-medico__avatar">
                            {{ strtoupper(substr($cita->medico_nombres,0,1).substr($cita->medico_apellido1,0,1)) }}
                        </div>
                        <div>
                            <div class="cita-medico__nombre">Dr. {{ $cita->medico_nombres }} {{ $cita->medico_apellido1 }}</div>
                            @if($cita->especialidad)
                                <div class="cita-medico__esp"><i class="fas fa-stethoscope"></i> {{ $cita->especialidad }}</div>
                            @endif
                        </div>
                    </div>

                    @if($cita->motivo_consulta)
                    <div class="cita-motivo">
                        <i class="fas fa-notes-medical"></i> {{ $cita->motivo_consulta }}
                    </div>
                    @endif
                </div>

                <div class="cita-card__footer">
                    <a href="{{ route('paciente.citas.detalle', $cita->id_cita) }}" class="btn-ver">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    @if($cita->tipo_cita === 'virtual')
                        <a href="{{ route('paciente.teleconsultas') }}" class="btn-video">
                            <i class="fas fa-video"></i> Unirse
                        </a>
                    @endif
                    <button class="btn-cancelar" onclick="confirmarCancelacion({{ $cita->id_cita }}, false)">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    @endif

@endsection

@push('scripts')
<script>
function confirmarCancelacion(id, isAgenda) {
    Swal.fire({
        title: '¿Cancelar esta cita?',
        text: 'Esta acción liberará el horario. No se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch('{{ route("paciente.citas.cancelar") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_cita: id, is_agenda: isAgenda })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ title: 'Cancelada', text: data.message, icon: 'success', confirmButtonColor: '#0078d7' })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
    });
}
</script>
@endpush