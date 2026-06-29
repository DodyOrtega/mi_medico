@extends('layouts.admin')
@section('titulo', 'Auditoría del Sistema')
@section('seccion_titulo', 'Auditoría')
@section('volver', route('admin.configuracion.index'))

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-clipboard-list"></i> Auditoría del Sistema</h1>
            <p class="page-header__sub">Registro de acciones realizadas por los usuarios</p>
        </div>
        <a href="{{ route('admin.configuracion.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Configuración
        </a>
    </div>

    {{-- Filtro por fecha --}}
    <form method="GET" action="{{ route('admin.auditoria.index') }}"
          style="display:flex;align-items:flex-end;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;min-width:200px;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">
                <i class="fas fa-calendar"></i> Fecha
            </label>
            <input type="date" name="fecha" value="{{ $fecha }}"
                   max="{{ now()->format('Y-m-d') }}"
                   style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;">
        </div>
        <button type="submit" class="btn-success" style="padding:9px 20px;">
            <i class="fas fa-search"></i> Buscar
        </button>
        @if(count($dias) > 1)
        <div class="form-group" style="margin:0;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">
                Días con registros
            </label>
            <select onchange="window.location='{{ route('admin.auditoria.index') }}?fecha='+this.value"
                    style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;">
                @foreach($dias as $dia)
                <option value="{{ $dia }}" {{ $dia === $fecha ? 'selected' : '' }}>{{ $dia }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </form>

    {{-- Contador --}}
    <div style="margin-bottom:12px;font-size:.875rem;color:#6b7280;">
        @if(count($entradas) > 0)
            <span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-weight:600;">
                {{ count($entradas) }} registro(s) el {{ $fecha }}
            </span>
        @else
            <span style="background:#f3f4f6;color:#6b7280;padding:4px 10px;border-radius:20px;">
                Sin registros para esta fecha
            </span>
        @endif
    </div>

    {{-- Tabla --}}
    @if(count($entradas) > 0)
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08);">
            <thead>
                <tr style="background:#1e3a5f;color:#fff;">
                    <th style="padding:12px 16px;text-align:left;font-weight:600;white-space:nowrap;">Hora</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Usuario</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Rol</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Acción</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Detalle</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;">IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entradas as $i => $e)
                @php
                    $cfg = match(true) {
                        str_contains($e['accion'], 'login')      => ['icon'=>'sign-in-alt',       'color'=>'#166534','bg'=>'#f0fdf4'],
                        str_contains($e['accion'], 'logout')     => ['icon'=>'sign-out-alt',      'color'=>'#6b7280','bg'=>'#f9fafb'],
                        str_contains($e['accion'], 'crear')      => ['icon'=>'plus-circle',       'color'=>'#1d4ed8','bg'=>'#eff6ff'],
                        str_contains($e['accion'], 'editar')     => ['icon'=>'pen',               'color'=>'#92400e','bg'=>'#fffbeb'],
                        str_contains($e['accion'], 'eliminar')   => ['icon'=>'trash',             'color'=>'#991b1b','bg'=>'#fef2f2'],
                        str_contains($e['accion'], 'activar') ||
                        str_contains($e['accion'], 'inactivar')  => ['icon'=>'toggle-on',         'color'=>'#7c3aed','bg'=>'#f5f3ff'],
                        str_contains($e['accion'], 'config')     => ['icon'=>'sliders-h',         'color'=>'#0e7490','bg'=>'#ecfeff'],
                        str_contains($e['accion'], 'backup')     => ['icon'=>'database',          'color'=>'#065f46','bg'=>'#ecfdf5'],
                        str_contains($e['accion'], 'restaurar')  => ['icon'=>'upload',            'color'=>'#9a3412','bg'=>'#fff7ed'],
                        str_contains($e['accion'], 'password') ||
                        str_contains($e['accion'], 'contrasena') => ['icon'=>'key',               'color'=>'#6d28d9','bg'=>'#ede9fe'],
                        default                                   => ['icon'=>'circle-info',       'color'=>'#374151','bg'=>'#f9fafb'],
                    };
                    $hora = substr($e['timestamp'], 11, 8);
                @endphp
                <tr style="background:{{ $i % 2 === 0 ? '#fff' : '#f9fafb' }};border-bottom:1px solid #f3f4f6;">
                    <td style="padding:10px 16px;white-space:nowrap;font-family:monospace;color:#374151;">{{ $hora }}</td>
                    <td style="padding:10px 16px;">
                        <span style="font-weight:600;color:#111827;">{{ $e['nombre'] }}</span>
                        <span style="display:block;font-size:.75rem;color:#9ca3af;">{{ $e['cedula'] }}</span>
                    </td>
                    <td style="padding:10px 16px;">
                        <span style="background:#e0e7ff;color:#3730a3;padding:2px 8px;border-radius:12px;font-size:.75rem;font-weight:600;">
                            {{ ucfirst($e['rol']) }}
                        </span>
                    </td>
                    <td style="padding:10px 16px;">
                        <span style="display:inline-flex;align-items:center;gap:6px;background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};padding:4px 10px;border-radius:12px;font-size:.8rem;font-weight:600;white-space:nowrap;">
                            <i class="fas fa-{{ $cfg['icon'] }}"></i>
                            {{ str_replace('_', ' ', ucfirst($e['accion'])) }}
                        </span>
                    </td>
                    <td style="padding:10px 16px;color:#4b5563;max-width:300px;">{{ $e['detalle'] }}</td>
                    <td style="padding:10px 16px;font-family:monospace;font-size:.8rem;color:#6b7280;white-space:nowrap;">{{ $e['ip'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="medico-empty" style="padding:60px 0;">
        <i class="fas fa-clipboard-list" style="font-size:3rem;color:#d1d5db;"></i>
        <p style="margin-top:12px;color:#9ca3af;">No hay registros de auditoría para el {{ $fecha }}</p>
    </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
@endpush
