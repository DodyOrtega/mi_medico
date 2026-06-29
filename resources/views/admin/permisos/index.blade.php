@extends('layouts.admin')
@section('titulo', 'Permisos por Usuario')
@section('seccion_titulo', 'Permisos')
@section('volver', route('admin.dashboard'))

@php use App\Services\Permisos; @endphp

@section('contenido')
<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-user-lock"></i> Permisos por Usuario</h1>
            <p class="page-header__sub">Activa o desactiva módulos para cada médico y paramédico</p>
        </div>
    </div>

    @if(session('exito'))
    <div class="alert alert--success"><i class="fas fa-check-circle"></i> {{ session('exito') }}</div>
    @endif

    {{-- Buscador --}}
    <div style="margin-bottom:20px;">
        <input type="text" id="buscador" placeholder="Buscar por nombre o cédula..."
               oninput="filtrarUsuarios()"
               style="width:100%;max-width:400px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;">
    </div>

    {{-- MÉDICOS --}}
    <h2 style="font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-stethoscope"></i> Médicos
    </h2>
    <div id="lista-usuarios">
    @foreach($medicos as $u)
    @php $perms = Permisos::obtenerPermisos($u->cedula); @endphp
    <div class="form-card usuario-card" style="margin-bottom:16px;" data-nombre="{{ strtolower($u->nombres . ' ' . $u->apellido1 . ' ' . $u->cedula) }}">
        <form method="POST" action="{{ route('admin.permisos.update', $u->cedula) }}">
            @csrf @method('PUT')
            <input type="hidden" name="rol" value="medico">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                    <span style="font-weight:700;color:#111827;font-size:.95rem;">
                        {{ $u->apellido1 }}, {{ $u->nombres }}
                    </span>
                    <span style="display:block;font-size:.8rem;color:#6b7280;">Cédula: {{ $u->cedula }}</span>
                </div>
                <span style="background:{{ $u->estado === 'activo' ? '#dcfce7' : '#fee2e2' }};color:{{ $u->estado === 'activo' ? '#166534' : '#991b1b' }};padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;">
                    {{ ucfirst($u->estado) }}
                </span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px;">
                @foreach(Permisos::MEDICO_MODULOS as $clave => $nombre)
                @php $activo = $perms[$clave] ?? true; @endphp
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.875rem;color:#374151;">
                    <input type="checkbox" name="perm_{{ $clave }}" value="1"
                           {{ $activo ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#2563eb;">
                    {{ $nombre }}
                </label>
                @endforeach
            </div>
            <div class="form-card__actions" style="margin:0;">
                <button type="submit" class="btn-success" style="padding:7px 18px;font-size:.85rem;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
    @endforeach

    {{-- PARAMÉDICOS --}}
    <h2 style="font-size:1rem;font-weight:700;color:#1e3a5f;margin:24px 0 12px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-user-nurse"></i> Paramédicos
    </h2>
    @foreach($paramedicos as $u)
    @php $perms = Permisos::obtenerPermisos($u->cedula); @endphp
    <div class="form-card usuario-card" style="margin-bottom:16px;" data-nombre="{{ strtolower($u->nombres . ' ' . $u->apellido1 . ' ' . $u->cedula) }}">
        <form method="POST" action="{{ route('admin.permisos.update', $u->cedula) }}">
            @csrf @method('PUT')
            <input type="hidden" name="rol" value="paramedico">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                    <span style="font-weight:700;color:#111827;font-size:.95rem;">
                        {{ $u->apellido1 }}, {{ $u->nombres }}
                    </span>
                    <span style="display:block;font-size:.8rem;color:#6b7280;">Cédula: {{ $u->cedula }}</span>
                </div>
                <span style="background:{{ $u->estado === 'activo' ? '#dcfce7' : '#fee2e2' }};color:{{ $u->estado === 'activo' ? '#166534' : '#991b1b' }};padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;">
                    {{ ucfirst($u->estado) }}
                </span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px;">
                @foreach(Permisos::PARA_MODULOS as $clave => $nombre)
                @php $activo = $perms[$clave] ?? true; @endphp
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.875rem;color:#374151;">
                    <input type="checkbox" name="perm_{{ $clave }}" value="1"
                           {{ $activo ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#2563eb;">
                    {{ $nombre }}
                </label>
                @endforeach
            </div>
            <div class="form-card__actions" style="margin:0;">
                <button type="submit" class="btn-success" style="padding:7px 18px;font-size:.85rem;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
    @endforeach
    </div>

    @if($medicos->isEmpty() && $paramedicos->isEmpty())
    <div class="medico-empty">
        <i class="fas fa-users-slash"></i>
        <p>No hay médicos ni paramédicos registrados.</p>
    </div>
    @endif

</div>

<script>
function filtrarUsuarios() {
    var texto = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('.usuario-card').forEach(function(card) {
        card.style.display = card.dataset.nombre.includes(texto) ? '' : 'none';
    });
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
@endpush
