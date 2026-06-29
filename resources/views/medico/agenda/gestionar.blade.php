@extends('layouts.medico')
@section('titulo', 'Gestionar Agenda')
@section('seccion_titulo', 'Gestionar Agenda')
@section('volver', route('medico.agenda.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <h1 class="page-header__title"><i class="fas fa-calendar-check"></i> Gestionar Agenda</h1>
        <a href="{{ route('medico.agenda.index') }}" class="btn-secondary">
            <i class="fas fa-calendar-alt"></i> Ver Calendario
        </a>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert--error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    {{-- FORMULARIO GENERAR SLOTS --}}
    <form method="POST" action="{{ route('medico.agenda.generar') }}" class="form-card" id="formGenerar">
        @csrf

        <h3 class="form-card__seccion"><i class="fas fa-wand-magic-sparkles"></i> Generar horarios masivos</h3>

        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:.875rem;color:#1e40af;display:flex;gap:20px;flex-wrap:wrap;">
            <span><i class="fas fa-clock"></i> Duración por cita: <strong>{{ $duracion }} min</strong></span>
            <span><i class="fas fa-calendar-check"></i> Máximo citas por día: <strong>{{ $maxCitas }}</strong></span>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Fecha inicio *</label>
                <input type="date" name="fecha_inicio" required min="{{ now()->format('Y-m-d') }}"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha fin *</label>
                <input type="date" name="fecha_fin" required min="{{ now()->format('Y-m-d') }}"
                       value="{{ now()->addDays(7)->format('Y-m-d') }}">
            </div>
        </div>

        <div class="form-group form-group--full">
            <label>Días de la semana</label>
            <div class="checkbox-grid">
                @foreach(['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'] as $val => $label)
                <label class="checkbox-item">
                    <input type="checkbox" name="{{ $val }}" value="1" {{ in_array($val, ['lunes','martes','miercoles','jueves','viernes']) ? 'checked' : '' }}>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        @php
            $generarSlots = function(string $inicio, string $fin) use ($duracion): array {
                $slots = [];
                $t = \Carbon\Carbon::createFromFormat('H:i', $inicio);
                $f = \Carbon\Carbon::createFromFormat('H:i', $fin);
                while ($t->lte($f)) {
                    $slots[$t->format('H:i:s')] = $t->format('H:i');
                    $t->addMinutes($duracion);
                }
                return $slots;
            };
            $manana = $generarSlots('08:00', '12:00');
            $tarde  = $generarSlots('13:00', '17:00');
        @endphp

        <div class="form-group form-group--full">
            <label>Horarios mañana <small style="color:#888; font-weight:400;">(cada {{ $duracion }} min)</small></label>
            <div class="checkbox-grid">
                @foreach($manana as $val => $label)
                <label class="checkbox-item">
                    <input type="checkbox" name="horarios_manana[]" value="{{ $val }}" class="horario-check"> {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-group form-group--full">
            <label>Horarios tarde <small style="color:#888; font-weight:400;">(cada {{ $duracion }} min)</small></label>
            <div class="checkbox-grid">
                @foreach($tarde as $val => $label)
                <label class="checkbox-item">
                    <input type="checkbox" name="horarios_tarde[]" value="{{ $val }}" class="horario-check"> {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        <div id="contador-horarios" style="border-radius:8px;padding:10px 16px;font-size:.875rem;display:flex;align-items:center;gap:10px;margin-bottom:8px;background:#f0fdf4;border:1px solid #86efac;color:#166534;">
            <i class="fas fa-check-circle"></i>
            <span id="contador-texto">0 horarios seleccionados — máximo <strong>{{ $maxCitas }}</strong> por día</span>
        </div>

        <div class="form-card__actions">
            <button type="submit" class="btn-success">
                <i class="fas fa-plus-circle"></i> Generar Horarios
            </button>
        </div>

        <script>
        (function () {
            var max = {{ $maxCitas }};
            var caja  = document.getElementById('contador-horarios');
            var texto = document.getElementById('contador-texto');

            function actualizar() {
                var total = document.querySelectorAll('.horario-check:checked').length;
                if (total === 0) {
                    caja.style.background  = '#f0fdf4';
                    caja.style.borderColor = '#86efac';
                    caja.style.color       = '#166534';
                    caja.querySelector('i').className = 'fas fa-check-circle';
                    texto.innerHTML = '0 horarios seleccionados — máximo <strong>' + max + '</strong> por día';
                } else if (total <= max) {
                    caja.style.background  = '#f0fdf4';
                    caja.style.borderColor = '#86efac';
                    caja.style.color       = '#166534';
                    caja.querySelector('i').className = 'fas fa-check-circle';
                    texto.innerHTML = '<strong>' + total + '</strong> horarios seleccionados de <strong>' + max + '</strong> máximo por día';
                } else {
                    caja.style.background  = '#fff7ed';
                    caja.style.borderColor = '#fdba74';
                    caja.style.color       = '#9a3412';
                    caja.querySelector('i').className = 'fas fa-triangle-exclamation';
                    texto.innerHTML = '<strong>' + total + '</strong> seleccionados — supera el máximo de <strong>' + max + '</strong>. Los horarios extras se omitirán al generar.';
                }
            }

            document.querySelectorAll('.horario-check').forEach(function(cb) {
                cb.addEventListener('change', actualizar);
            });
        })();
        </script>
    </form>

    {{-- SLOTS EXISTENTES --}}
    <div class="medico-section" style="margin-top:0">
        <h2 class="medico-section__title">
            <i class="fas fa-list"></i> Slots próximos
        </h2>

        @if($slots->isEmpty())
        <div class="medico-empty">
            <i class="fas fa-calendar-xmark"></i>
            <p>No tienes horarios generados aún.</p>
        </div>
        @else
        @foreach($slots as $fecha => $grupo)
        <div class="slots-grupo">
            <h4 class="slots-grupo__fecha">
                <i class="fas fa-calendar-day"></i>
                {{ \Carbon\Carbon::parse($fecha)->isoFormat('dddd D [de] MMMM') }}
            </h4>
            <div class="slots-lista">
                @foreach($grupo as $slot)
                <div class="slot-item slot-item--{{ $slot->estado }}">
                    <span class="slot-item__hora">{{ substr($slot->hora, 0, 5) }}</span>
                    <span class="slot-item__estado">
                        @if($slot->estado === 'disponible')
                            <i class="fas fa-circle-check" style="color:#28a745"></i> Disponible
                        @elseif($slot->estado === 'ocupado')
                            <i class="fas fa-user" style="color:#0078d7"></i>
                            {{ $slot->nombres ? $slot->nombres . ' ' . $slot->apellido1 : 'Ocupado' }}
                        @else
                            <i class="fas fa-ban" style="color:#dc3545"></i> {{ ucfirst($slot->estado) }}
                        @endif
                        @if($slot->es_emergencia)
                            <span class="badge-emergencia">🚨 EMERGENCIA</span>
                        @endif
                    </span>

                    @if($slot->estado === 'disponible')
                    <form method="POST" action="{{ route('medico.agenda.eliminar', $slot->id_agenda) }}"
                          onsubmit="return confirmarEliminarSlot(event)">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn action-btn--delete" style="padding:5px 10px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/agenda.css') }}">
@endpush

@push('scripts')
<script>
document.getElementById('formGenerar').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: '¿Generar horarios?',
        text: 'Se crearán los slots de agenda seleccionados.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

function confirmarEliminarSlot(event) {
    event.preventDefault();
    const form = event.target;
    Swal.fire({
        title: '¿Eliminar slot?',
        text: 'Este horario quedará eliminado de tu agenda.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6b7280'
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}
</script>
@endpush