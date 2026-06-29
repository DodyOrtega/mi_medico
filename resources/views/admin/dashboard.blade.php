@extends('layouts.admin')

@section('titulo', 'Panel de Administración')
@section('seccion_titulo', 'Panel de Administración')

@section('contenido')

{{-- ═══ BIENVENIDA ═══ --}}
<section class="admin-welcome">
    <h1 class="admin-welcome__title">Bienvenido, Administrador</h1>
    <p class="admin-welcome__sub">Sistema de gestión integral para Mi Médico</p>

    <div class="admin-welcome__badges">
        <span class="admin-badge">
            <i class="fas fa-user-shield"></i> Rol: Administrador
        </span>
        <span class="admin-badge">
            <i class="fas fa-clock"></i> {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>
</section>

{{-- ═══ RESUMEN RÁPIDO ═══ --}}
<section class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon admin-stat-card__icon--blue">
            <i class="fas fa-stethoscope"></i>
        </div>
        <div class="admin-stat-card__body">
            <span class="admin-stat-card__num">{{ $stats['medicos'] }}</span>
            <span class="admin-stat-card__label">Médicos</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card__icon admin-stat-card__icon--green">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="admin-stat-card__body">
            <span class="admin-stat-card__num">{{ $stats['paramedicos'] }}</span>
            <span class="admin-stat-card__label">Paramédicos</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card__icon admin-stat-card__icon--purple">
            <i class="fas fa-users"></i>
        </div>
        <div class="admin-stat-card__body">
            <span class="admin-stat-card__num">{{ $stats['pacientes'] }}</span>
            <span class="admin-stat-card__label">Pacientes</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card__icon admin-stat-card__icon--orange">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="admin-stat-card__body">
            <span class="admin-stat-card__num">{{ $stats['citas_hoy'] }}</span>
            <span class="admin-stat-card__label">Citas hoy</span>
        </div>
    </div>
</section>

{{-- ═══ TARJETAS DE MÓDULOS ═══ --}}
<section class="admin-cards">
    @foreach ($tarjetas as $tarjeta)
    <div class="admin-card">
        <div class="admin-card__icon">
            <i class="fas fa-{{ $tarjeta['icono'] }}"></i>
        </div>

        <h3 class="admin-card__title">{{ $tarjeta['titulo'] }}</h3>

        @if($tarjeta['contador'] !== null)
        <span class="admin-card__contador">{{ $tarjeta['contador'] }} registrados</span>
        @endif

        <p class="admin-card__desc">{{ $tarjeta['descripcion'] }}</p>

        <a href="{{ $tarjeta['ruta'] }}" class="admin-card__btn">
            <i class="fas fa-{{ $tarjeta['boton_icono'] }}"></i>
            {{ $tarjeta['boton'] }}
        </a>
    </div>
    @endforeach
</section>

@endsection

@push('styles')
@endpush

@push('scripts')
@if($showWelcome)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: '¡Bienvenido!',
        html: `
            <div style="text-align:center; padding:10px;">
                <div style="margin:20px 0;">
                    <i class="fas fa-user-shield" style="font-size:4rem; color:#0078d7;"></i>
                </div>
                <p style="font-size:1.3rem; font-weight:600; color:#2c3e50; margin:10px 0;">
                    {{ session('usuario_nombre', 'Administrador') }}
                </p>
                <p style="font-size:1rem; color:#5a6268;">Administrador</p>
                <div style="margin:20px 0; padding:10px; background:#e8f4f8; border-radius:10px;">
                    <p style="font-size:.95rem; color:#0078d7;">
                        <i class="fas fa-clock"></i> Último acceso: {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
                <p style="font-size:1rem; color:#28a745;">
                    <i class="fas fa-check-circle"></i> Sesión iniciada correctamente
                </p>
            </div>`,
        icon: 'success',
        iconColor: '#28a745',
        confirmButtonText: 'Ir al Panel',
        confirmButtonColor: '#0078d7',
        timer: 5000,
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
    });
});
</script>
@endif
@endpush