@extends('layouts.medico')
@section('titulo', 'Gestión de Citas')
@section('seccion_titulo', 'Gestión de Citas')
@section('volver', route('medico.dashboard'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-calendar-check"></i> Gestión de Citas</h1>
            <p class="page-header__sub">{{ $citas->count() }} cita(s) encontradas</p>
        </div>
        <a href="{{ route('medico.citas.nueva') }}" class="btn-add-big">
            <i class="fas fa-plus-circle"></i> Nueva Cita
        </a>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif

    {{-- FILTROS --}}
    <div class="toolbar" style="flex-wrap:wrap; gap:10px;">
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @foreach(['todas'=>'Todas','hoy'=>'Hoy','pendientes'=>'Pendientes','pasadas'=>'Pasadas'] as $val => $label)
            <a href="{{ route('medico.citas.index', ['filtro'=>$val, 'busqueda'=>$busqueda]) }}"
               class="btn-secondary {{ $filtro === $val ? 'btn-filtro--activo' : '' }}"
               style="padding:8px 16px; font-size:.82rem;">
                {{ $label }}
            </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('medico.citas.index') }}" class="search-form" style="flex:1; min-width:200px;">
            <input type="hidden" name="filtro" value="{{ $filtro }}">
            <input type="text" name="busqueda" class="search-input" placeholder="Buscar paciente..." value="{{ $busqueda }}">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
        </form>
    </div>

    @if($citas->isEmpty())
    <div class="empty-state">
        <i class="fas fa-calendar-xmark"></i>
        <p>No hay citas para mostrar.</p>
    </div>
    @else
    <div class="citas-lista-completa">
        @foreach($citas as $cita)
        @php $esFutura = \Carbon\Carbon::parse($cita->fecha_hora)->gt(now()); @endphp
        <div class="cita-completa {{ $esFutura ? 'cita-completa--futura' : 'cita-completa--pasada' }}">
            <div class="cita-completa__fecha">
                <span class="cita-completa__dia">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d') }}</span>
                <span class="cita-completa__mes">{{ \Carbon\Carbon::parse($cita->fecha_hora)->isoFormat('MMM') }}</span>
                <span class="cita-completa__hora">{{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}</span>
            </div>
            <div class="cita-completa__body">
                <span class="cita-completa__paciente">{{ $cita->nombres }} {{ $cita->apellido1 }}</span>
                <span class="cita-completa__motivo">{{ $cita->motivo_consulta ?: 'Sin motivo especificado' }}</span>
                @if($cita->diagnostico)
                <span class="cita-completa__diag"><i class="fas fa-notes-medical"></i> {{ Str::limit($cita->diagnostico, 60) }}</span>
                @endif
            </div>
            <div class="cita-completa__side">
                <span class="cita-item__badge cita-item__badge--{{ $cita->tipo_cita }}">
                    {{ ucfirst($cita->tipo_cita) }}
                </span>
                @if($esFutura)
                <a href="{{ route('medico.citas.nueva', ['paciente' => $cita->cedula_paciente]) }}" class="action-btn action-btn--edit" style="margin-top:6px;">
                    <i class="fas fa-stethoscope"></i> Atender
                </a>
                @if($cita->tipo_cita === 'virtual')
                <a href="{{ route('medico.video.index') }}" class="action-btn" style="margin-top:6px;background:#7c3aed;color:#fff;border-color:#7c3aed;">
                    <i class="fas fa-video"></i> Videollamada
                </a>
                @endif
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
@endpush