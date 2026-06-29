@extends('layouts.admin')

@section('titulo', 'Reportes y Estadísticas')
@section('seccion_titulo', 'Reportes y Estadísticas')
@section('volver', route('admin.dashboard'))

@section('contenido')

@php
    function porcentajeCambio($actual, $anterior) {
        if ($anterior == 0) return $actual > 0 ? '+100%' : '0%';
        $valor = (($actual - $anterior) / $anterior) * 100;
        return ($valor >= 0 ? '+' : '') . number_format($valor, 1) . '%';
    }
@endphp

<div class="medicos-page">

    <div class="page-header">
        <div>
            <h1 class="page-header__title"><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h1>
            <p class="page-header__sub">Periodo: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.reportes.index', ['desde' => $fechaDesde, 'hasta' => $fechaHasta, 'export' => 'csv']) }}" class="btn-success">
            <i class="fas fa-file-csv"></i> Exportar CSV
        </a>
    </div>

    {{-- ═══ FILTRO DE FECHAS ═══ --}}
    <form method="GET" action="{{ route('admin.reportes.index') }}" class="reportes-filtro">
        <div class="form-group">
            <label>Desde</label>
            <input type="date" name="desde" value="{{ $fechaDesde }}">
        </div>
        <div class="form-group">
            <label>Hasta</label>
            <input type="date" name="hasta" value="{{ $fechaHasta }}">
        </div>
        <button type="submit" class="btn-search"><i class="fas fa-filter"></i> Filtrar</button>
    </form>

    {{-- ═══ INDICADORES PRINCIPALES ═══ --}}
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon admin-stat-card__icon--purple"><i class="fas fa-users"></i></div>
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__num">{{ $estadisticas['total_pacientes'] }}</span>
                <span class="admin-stat-card__label">Total pacientes</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon admin-stat-card__icon--blue"><i class="fas fa-stethoscope"></i></div>
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__num">{{ $estadisticas['total_medicos'] }}</span>
                <span class="admin-stat-card__label">Médicos</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon admin-stat-card__icon--green"><i class="fas fa-user-md"></i></div>
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__num">{{ $estadisticas['total_paramedicos'] }}</span>
                <span class="admin-stat-card__label">Paramédicos</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon admin-stat-card__icon--orange"><i class="fas fa-calendar-check"></i></div>
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__num">{{ $estadisticas['citas_periodo'] }}</span>
                <span class="admin-stat-card__label">
                    Citas del periodo
                    <small class="reportes-cambio">{{ porcentajeCambio($estadisticas['citas_periodo'], $estadisticas['citas_periodo_anterior']) }} vs anterior</small>
                </span>
            </div>
        </div>
    </div>

    {{-- ═══ GRÁFICOS ═══ --}}
    <div class="reportes-charts">
        <div class="reportes-chart-card">
            <h3><i class="fas fa-chart-line"></i> Evolución de citas (últimos 12 meses)</h3>
            <canvas id="growthChart" height="220"></canvas>
        </div>
        <div class="reportes-chart-card">
            <h3><i class="fas fa-chart-pie"></i> Citas por especialidad</h3>
            <canvas id="specialtyChart" height="220"></canvas>
        </div>
    </div>

    {{-- ═══ TABLAS DE DATOS ═══ --}}
    <div class="reportes-tablas">

        <div class="reportes-tabla-card">
            <h3><i class="fas fa-birthday-cake"></i> Pacientes por rango de edad</h3>
            <table class="tabla-reporte">
                <thead><tr><th>Rango</th><th>Pacientes</th></tr></thead>
                <tbody>
                    @foreach($pacientesPorEdad as $fila)
                    <tr><td>{{ $fila->label }}</td><td>{{ $fila->total }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="reportes-tabla-card">
            <h3><i class="fas fa-file-medical"></i> Diagnósticos más frecuentes</h3>
            <table class="tabla-reporte">
                <thead><tr><th>Diagnóstico</th><th>Citas</th></tr></thead>
                <tbody>
                    @foreach($diagnosticos as $fila)
                    <tr><td>{{ $fila->label }}</td><td>{{ $fila->total }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="reportes-tabla-card">
            <h3><i class="fas fa-map-marker-alt"></i> Distribución geográfica (top 10)</h3>
            <table class="tabla-reporte">
                <thead><tr><th>Provincia</th><th>Cantón</th><th>Parroquia</th><th>Pacientes</th></tr></thead>
                <tbody>
                    @foreach($sectores as $fila)
                    <tr>
                        <td>{{ $fila->provincia ?? 'N/D' }}</td>
                        <td>{{ $fila->canton ?? 'N/D' }}</td>
                        <td>{{ $fila->parroquia ?? 'N/D' }}</td>
                        <td>{{ $fila->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="reportes-tabla-card">
            <h3><i class="fas fa-user-md"></i> Médicos más activos</h3>
            <table class="tabla-reporte">
                <thead><tr><th>Médico</th><th>Especialidad</th><th>Citas</th></tr></thead>
                <tbody>
                    @foreach($medicosActivos as $fila)
                    <tr>
                        <td>{{ $fila->medico }}</td>
                        <td>{{ $fila->especialidad }}</td>
                        <td>{{ $fila->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="reportes-tabla-card reportes-tabla-card--full">
            <h3><i class="fas fa-users-cog"></i> Personal médico y paramédico por estado</h3>
            <table class="tabla-reporte">
                <thead><tr><th>Tipo</th><th>Estado</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($personalEstado as $fila)
                    <tr>
                        <td>{{ $fila->label }}</td>
                        <td>
                            <span class="estado-badge {{ $fila->estado === 'activo' ? 'estado-badge--activo' : 'estado-badge--inactivo' }}" style="position:static; display:inline-flex;">
                                {{ ucfirst($fila->estado) }}
                            </span>
                        </td>
                        <td>{{ $fila->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/medicos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/reportes.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const growthCtx = document.getElementById('growthChart');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: @json($citasMensuales->pluck('label')),
        datasets: [{
            label: 'Citas',
            data: @json($citasMensuales->pluck('total')),
            borderColor: '#0078d7',
            backgroundColor: 'rgba(0,120,215,.1)',
            tension: 0.35,
            fill: true,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

const specialtyCtx = document.getElementById('specialtyChart');
new Chart(specialtyCtx, {
    type: 'doughnut',
    data: {
        labels: @json($citasPorEspecialidad->pluck('label')),
        datasets: [{
            data: @json($citasPorEspecialidad->pluck('total')),
            backgroundColor: ['#0078d7','#28a745','#f59e0b','#6f42c1','#dc3545','#17a2b8','#fd7e14','#20c997'],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush