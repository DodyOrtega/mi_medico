@extends('layouts.laboratorio')
@section('titulo', 'Laboratorio — Orden de Exámenes')
@section('seccion_titulo', 'Laboratorio Clínico')

@push('styles')
<style>
@media print {
    .lab-topbar, .lab-footer, .lab-section, .no-print { display: none !important; }
    .lab-main { padding: 0 !important; max-width: 100% !important; }
    .print-header { display: flex !important; }
    body { background: #fff !important; }
    .lab-paciente { box-shadow: none !important; }
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
            <strong>MiMédico — Laboratorio Clínico</strong>
            Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="lab-section no-print">
        <h3 class="lab-section__title"><i class="fas fa-search"></i> Buscar Paciente</h3>
        <form method="GET" action="{{ route('laboratorio.buscar') }}" class="lab-search-form" role="search">
            <input type="text" name="cedula" class="lab-search-input"
                   placeholder="Ingrese cédula del paciente (10 dígitos)"
                   value="{{ $cedula ?? '' }}"
                   pattern="[0-9]{10}" maxlength="10" required
                   autocomplete="off" autofocus>
            <button type="submit" class="lab-search-btn">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>
        @if($cedula && !$error)
        <div style="margin-top:10px;">
            <a href="{{ route('laboratorio.buscar') }}" style="font-size:.82rem;color:#0078d7;text-decoration:none;">
                <i class="fas fa-times-circle"></i> Limpiar búsqueda
            </a>
        </div>
        @endif
    </div>

    {{-- ERROR --}}
    @if($error)
    <div class="lab-alert-error no-print" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Paciente no encontrado.</strong> {{ $error }}
            <br><small>Verifique que la cédula sea correcta y que el paciente esté registrado.</small>
        </div>
    </div>
    @endif

    @if($paciente)

    {{-- TARJETA PACIENTE --}}
    <div class="lab-paciente">
        <div class="lab-paciente__avatar">
            {{ strtoupper(substr($paciente->nombres,0,1).substr($paciente->apellido1,0,1)) }}
        </div>
        <div>
            <div class="lab-paciente__nombre">
                {{ trim($paciente->nombres.' '.$paciente->apellido1.' '.($paciente->apellido2??'')) }}
            </div>
            <div class="lab-paciente__badges">
                <span class="lab-badge"><i class="fas fa-id-card"></i> {{ $paciente->cedula }}</span>
                @if(isset($paciente->edad))
                <span class="lab-badge"><i class="fas fa-calendar"></i> {{ $paciente->edad }} años</span>
                @endif
                @if($paciente->genero)
                <span class="lab-badge"><i class="fas fa-venus-mars"></i>
                    {{ $paciente->genero === 'M' ? 'Masculino' : ($paciente->genero === 'F' ? 'Femenino' : 'Otro') }}
                </span>
                @endif
                @if($paciente->tipo_sangre)
                <span class="lab-badge lab-badge--blue"><i class="fas fa-tint"></i> {{ $paciente->tipo_sangre }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ÚLTIMA ORDEN --}}
    @if($examen)

    <div style="display:flex;align-items:center;justify-content:space-between;margin:20px 0 12px;">
        <h3 class="lab-section__title" style="margin:0;">
            <i class="fas fa-flask"></i> Última Orden de Exámenes
        </h3>
        <button onclick="window.print()" class="no-print" style="padding:8px 16px;background:#0078d7;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px;box-shadow:0 1px 4px rgba(0,0,0,.05);">

        {{-- Médico y fecha --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #e8f4fb;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#0078d7,#005fa3);color:#fff;font-size:.9rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ strtoupper(substr($examen->medico_nombres,0,1).substr($examen->medico_apellido1,0,1)) }}
                </div>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1a2e44;">
                        Dr. {{ $examen->medico_nombres }} {{ $examen->medico_apellido1 }}
                    </div>
                    @if($examen->especialidad)
                    <div style="font-size:.75rem;color:#6b7280;">{{ $examen->especialidad }}</div>
                    @endif
                </div>
            </div>
            <span style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-calendar-alt"></i>
                {{ \Carbon\Carbon::parse($examen->fecha_hora)->format('d/m/Y H:i') }}
            </span>
        </div>

        @if($examen->diagnostico)
        <div style="background:#f8fafc;border-left:3px solid #0078d7;padding:10px 14px;border-radius:0 8px 8px 0;margin-bottom:14px;font-size:.85rem;color:#374151;">
            <span style="font-weight:700;font-size:.72rem;text-transform:uppercase;color:#0078d7;display:block;margin-bottom:4px;">
                <i class="fas fa-stethoscope"></i> Diagnóstico
            </span>
            {{ $examen->diagnostico }}
        </div>
        @endif

        @if($examen->examen_laboratorio)
        <div style="margin-bottom:14px;">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#0078d7;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-flask"></i> Orden de Laboratorio
            </div>
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px 16px;font-size:.9rem;color:#1a2e44;white-space:pre-line;line-height:1.7;">{{ $examen->examen_laboratorio }}</div>
        </div>
        @endif

        @if($examen->examen_imagenes)
        <div>
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#7c3aed;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-x-ray"></i> Orden de Imágenes
            </div>
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:14px 16px;font-size:.9rem;color:#1a2e44;white-space:pre-line;line-height:1.7;">{{ $examen->examen_imagenes }}</div>
        </div>
        @endif

    </div>

    @else
    <div class="lab-section">
        <div class="lab-empty">
            <i class="fas fa-flask"></i>
            <p>Este paciente no tiene órdenes de exámenes registradas.</p>
        </div>
    </div>
    @endif

    @elseif(!$cedula)
    <div style="text-align:center;padding:80px 20px;">
        <i class="fas fa-flask" style="font-size:4rem;color:#bfdbfe;display:block;margin-bottom:16px;"></i>
        <h3 style="color:#1a2e44;margin-bottom:8px;font-size:1.2rem;">Consulta de Órdenes de Exámenes</h3>
        <p style="color:#9ca3af;font-size:.9rem;">Ingrese la cédula del paciente para ver su última orden</p>
    </div>
    @endif

@endsection
