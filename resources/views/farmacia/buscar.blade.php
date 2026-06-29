@extends('layouts.farmacia')
@section('titulo', 'Farmacia — Consulta de Recetas')
@section('seccion_titulo', 'Farmacia')

@push('styles')
<style>
@media print {
    .farm-topbar, .farm-footer, .farm-section, .no-print { display: none !important; }
    .farm-main { padding: 0 !important; max-width: 100% !important; }
    .print-header { display: flex !important; }
    body { background: #fff !important; }
    .farm-receta { box-shadow: none !important; border: 1px solid #ddd !important; }
    .farm-paciente { box-shadow: none !important; }
}
.print-header {
    display: none;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 16px;
    margin-bottom: 20px;
    border-bottom: 2px solid #0078d7;
}
.print-header img { height: 60px; }
.print-header__info { text-align: right; font-size: .8rem; color: #555; }
.print-header__info strong { display: block; font-size: 1rem; color: #1a2e44; }
</style>
@endpush

@section('contenido')

    {{-- Encabezado solo visible al imprimir --}}
    <div class="print-header">
        <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
        <div class="print-header__info">
            <strong>MiMédico — Farmacia</strong>
            Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="farm-section no-print">
        <h3 class="farm-section__title"><i class="fas fa-search"></i> Buscar Paciente</h3>
        <form method="GET" action="{{ route('farmacia.buscar') }}" class="farm-search-form" role="search">
            <input type="text" name="cedula" class="farm-search-input"
                   placeholder="Ingrese cédula del paciente (10 dígitos)"
                   value="{{ $cedula ?? '' }}"
                   pattern="[0-9]{10}" maxlength="10" required
                   autocomplete="off" autofocus>
            <button type="submit" class="farm-search-btn">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>
        @if($cedula && !$error)
        <div style="margin-top:10px;">
            <a href="{{ route('farmacia.buscar') }}" style="font-size:.82rem;color:#0078d7;text-decoration:none;">
                <i class="fas fa-times-circle"></i> Limpiar búsqueda
            </a>
        </div>
        @endif
    </div>

    {{-- ERROR --}}
    @if($error)
    <div class="farm-alert-error no-print" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Paciente no encontrado.</strong> {{ $error }}
            <br><small>Verifique que la cédula sea correcta y que el paciente esté registrado.</small>
        </div>
    </div>
    @endif

    @if($paciente)

    {{-- TARJETA PACIENTE --}}
    <div class="farm-paciente">
        <div class="farm-paciente__avatar">
            {{ strtoupper(substr($paciente->nombres,0,1).substr($paciente->apellido1,0,1)) }}
        </div>
        <div>
            <div class="farm-paciente__nombre">
                {{ trim($paciente->nombres.' '.$paciente->apellido1.' '.($paciente->apellido2??'')) }}
            </div>
            <div class="farm-paciente__badges">
                <span class="farm-badge"><i class="fas fa-id-card"></i> {{ $paciente->cedula }}</span>
                @if(isset($paciente->edad))
                <span class="farm-badge"><i class="fas fa-calendar"></i> {{ $paciente->edad }} años</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ÚLTIMA RECETA --}}
    @if($receta)

    <div style="display:flex;align-items:center;justify-content:space-between;margin:20px 0 12px;">
        <h3 class="farm-section__title" style="margin:0;">
            <i class="fas fa-pills"></i> Última Receta Médica
        </h3>
        <button onclick="window.print()" class="farm-btn farm-btn--print no-print" style="margin:0;">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="farm-receta">

        <div class="farm-receta__header">
            <div class="farm-receta__titulo">RECETA MÉDICA</div>
            <span class="farm-receta__fecha">
                <i class="fas fa-calendar-alt"></i>
                {{ \Carbon\Carbon::parse($receta->fecha_hora)->format('d/m/Y H:i') }}
            </span>
        </div>

        <div class="farm-info-block farm-info-block--medico">
            <div class="farm-info-block__label"><i class="fas fa-user-md"></i> Médico Tratante</div>
            <div class="farm-info-block__content">
                <strong>Dr. {{ $receta->medico_nombres }} {{ $receta->medico_apellido1 }}</strong>
                @if($receta->especialidad)
                <div style="font-size:.82rem;color:#888;margin-top:3px;">{{ $receta->especialidad }}</div>
                @endif
            </div>
        </div>

        @if($receta->diagnostico)
        <div class="farm-info-block farm-info-block--diag">
            <div class="farm-info-block__label"><i class="fas fa-stethoscope"></i> Diagnóstico</div>
            <div class="farm-info-block__content">{{ $receta->diagnostico }}</div>
        </div>
        @endif

        <div class="farm-info-block farm-info-block--receta">
            <div class="farm-info-block__label"><i class="fas fa-pills"></i> Medicación Prescrita</div>
            <div class="farm-info-block__content" style="white-space:pre-line;">{{ $receta->receta }}</div>
        </div>

        <div class="farm-firma">
            _________________________
            <strong>Dr. {{ $receta->medico_nombres }} {{ $receta->medico_apellido1 }}</strong>
            <span style="font-size:.78rem;">Médico Tratante</span>
        </div>

    </div>

    @else
    <div class="farm-section">
        <div class="farm-empty">
            <i class="fas fa-prescription"></i>
            <p>Este paciente no tiene recetas médicas registradas.</p>
        </div>
    </div>
    @endif

    @elseif(!$cedula)
    <div style="text-align:center;padding:80px 20px;">
        <i class="fas fa-prescription" style="font-size:4rem;color:#bfdbfe;display:block;margin-bottom:16px;"></i>
        <h3 style="color:#1a2e44;margin-bottom:8px;font-size:1.2rem;">Consulta de Recetas Médicas</h3>
        <p style="color:#9ca3af;font-size:.9rem;">Ingrese la cédula del paciente para ver su última receta</p>
    </div>
    @endif

@endsection
