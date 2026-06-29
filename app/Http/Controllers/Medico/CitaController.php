<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * CitaController — Módulo Médico
 *
 * - index():        Gestión de citas (listado filtrable)
 * - nuevaCita():    Formulario para atender/registrar consulta
 * - guardarCita():  Guarda la consulta + signos vitales
 * - historial():    Historial clínico global del médico
 */
class CitaController extends Controller
{
    // ── Gestión de citas ──────────────────────────────────────────
    public function index(Request $request)
    {
        $cedula  = Session::get('usuario_cedula');
        $filtro  = $request->query('filtro', 'todas');
        $busqueda = trim($request->query('busqueda', ''));

        $query = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita',
                'c.motivo_consulta', 'c.diagnostico', 'c.receta',
                'p.nombres', 'p.apellido1', 'p.cedula as cedula_paciente'
            );

        if ($filtro === 'hoy') {
            $query->whereDate('c.fecha_hora', today())
                  ->where('c.fecha_hora', '>=', now());
        } elseif ($filtro === 'pendientes') {
            $query->where('c.fecha_hora', '>', now());
        } elseif ($filtro === 'pasadas') {
            $query->where('c.fecha_hora', '<', now());
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1', 'like', "%{$busqueda}%")
                  ->orWhere('p.cedula', 'like', "%{$busqueda}%");
            });
        }

        $citas = $query->orderByDesc('c.fecha_hora')->get();

        return view('medico.citas.index', [
            'citas'    => $citas,
            'filtro'   => $filtro,
            'busqueda' => $busqueda,
        ]);
    }

    // ── Formulario nueva cita / atender consulta ──────────────────
    public function nuevaCita(Request $request)
    {
        $cedula   = Session::get('usuario_cedula');
        $cedulaPaciente = $request->query('paciente');
        $idAgenda = $request->query('id_agenda');

        // Si viene desde agenda, cargar datos del slot
        $slotAgenda = null;
        if ($idAgenda) {
            $slotAgenda = DB::table('AGENDA')
                ->where('id_agenda', $idAgenda)
                ->where('cedula_medico', $cedula)
                ->first();
        }

        // Pacientes de este médico para el selector
        $pacientes = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->where(function ($q) use ($cedula) {
                $q->whereExists(function ($sub) use ($cedula) {
                    $sub->select(DB::raw(1))
                        ->from('CITAS')
                        ->whereColumn('cedula_paciente', 'p.cedula')
                        ->where('cedula_medico', $cedula);
                });
            })
            ->select('p.cedula', 'p.nombres', 'p.apellido1')
            ->orderBy('p.nombres')
            ->get();

        // Datos del paciente preseleccionado
        $pacienteSeleccionado = null;
        if ($cedulaPaciente) {
            $pacienteSeleccionado = DB::table('PERSONAS as p')
                ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
                ->where('p.cedula', $cedulaPaciente)
                ->select('p.*', 'pac.tipo_sangre', 'pac.alergias', 'pac.antecedentes')
                ->first();
        }

        return view('medico.citas.nueva', [
            'pacientes'           => $pacientes,
            'pacienteSeleccionado'=> $pacienteSeleccionado,
            'slotAgenda'          => $slotAgenda,
        ]);
    }

    // ── Guardar cita / consulta ───────────────────────────────────
    public function guardarCita(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula_paciente' => 'required|string|exists:PERSONAS,cedula',
            'fecha_hora'      => 'required|date',
            'tipo_cita'       => 'required|in:presencial,virtual',
            'motivo_consulta' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        $cedula = Session::get('usuario_cedula');

        DB::beginTransaction();
        try {
            // Crear la cita
            $idCita = DB::table('CITAS')->insertGetId([
                'cedula_paciente'  => $request->cedula_paciente,
                'cedula_medico'    => $cedula,
                'fecha_hora'       => $request->fecha_hora,
                'tipo_cita'        => $request->tipo_cita,
                'motivo_consulta'  => $request->motivo_consulta,
                'diagnostico'      => $request->diagnostico,
                'receta'           => $request->receta,
                'examen_laboratorio' => $request->examen_laboratorio,
                'examen_imagenes'  => $request->examen_imagenes,
            ]);

            // Guardar signos vitales si se ingresaron
            if ($request->presion || $request->temperatura || $request->peso) {
                DB::table('SIGNOS_VITALES')->insert([
                    'id_cita'             => $idCita,
                    'presion'             => $request->presion ?: null,
                    'frecuencia_cardiaca' => $request->frecuencia_cardiaca ?: null,
                    'temperatura'         => $request->temperatura ?: null,
                    'peso'                => $request->peso ?: null,
                    'altura'              => $request->altura ? $request->altura / 100 : null,
                ]);
            }

            // Insertar en EXAMENES si el médico ordenó laboratorio
            if (!empty($request->examen_laboratorio)) {
                DB::table('EXAMENES')->insert([
                    'cedula_paciente' => $request->cedula_paciente,
                    'cedula_medico'   => $cedula,
                    'id_cita'         => $idCita,
                    'tipo_examen'     => substr($request->examen_laboratorio, 0, 50),
                    'fecha'           => now()->toDateString(),
                ]);
            }

            // Insertar en EXAMENES si el médico ordenó imágenes
            if (!empty($request->examen_imagenes)) {
                DB::table('EXAMENES')->insert([
                    'cedula_paciente' => $request->cedula_paciente,
                    'cedula_medico'   => $cedula,
                    'id_cita'         => $idCita,
                    'tipo_examen'     => substr($request->examen_imagenes, 0, 50),
                    'fecha'           => now()->toDateString(),
                ]);
            }

            // Si vino de un slot de agenda, marcarlo como atendido
            if ($request->id_agenda) {
                DB::table('AGENDA')
                    ->where('id_agenda', $request->id_agenda)
                    ->update(['estado' => 'asistio', 'id_cita' => $idCita]);
            }

            DB::commit();

            return redirect()
                ->route('medico.citas.index')
                ->with('exito', 'Consulta registrada exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    // ── Agendar cita a un paciente (médico agenda por el paciente) ──
    public function agendarPaciente(Request $request)
    {
        // Paciente pre-seleccionado cuando viene desde la tarjeta de pacientes
        $cedulaPaciente = $request->query('paciente');
        $pacienteSeleccionado = null;
        if ($cedulaPaciente) {
            $pacienteSeleccionado = DB::table('PERSONAS as p')
                ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
                ->where('p.cedula', $cedulaPaciente)
                ->select('p.cedula', 'p.nombres', 'p.apellido1')
                ->first();
        }

        return view('medico.citas.agendar-paciente', compact('pacienteSeleccionado'));
    }

    public function guardarAgendaPaciente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula_paciente' => 'required|string|exists:PACIENTE,cedula',
            'fecha'           => 'required|date|after_or_equal:today',
            'hora'            => 'required',
            'tipo_cita'       => 'required|in:presencial,virtual',
            'motivo'          => 'required|string|min:3|max:200',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        $cedula         = Session::get('usuario_cedula');
        $cedulaPaciente = $request->cedula_paciente;
        $fechaHora      = $request->fecha . ' ' . $request->hora;

        DB::beginTransaction();
        try {
            $slot = DB::table('AGENDA')
                ->where('cedula_medico', $cedula)
                ->where('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->first();

            if ($slot && $slot->estado === 'ocupado') {
                return back()->withInput()->with('error', 'Ese horario ya está ocupado por otro paciente.');
            }

            if ($slot) {
                DB::table('AGENDA')->where('id_agenda', $slot->id_agenda)->update([
                    'cedula_paciente' => $cedulaPaciente,
                    'estado'          => 'ocupado',
                ]);
                $agendaId = $slot->id_agenda;
            } else {
                $agendaId = DB::table('AGENDA')->insertGetId([
                    'cedula_medico'   => $cedula,
                    'cedula_paciente' => $cedulaPaciente,
                    'fecha'           => $request->fecha,
                    'hora'            => $request->hora,
                    'estado'          => 'ocupado',
                ]);
            }

            $idCita = DB::table('CITAS')->insertGetId([
                'cedula_paciente' => $cedulaPaciente,
                'cedula_medico'   => $cedula,
                'id_agenda'       => $agendaId,
                'fecha_hora'      => $fechaHora,
                'tipo_cita'       => $request->tipo_cita,
                'motivo_consulta' => $request->motivo,
            ]);

            DB::table('AGENDA')->where('id_agenda', $agendaId)->update(['id_cita' => $idCita]);

            DB::commit();

            if (SmsService::habilitado()) {
                $paciente   = DB::table('PERSONAS')->where('cedula', $cedulaPaciente)->first();
                $medicoData = DB::table('PERSONAS')->where('cedula', $cedula)->first();
                if ($paciente && $paciente->telefono) {
                    $especialidad = DB::table('MEDICO_ESPECIALIDAD as me')
                        ->join('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
                        ->where('me.cedula_medico', $cedula)
                        ->value('e.nombre') ?? 'General';
                    $nombreMed = strtoupper(trim(($medicoData->nombres ?? '') . ' ' . ($medicoData->apellido1 ?? '')));
                    $nombrePac = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellido1 ?? ''));
                    SmsService::enviar(
                        $paciente->telefono,
                        SmsService::formatearCita(
                            'le informa su cita agendada:',
                            $especialidad,
                            $nombreMed,
                            \Carbon\Carbon::parse($fechaHora)->isoFormat('D [de] MMMM [de] YYYY'),
                            \Carbon\Carbon::parse($fechaHora)->format('H:i'),
                            $idCita,
                            $nombrePac
                        )
                    );
                }
            }

            return redirect()->route('medico.citas.index')
                ->with('exito', 'Cita agendada al paciente exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al agendar: ' . $e->getMessage());
        }
    }

    // ── AJAX: días con disponibilidad del médico logueado ────────────
    public function misDiasDisponibles()
    {
        $cedula     = Session::get('usuario_cedula');
        $hoy        = now()->toDateString();
        $horaActual = now()->format('H:i:s');
        $limite     = now()->addDays(30)->toDateString();

        // Solo días con slots disponibles que aún no han pasado:
        // - Fecha futura: incluir todos sus slots disponibles
        // - Fecha de hoy: solo slots con hora mayor a la actual
        $slots = DB::table('AGENDA')
            ->where('cedula_medico', $cedula)
            ->where('estado', 'disponible')
            ->whereBetween('fecha', [$hoy, $limite])
            ->where(function ($q) use ($hoy, $horaActual) {
                $q->where('fecha', '>', $hoy)
                  ->orWhere(function ($q2) use ($hoy, $horaActual) {
                      $q2->where('fecha', $hoy)->where('hora', '>', $horaActual);
                  });
            })
            ->select('fecha', DB::raw('COUNT(*) AS disponibles'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json($slots->map(function ($s) {
            $d = \Carbon\Carbon::parse($s->fecha);
            return [
                'fecha'       => $s->fecha,
                'dia_semana'  => $d->isoFormat('ddd'),
                'dia_num'     => $d->day,
                'mes'         => $d->isoFormat('MMM'),
                'disponibles' => (int) $s->disponibles,
            ];
        }));
    }

    // ── AJAX: horarios del médico logueado en una fecha ──────────────
    public function misHorarios(Request $request)
    {
        $cedula = Session::get('usuario_cedula');
        $fecha  = $request->query('fecha');

        if (!$fecha) {
            return response()->json(['error' => 'Fecha requerida'], 422);
        }

        $query = DB::table('AGENDA as a')
            ->leftJoin('PERSONAS as p', 'a.cedula_paciente', '=', 'p.cedula')
            ->where('a.cedula_medico', $cedula)
            ->where('a.fecha', $fecha);

        // Si la fecha seleccionada es hoy, ocultar horas que ya pasaron
        if ($fecha === now()->toDateString()) {
            $query->where('a.hora', '>', now()->format('H:i:s'));
        }

        $horarios = $query
            ->select('a.hora', 'a.estado', 'p.nombres', 'p.apellido1')
            ->orderBy('a.hora')
            ->get()
            ->map(fn($h) => [
                'hora'     => substr($h->hora, 0, 5),
                'estado'   => $h->estado,
                'paciente' => $h->nombres ? trim("{$h->nombres} {$h->apellido1}") : null,
            ]);

        return response()->json($horarios);
    }

    // ── Historial clínico global ───────────────────────────────────
    public function historial(Request $request)
    {
        $cedula   = Session::get('usuario_cedula');
        $busqueda = trim($request->query('busqueda', ''));
        $desde    = $request->query('desde', now()->subMonth()->format('Y-m-d'));
        $hasta    = $request->query('hasta', now()->format('Y-m-d'));

        $query = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_medico', $cedula)
            ->where('c.fecha_hora', '<', now())
            ->whereBetween(DB::raw('DATE(c.fecha_hora)'), [$desde, $hasta])
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita',
                'c.motivo_consulta', 'c.diagnostico', 'c.receta',
                'c.examen_laboratorio', 'c.examen_imagenes',
                'p.nombres', 'p.apellido1', 'p.apellido2', 'p.cedula as cedula_paciente',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura',
                'sv.peso', 'sv.altura'
            );

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres',          'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1',       'like', "%{$busqueda}%")
                  ->orWhere('p.cedula',           'like', "%{$busqueda}%")
                  ->orWhere('c.motivo_consulta',  'like', "%{$busqueda}%")
                  ->orWhere('c.diagnostico',      'like', "%{$busqueda}%");
            });
        }

        $historial = $query->orderByDesc('c.fecha_hora')->get();

        return view('medico.citas.historial', [
            'historial' => $historial,
            'busqueda'  => $busqueda,
            'desde'     => $desde,
            'hasta'     => $hasta,
        ]);
    }
}