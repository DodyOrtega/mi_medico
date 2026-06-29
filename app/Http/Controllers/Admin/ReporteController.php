<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReporteController
 *
 * Estadísticas y reportes del sistema: indicadores generales,
 * citas por especialidad, evolución mensual, pacientes por edad,
 * diagnósticos frecuentes, distribución geográfica y médicos
 * más activos. Incluye exportación a CSV.
 */
class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fechaDesde = $request->get('desde') ?: now()->startOfMonth()->format('Y-m-d');
        $fechaHasta = $request->get('hasta') ?: now()->format('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
            $fechaDesde = now()->startOfMonth()->format('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
            $fechaHasta = now()->format('Y-m-d');
        }

        $desdeDate = Carbon::parse($fechaDesde);
        $hastaDate = Carbon::parse($fechaHasta);

        if ($desdeDate->gt($hastaDate)) {
            [$desdeDate, $hastaDate] = [$hastaDate, $desdeDate];
            $fechaDesde = $desdeDate->format('Y-m-d');
            $fechaHasta = $hastaDate->format('Y-m-d');
        }

        $desde = $fechaDesde . ' 00:00:00';
        $hasta = $fechaHasta . ' 23:59:59';

        $diasPeriodo = max(1, $desdeDate->diffInDays($hastaDate) + 1);
        $prevHastaDate = $desdeDate->copy()->subDay();
        $prevDesdeDate = $prevHastaDate->copy()->subDays($diasPeriodo - 1);
        $prevDesde = $prevDesdeDate->format('Y-m-d') . ' 00:00:00';
        $prevHasta = $prevHastaDate->format('Y-m-d') . ' 23:59:59';

        $datos = $this->calcularReportes($desde, $hasta, $prevDesde, $prevHasta, $fechaDesde, $fechaHasta);

        if ($request->get('export') === 'csv') {
            return $this->exportarCsv($datos, $fechaDesde, $fechaHasta);
        }

        return view('admin.reportes.index', array_merge($datos, [
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]));
    }

    // ── Cálculo central de todos los indicadores ──────────────────
    private function calcularReportes(string $desde, string $hasta, string $prevDesde, string $prevHasta, string $fechaDesdeDia, string $fechaHastaDia): array
    {
        $estadisticas = [
            'total_pacientes'        => DB::table('PACIENTE')->count(),
            'total_medicos'          => DB::table('PERSONAL_MEDICO')->where('tipo', 'Medico')->count(),
            'total_paramedicos'      => DB::table('PERSONAL_MEDICO')->where('tipo', 'Paramedico')->count(),
            'citas_periodo'          => DB::table('CITAS')->whereBetween('fecha_hora', [$desde, $hasta])->count(),
            'citas_periodo_anterior' => DB::table('CITAS')->whereBetween('fecha_hora', [$prevDesde, $prevHasta])->count(),
            'emergencias_periodo'    => DB::table('AGENDA')->where('es_emergencia', 1)->whereBetween('fecha', [$fechaDesdeDia, $fechaHastaDia])->count(),
        ];

        $citasPorEspecialidad = DB::table('CITAS as c')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'c.cedula_medico', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->whereBetween('c.fecha_hora', [$desde, $hasta])
            ->select(DB::raw("COALESCE(e.nombre, 'Sin especialidad') as label"), DB::raw('COUNT(c.id_cita) as total'))
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $citasMensuales = DB::table('CITAS')
            ->where('fecha_hora', '>=', now()->subMonths(11)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(fecha_hora, '%Y-%m') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $pacientesPorEdad = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->select(DB::raw("
                CASE
                    WHEN p.fecha_nac IS NULL THEN 'Sin fecha'
                    WHEN TIMESTAMPDIFF(YEAR, p.fecha_nac, CURDATE()) BETWEEN 0 AND 17 THEN '0-17'
                    WHEN TIMESTAMPDIFF(YEAR, p.fecha_nac, CURDATE()) BETWEEN 18 AND 29 THEN '18-29'
                    WHEN TIMESTAMPDIFF(YEAR, p.fecha_nac, CURDATE()) BETWEEN 30 AND 44 THEN '30-44'
                    WHEN TIMESTAMPDIFF(YEAR, p.fecha_nac, CURDATE()) BETWEEN 45 AND 59 THEN '45-59'
                    ELSE '60+'
                END as label
            "), DB::raw('COUNT(*) as total'))
            ->groupBy('label')
            ->orderByRaw("FIELD(label, '0-17', '18-29', '30-44', '45-59', '60+', 'Sin fecha')")
            ->get();

        $diagnosticos = DB::table('CITAS')
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->select(DB::raw("COALESCE(NULLIF(TRIM(diagnostico), ''), 'Sin diagnóstico') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $sectores = DB::table('PACIENTE as pac')
            ->leftJoin('PARROQUIA as pa', 'pac.codigo_parroquia', '=', 'pa.codigo_parroquia')
            ->leftJoin('CANTON as ca', 'pa.codigo_canton', '=', 'ca.codigo_canton')
            ->leftJoin('PROVINCIA as pr', 'ca.codigo_provincia', '=', 'pr.codigo_provincia')
            ->select('pr.nombre_provincia as provincia', 'ca.nombre_canton as canton', 'pa.nombre_parroquia as parroquia', DB::raw('COUNT(pac.cedula) as total'))
            ->groupBy('pr.nombre_provincia', 'ca.nombre_canton', 'pa.nombre_parroquia')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $medicosActivos = DB::table('PERSONAL_MEDICO as pm')
            ->join('PERSONAS as p', 'pm.cedula', '=', 'p.cedula')
            ->leftJoin('CITAS as c', function ($join) use ($desde, $hasta) {
                $join->on('pm.cedula', '=', 'c.cedula_medico')
                     ->whereBetween('c.fecha_hora', [$desde, $hasta]);
            })
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('pm.tipo', 'Medico')
            ->select(
                DB::raw("CONCAT(p.nombres, ' ', p.apellido1) as medico"),
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', '), 'Sin especialidad') as especialidad"),
                DB::raw('COUNT(c.id_cita) as total')
            )
            ->groupBy('pm.cedula', 'p.nombres', 'p.apellido1')
            ->orderByDesc('total')
            ->orderBy('medico')
            ->limit(10)
            ->get();

        $personalEstado = DB::table('PERSONAL_MEDICO as pm')
            ->join('PERSONAS as p', 'pm.cedula', '=', 'p.cedula')
            ->select('pm.tipo as label', 'p.estado', DB::raw('COUNT(*) as total'))
            ->groupBy('pm.tipo', 'p.estado')
            ->orderBy('pm.tipo')
            ->orderBy('p.estado')
            ->get();

        return [
            'estadisticas'          => $estadisticas,
            'citasPorEspecialidad'  => $citasPorEspecialidad,
            'citasMensuales'        => $citasMensuales,
            'pacientesPorEdad'      => $pacientesPorEdad,
            'diagnosticos'          => $diagnosticos,
            'sectores'              => $sectores,
            'medicosActivos'        => $medicosActivos,
            'personalEstado'        => $personalEstado,
        ];
    }

    // ── Exportación a CSV ────────────────────────────────────────
    private function exportarCsv(array $datos, string $desde, string $hasta): StreamedResponse
    {
        $filename = "reporte_mimedico_{$desde}_{$hasta}.csv";

        return response()->streamDownload(function () use ($datos, $desde, $hasta) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($out, ['Reporte MiMedico', $desde, $hasta]);
            fputcsv($out, []);
            fputcsv($out, ['Indicador', 'Valor']);
            fputcsv($out, ['Total pacientes', $datos['estadisticas']['total_pacientes']]);
            fputcsv($out, ['Total medicos', $datos['estadisticas']['total_medicos']]);
            fputcsv($out, ['Total paramedicos', $datos['estadisticas']['total_paramedicos']]);
            fputcsv($out, ['Citas del periodo', $datos['estadisticas']['citas_periodo']]);
            fputcsv($out, ['Emergencias del periodo', $datos['estadisticas']['emergencias_periodo']]);

            $secciones = [
                'Citas por especialidad'        => [$datos['citasPorEspecialidad'], ['Especialidad', 'Citas']],
                'Pacientes por edad'            => [$datos['pacientesPorEdad'], ['Rango', 'Pacientes']],
                'Diagnosticos / enfermedades'   => [$datos['diagnosticos'], ['Diagnostico', 'Citas']],
                'Medicos mas activos'           => [$datos['medicosActivos'], ['Medico', 'Especialidad', 'Citas']],
                'Personal medico y paramedico'  => [$datos['personalEstado'], ['Tipo', 'Estado', 'Total']],
            ];

            foreach ($secciones as $titulo => [$filas, $headers]) {
                fputcsv($out, []);
                fputcsv($out, [$titulo]);
                fputcsv($out, $headers);

                foreach ($filas as $fila) {
                    if ($titulo === 'Medicos mas activos') {
                        fputcsv($out, [$fila->medico, $fila->especialidad, $fila->total]);
                    } elseif ($titulo === 'Personal medico y paramedico') {
                        fputcsv($out, [$fila->label, $fila->estado, $fila->total]);
                    } else {
                        fputcsv($out, [$fila->label ?? ($fila->provincia ?? ''), $fila->total ?? '']);
                    }
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}