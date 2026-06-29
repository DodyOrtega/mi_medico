{{-- ═══ emergencias.blade.php ═══ --}}
@extends('layouts.paramedico')
@section('titulo','Emergencias')
@section('seccion_titulo','Emergencias')
@section('volver', route('paramedico.dashboard'))

@section('contenido')
<div class="medicos-page">
    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-triangle-exclamation"></i> Emergencias</h1>
        <p class="page-header__sub">{{ $emergencias->count() }} emergencia(s) activa(s)</p>
    </div>

    @if(session('exito'))<div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>@endif
    @if(session('error'))<div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    @if($emergencias->isEmpty())
    <div class="empty-state">
        <i class="fas fa-circle-check" style="color:#28a745;"></i>
        <p>No hay emergencias activas en este momento.</p>
    </div>
    @else
    <div class="citas-lista-completa">
        @foreach($emergencias as $emerg)
        @php $disponible = $emerg->estado === 'disponible'; @endphp
        <div class="cita-completa cita-completa--futura" style="border-left-color:{{ $disponible ? '#dc3545' : '#9ca3af' }};">
            <div class="cita-completa__fecha">
                <span class="cita-completa__dia">{{ \Carbon\Carbon::parse($emerg->fecha)->format('d') }}</span>
                <span class="cita-completa__mes">{{ \Carbon\Carbon::parse($emerg->fecha)->isoFormat('MMM') }}</span>
                <span class="cita-completa__hora" style="color:#dc3545;">{{ substr($emerg->hora,0,5) }}</span>
            </div>
            <div class="cita-completa__body">
                <span class="cita-completa__paciente">
                    {{ $emerg->nombres ? $emerg->nombres . ' ' . $emerg->apellido1 : 'Sin paciente asignado' }}
                </span>
                <span class="cita-completa__motivo">🚨 {{ $emerg->motivo_emergencia ?: 'Sin motivo especificado' }}</span>
                @if($emerg->telefono)
                <span class="cita-completa__diag"><i class="fas fa-phone"></i> {{ $emerg->telefono }}</span>
                @endif
            </div>
            <div class="cita-completa__side">
                @if($disponible)
                <button class="btn-primary" onclick="atenderEmergencia({{ $emerg->id_agenda }}, this)"
                        style="padding:8px 16px; font-size:.82rem;">
                    <i class="fas fa-hand-holding-medical"></i> Atender
                </button>
                @else
                <span class="estado-badge estado-badge--inactivo" style="position:static;display:inline-flex;">
                    En atención
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/citas.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pacientes.css') }}">
@endpush

@push('scripts')
<script>
function atenderEmergencia(idAgenda, btn) {
    Swal.fire({
        title: '¿Atender esta emergencia?',
        text: 'Serás asignado como responsable de esta emergencia.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, atender',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0078d7',
    }).then((result) => {
        if (!result.isConfirmed) return;
        fetch('{{ route("paramedico.emergencias.atender") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({id_agenda: idAgenda})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.room) {
                window.location.href = '/video/sala/' + data.room;
            } else if (!data.success) {
                Swal.fire('Error', data.message || 'Esta emergencia ya fue atendida.', 'error');
            }
        });
    });
}
</script>
@endpush