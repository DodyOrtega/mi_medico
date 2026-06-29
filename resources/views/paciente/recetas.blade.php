@extends('layouts.paciente')

@section('titulo', 'Mis Recetas')
@section('seccion_titulo', 'Mis Recetas')
@section('volver', route('paciente.dashboard'))

@section('contenido')

    <div class="page-header">
        <div>
            <h2 class="page-header__title"><i class="fas fa-prescription-bottle"></i> Mis Recetas</h2>
            <p class="page-header__sub">Historial de recetas médicas emitidas</p>
        </div>
        <a href="{{ route('paciente.dashboard') }}" class="btn-ver"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    @if($recetas->isEmpty())
        <div class="paciente-section">
            <div class="paciente-empty">
                <i class="fas fa-prescription-bottle" style="color:#dee2e6;"></i>
                <p>No tienes recetas registradas aún.</p>
            </div>
        </div>
    @else
        @foreach($recetas as $r)
        <div class="paciente-section" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f0f4f8;">
                <div>
                    <div style="font-weight:700;color:#1a2e44;font-size:1rem;">
                        Dr. {{ $r->medico_nombres }} {{ $r->medico_apellido1 }}
                    </div>
                    @if($r->especialidad)
                        <span class="badge-virtual" style="margin-top:4px;">{{ $r->especialidad }}</span>
                    @endif
                </div>
                <span style="font-size:.82rem;color:#888;"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($r->fecha_hora)->format('d/m/Y') }}</span>
            </div>

            @if($r->diagnostico)
            <div class="info-block">
                <span class="info-label">Diagnóstico</span>
                <div class="info-text">{{ $r->diagnostico }}</div>
            </div>
            @endif

            <div class="info-block">
                <span class="info-label">Receta Médica</span>
                <div class="info-text info-text--receta" style="font-size:.9rem;line-height:1.7;">{{ $r->receta }}</div>
            </div>
        </div>
        @endforeach
    @endif

@endsection