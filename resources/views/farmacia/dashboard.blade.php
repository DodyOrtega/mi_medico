@extends('layouts.farmacia')

@section('titulo', 'Dashboard — Farmacia')
@section('seccion_titulo', 'Farmacia')

@section('contenido')

    <div class="farm-welcome">
        <h2 class="farm-welcome__title">Farmacia</h2>
        <p class="farm-welcome__sub">Bienvenido, {{ session('usuario_nombre') }} — Solo lectura</p>
    </div>

    {{-- STATS --}}
    <div class="farm-stats">
        <div class="farm-stat">
            <i class="fas fa-prescription" style="background:#fef3c7;color:#d97706;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="farm-stat__num">{{ $totalRecetas }}</span>
                <span class="farm-stat__label">Total recetas</span>
            </div>
        </div>
        <div class="farm-stat">
            <i class="fas fa-calendar-day" style="background:#e6f9f0;color:#00875a;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="farm-stat__num">{{ $hoy }}</span>
                <span class="farm-stat__label">Recetas de hoy</span>
            </div>
        </div>
        <div class="farm-stat">
            <i class="fas fa-users" style="background:#e8f4fb;color:#0078d7;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="farm-stat__num">{{ $totalPacientes }}</span>
                <span class="farm-stat__label">Pacientes registrados</span>
            </div>
        </div>
    </div>

    {{-- ACCESO RÁPIDO --}}
    <div class="farm-section">
        <h3 class="farm-section__title"><i class="fas fa-th-large"></i> Acceso Rápido</h3>
        <div class="farm-modulos">
            <a href="{{ route('farmacia.buscar') }}" class="farm-modulo">
                <i class="fas fa-search"></i>
                <h3>Buscar Paciente</h3>
                <p>Consultar última receta por cédula</p>
            </a>
            <div class="farm-modulo" style="cursor:default;opacity:.6;">
                <i class="fas fa-edit"></i>
                <h3>Editar Recetas</h3>
                <p>No disponible — solo lectura</p>
            </div>
            <div class="farm-modulo" style="cursor:default;opacity:.6;">
                <i class="fas fa-boxes"></i>
                <h3>Inventario</h3>
                <p>No disponible — solo lectura</p>
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:flex-start;gap:10px;background:#fef3c7;border:1px solid #fcd34d;border-radius:12px;padding:16px 20px;">
        <i class="fas fa-shield-alt" style="color:#d97706;margin-top:2px;font-size:1.1rem;flex-shrink:0;"></i>
        <div style="font-size:.875rem;color:#92400e;">
            <strong>Módulo de solo lectura.</strong> Este módulo permite únicamente consultar la última receta emitida por el médico tratante.
            No es posible editar recetas, agregar medicamentos ni gestionar inventario.
        </div>
    </div>

@endsection