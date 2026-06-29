@extends('layouts.laboratorio')

@section('titulo', 'Dashboard — Laboratorio')
@section('seccion_titulo', 'Laboratorio Clínico')

@section('contenido')

    <div class="lab-welcome">
        <h2 class="lab-welcome__title">Laboratorio Clínico</h2>
        <p class="lab-welcome__sub">Bienvenido, {{ session('usuario_nombre') }} — Solo lectura</p>
    </div>

    {{-- STATS --}}
    <div class="lab-stats">
        <div class="lab-stat">
            <i class="fas fa-flask lab-stat--green" style="background:#e6f9f0;color:#00875a;"></i>
            <div>
                <span class="lab-stat__num">{{ $totalExamenes }}</span>
                <span class="lab-stat__label">Total exámenes</span>
            </div>
        </div>
        <div class="lab-stat">
            <i class="fas fa-calendar-day" style="background:#e8f4fb;color:#0078d7;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="lab-stat__num">{{ $hoy }}</span>
                <span class="lab-stat__label">Registrados hoy</span>
            </div>
        </div>
        <div class="lab-stat">
            <i class="fas fa-hourglass-half" style="background:#fff4e6;color:#f59e0b;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="lab-stat__num">{{ $pendientes }}</span>
                <span class="lab-stat__label">Pendientes de resultado</span>
            </div>
        </div>
        <div class="lab-stat">
            <i class="fas fa-check-circle" style="background:#e6f9f7;color:#0d9488;font-size:1.6rem;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></i>
            <div>
                <span class="lab-stat__num">{{ $conResultado }}</span>
                <span class="lab-stat__label">Con resultado</span>
            </div>
        </div>
    </div>

    {{-- ACCESO RÁPIDO --}}
    <div class="lab-section">
        <h3 class="lab-section__title"><i class="fas fa-th-large"></i> Acceso Rápido</h3>
        <div class="lab-modulos">
            <a href="{{ route('laboratorio.buscar') }}" class="lab-modulo">
                <i class="fas fa-search"></i>
                <h3>Buscar Paciente</h3>
                <p>Consultar exámenes por cédula</p>
            </a>
            <div class="lab-modulo" style="cursor:default;opacity:.6;">
                <i class="fas fa-upload"></i>
                <h3>Subir Resultados</h3>
                <p>No disponible — solo lectura</p>
            </div>
            <div class="lab-modulo" style="cursor:default;opacity:.6;">
                <i class="fas fa-edit"></i>
                <h3>Editar Exámenes</h3>
                <p>No disponible — solo lectura</p>
            </div>
        </div>
    </div>

    {{-- AVISO DE PERMISOS (Heurística #1 Nielsen: visibilidad del estado) --}}
    <div style="display:flex;align-items:flex-start;gap:10px;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:12px;padding:16px 20px;">
        <i class="fas fa-shield-alt" style="color:#00875a;margin-top:2px;font-size:1.1rem;flex-shrink:0;"></i>
        <div style="font-size:.875rem;color:#065f46;">
            <strong>Módulo de solo lectura.</strong> Este módulo permite únicamente consultar exámenes ordenados por médicos.
            No es posible crear, editar, subir resultados ni modificar información del paciente.
        </div>
    </div>

@endsection