<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * AgendaController
 *
 * Módulo médico — agenda:
 * - index(): calendario mensual con citas del médico
 * - gestionar(): formulario para generar slots masivos
 * - generarSlots(): procesa la generación masiva
 * - eliminarSlot(): elimina un slot disponible
 */
class AgendaController extends Controller
{
    // ── Calendario mensual ────────────────────────────────────────
    public function index(Request $request)
    {
        $cedula = Session::get('usuario_cedula');
        $mes    = (int) $request->get('mes', now()->month);
        $anio   = (int) $request->get('anio', now()->year);

        if ($mes < 1 || $mes > 12) { $mes = now()->month; $anio = now()->year; }

        $primerDia   = Carbon::create($anio, $mes, 1);
        $diasEnMes   = $primerDia->daysInMonth;
        $iniciaSemana = ($primerDia->dayOfWeek === 0) ? 6 : $primerDia->dayOfWeek - 1;

        // Citas confirmadas del mes (consultas reales — sin duplicar con AGENDA)
        $citasMes = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->whereYear('c.fecha_hora', $anio)
            ->whereMonth('c.fecha_hora', $mes)
            ->select(
                'c.id_cita as id', 'c.fecha_hora', 'c.tipo_cita',
                'c.motivo_consulta', 'p.nombres', 'p.apellido1'
            )
            ->get();

        // Slots disponibles del mes agrupados por fecha (solo para indicar disponibilidad)
        $disponiblesPorFecha = DB::table('AGENDA')
            ->where('cedula_medico', $cedula)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('estado', 'disponible')
            ->groupBy('fecha')
            ->pluck(DB::raw('COUNT(*)'), 'fecha');

        // Organizar citas reales por fecha (sin duplicados)
        $citasPorFecha = [];
        foreach ($citasMes as $cita) {
            $fecha = substr($cita->fecha_hora, 0, 10);
            $citasPorFecha[$fecha][] = $cita;
        }

        // Próximas citas (futura)
        $proximasCitas = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->where('c.fecha_hora', '>', now())
            ->select('c.id_cita', 'c.fecha_hora', 'c.tipo_cita',
                     'p.nombres', 'p.apellido1')
            ->orderBy('c.fecha_hora')
            ->limit(5)
            ->get();

        $mesSiguiente = $mes === 12 ? 1  : $mes + 1;
        $anioSig      = $mes === 12 ? $anio + 1 : $anio;
        $mesAnterior  = $mes === 1  ? 12 : $mes - 1;
        $anioAnt      = $mes === 1  ? $anio - 1 : $anio;

        return view('medico.agenda.index', [
            'mes'                 => $mes,
            'anio'                => $anio,
            'mesNombre'           => $primerDia->isoFormat('MMMM'),
            'diasEnMes'           => $diasEnMes,
            'iniciaSemana'        => $iniciaSemana,
            'citasPorFecha'       => $citasPorFecha,
            'disponiblesPorFecha' => $disponiblesPorFecha,
            'proximasCitas'       => $proximasCitas,
            'mesSiguiente'        => $mesSiguiente,
            'anioSig'             => $anioSig,
            'mesAnterior'         => $mesAnterior,
            'anioAnt'             => $anioAnt,
            'hoy'                 => now()->format('Y-m-d'),
        ]);
    }

    // ── Formulario de gestión de slots ───────────────────────────
    public function gestionar(Request $request)
    {
        $cedula = Session::get('usuario_cedula');
        $filtroFecha = $request->get('fecha_filtro', now()->format('Y-m-d'));

        $slots = DB::table('AGENDA as a')
            ->leftJoin('PERSONAS as p', 'a.cedula_paciente', '=', 'p.cedula')
            ->where('a.cedula_medico', $cedula)
            ->where('a.fecha', '>=', now()->format('Y-m-d'))
            ->select(
                'a.id_agenda', 'a.fecha', 'a.hora', 'a.estado', 'a.es_emergencia',
                'p.nombres', 'p.apellido1'
            )
            ->orderBy('a.fecha')
            ->orderBy('a.hora')
            ->get()
            ->groupBy('fecha');

        $cfgCitas = ConfiguracionController::leerConfig()['citas'] ?? [];
        $duracion = (int) ($cfgCitas['duracion_cita_default'] ?? 30);
        $maxCitas = (int) ($cfgCitas['max_citas_dia']         ?? 20);

        return view('medico.agenda.gestionar', [
            'slots'       => $slots,
            'filtroFecha' => $filtroFecha,
            'duracion'    => $duracion,
            'maxCitas'    => $maxCitas,
        ]);
    }

    // ── Generar slots masivos ─────────────────────────────────────
    public function generarSlots(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $cedula      = Session::get('usuario_cedula');
        $diasSemana  = array_filter([
            1 => $request->has('lunes'),
            2 => $request->has('martes'),
            3 => $request->has('miercoles'),
            4 => $request->has('jueves'),
            5 => $request->has('viernes'),
            6 => $request->has('sabado'),
            7 => $request->has('domingo'),
        ]);

        $horarios = array_merge(
            $request->get('horarios_manana', []),
            $request->get('horarios_tarde', [])
        );

        if (empty($diasSemana)) return back()->with('error', 'Selecciona al menos un día de la semana.');
        if (empty($horarios))   return back()->with('error', 'Selecciona al menos un horario.');

        $maxCitas = (int) (ConfiguracionController::leerConfig()['citas']['max_citas_dia'] ?? 20);

        DB::beginTransaction();
        try {
            $inicio    = Carbon::parse($request->fecha_inicio);
            $fin       = Carbon::parse($request->fecha_fin);
            $generados = 0;
            $omitidos  = 0;
            $limitados = 0;

            for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                $diaSemana = $fecha->isoWeekday(); // 1=Lun ... 7=Dom

                if (!isset($diasSemana[$diaSemana])) continue;

                $slotsDia = DB::table('AGENDA')
                    ->where('cedula_medico', $cedula)
                    ->where('fecha', $fecha->format('Y-m-d'))
                    ->count();

                foreach ($horarios as $hora) {
                    if ($slotsDia >= $maxCitas) { $limitados++; continue; }

                    $existe = DB::table('AGENDA')
                        ->where('cedula_medico', $cedula)
                        ->where('fecha', $fecha->format('Y-m-d'))
                        ->where('hora', $hora)
                        ->exists();

                    if ($existe) { $omitidos++; continue; }

                    DB::table('AGENDA')->insert([
                        'cedula_medico'   => $cedula,
                        'cedula_paciente' => null,
                        'fecha'           => $fecha->format('Y-m-d'),
                        'hora'            => $hora,
                        'estado'          => 'disponible',
                    ]);
                    $generados++;
                    $slotsDia++;
                }
            }

            DB::commit();

            $msg = "Se generaron {$generados} slot(s) de agenda.";
            if ($omitidos  > 0) $msg .= " ({$omitidos} ya existían y se omitieron)";
            if ($limitados > 0) $msg .= " ({$limitados} omitidos por límite de {$maxCitas} citas/día)";

            return back()->with('exito', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar horarios: ' . $e->getMessage());
        }
    }

    // ── Eliminar un slot disponible ───────────────────────────────
    public function eliminarSlot(int $id)
    {
        $cedula = Session::get('usuario_cedula');

        $slot = DB::table('AGENDA')
            ->where('id_agenda', $id)
            ->where('cedula_medico', $cedula)
            ->where('estado', 'disponible')
            ->first();

        if (!$slot) {
            return back()->with('error', 'No se puede eliminar: slot no encontrado o ya está ocupado.');
        }

        DB::table('AGENDA')->where('id_agenda', $id)->delete();

        return back()->with('exito', 'Slot eliminado correctamente.');
    }
}