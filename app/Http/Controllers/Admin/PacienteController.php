<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * PacienteController
 *
 * Gestión de pacientes desde el panel admin: listado con búsqueda,
 * ver detalle completo (historial clínico, signos vitales, contactos
 * de emergencia), editar datos, activar/desactivar y reagendar citas
 * futuras a otro médico.
 *
 * Nota: adaptado al esquema normalizado actual. Los contactos de
 * emergencia viven en CONTACTO_EMERGENCIA (no en PACIENTE), y los
 * signos vitales en SIGNOS_VITALES (no en CITAS).
 */
class PacienteController extends Controller
{
    // ── Listado de pacientes con búsqueda ─────────────────────────
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $query = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->leftJoin('PARROQUIA as par', 'pac.codigo_parroquia', '=', 'par.codigo_parroquia')
            ->leftJoin('CANTON as can', 'par.codigo_canton', '=', 'can.codigo_canton')
            ->leftJoin('PROVINCIA as pro', 'can.codigo_provincia', '=', 'pro.codigo_provincia')
            ->select(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.correo', 'p.telefono', 'p.fecha_nac', 'p.genero', 'p.estado',
                'p.created_at as fecha_registro',
                'pac.tipo_afiliacion', 'pac.tipo_sangre', 'pac.gestante', 'pac.discapacidad',
                'par.nombre_parroquia', 'can.nombre_canton', 'pro.nombre_provincia',
                DB::raw('(SELECT COUNT(*) FROM CITAS WHERE cedula_paciente = p.cedula AND fecha_hora > NOW()) as citas_pendientes'),
                DB::raw('(SELECT cedula_medico FROM CITAS WHERE cedula_paciente = p.cedula AND fecha_hora > NOW() ORDER BY fecha_hora ASC LIMIT 1) as medico_actual'),
                DB::raw("(SELECT CONCAT(per2.nombres, ' ', per2.apellido1) FROM CITAS c2 JOIN PERSONAS per2 ON per2.cedula = c2.cedula_medico WHERE c2.cedula_paciente = p.cedula AND c2.fecha_hora > NOW() ORDER BY c2.fecha_hora ASC LIMIT 1) as medico_actual_nombre")
            );

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido2', 'like', "%{$busqueda}%")
                  ->orWhere('p.cedula', 'like', "%{$busqueda}%");
            });
        }

        $pacientes = $query->orderBy('p.nombres')->orderBy('p.apellido1')->get();

        // Médicos activos para el selector del modal de reagendación
        $medicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Medico')
            ->where('p.estado', 'activo')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2')
            ->orderBy('p.nombres')
            ->get();

        $especialidades = DB::table('ESPECIALIDAD')->orderBy('nombre')->get();

        return view('admin.pacientes.index', [
            'pacientes'      => $pacientes,
            'busqueda'       => $busqueda,
            'medicos'        => $medicos,
            'especialidades' => $especialidades,
        ]);
    }

    // ── Ver detalle completo de un paciente ───────────────────────
    public function show(string $cedula)
    {
        $paciente = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->leftJoin('PARROQUIA as par', 'pac.codigo_parroquia', '=', 'par.codigo_parroquia')
            ->leftJoin('CANTON as can', 'par.codigo_canton', '=', 'can.codigo_canton')
            ->leftJoin('PROVINCIA as pro', 'can.codigo_provincia', '=', 'pro.codigo_provincia')
            ->leftJoin('ETNIA as e', 'pac.id_etnia', '=', 'e.id_etnia')
            ->where('p.cedula', $cedula)
            ->select('p.*', 'pac.*', 'par.nombre_parroquia', 'can.nombre_canton', 'pro.nombre_provincia', 'e.tipo_etnia')
            ->first();

        if (!$paciente) {
            abort(404, 'Paciente no encontrado.');
        }

        $contactos = DB::table('CONTACTO_EMERGENCIA')
            ->where('cedula_paciente', $cedula)
            ->orderByDesc('es_principal')
            ->get();

        $historial = DB::table('CITAS as c')
            ->join('PERSONAS as per', 'c.cedula_medico', '=', 'per.cedula')
            ->leftJoin('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_paciente', $cedula)
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta',
                'c.diagnostico', 'c.receta', 'c.examen_laboratorio', 'c.examen_imagenes',
                'per.nombres as profesional_nombres', 'per.apellido1 as profesional_apellido1',
                'pm.tipo as tipo_profesional',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura', 'sv.peso', 'sv.altura'
            )
            ->orderByDesc('c.fecha_hora')
            ->get();

        $edad = $paciente->fecha_nac
            ? \Carbon\Carbon::parse($paciente->fecha_nac)->age
            : null;

        // Médico asignado: prioridad a próxima cita, si no existe el último que lo atendió
        $medicoAsignado = $this->medicoAsignadoDe($cedula);

        return view('admin.pacientes.detalle', [
            'paciente'        => $paciente,
            'contactos'       => $contactos,
            'historial'       => $historial,
            'edad'            => $edad,
            'medicoAsignado'  => $medicoAsignado,
        ]);
    }

    // ── Formulario de edición ──────────────────────────────────────
    public function edit(string $cedula)
    {
        $paciente = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->where('p.cedula', $cedula)
            ->select('p.*', 'pac.*')
            ->first();

        if (!$paciente) {
            abort(404, 'Paciente no encontrado.');
        }

        $contacto = DB::table('CONTACTO_EMERGENCIA')
            ->where('cedula_paciente', $cedula)
            ->where('es_principal', 1)
            ->first();

        return view('admin.pacientes.editar', [
            'paciente' => $paciente,
            'contacto' => $contacto,
        ]);
    }

    // ── Actualizar paciente ──────────────────────────────────────────
    public function update(Request $request, string $cedula)
    {
        $validator = Validator::make($request->all(), [
            'nombres'   => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'correo'    => 'required|email|max:100',
            'telefono'  => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            DB::table('PERSONAS')->where('cedula', $cedula)->update([
                'nombres'   => trim($request->nombres),
                'apellido1' => trim($request->apellido1),
                'apellido2' => trim($request->apellido2 ?? ''),
                'correo'    => trim($request->correo),
                'telefono'  => trim($request->telefono ?? ''),
                'estado'    => $request->estado ?? 'activo',
            ]);

            DB::table('PACIENTE')->where('cedula', $cedula)->update([
                'tipo_sangre'         => $request->tipo_sangre ?? 'Desconocido',
                'alergias'            => $request->alergias,
                'antecedentes'        => $request->antecedentes,
                'medicacion_habitual' => $request->medicacion_habitual,
                'barrio'              => $request->barrio,
                'direccion_1'         => $request->direccion_1,
            ]);

            // Contacto de emergencia principal (upsert)
            if (!empty($request->contacto_nombre)) {
                DB::table('CONTACTO_EMERGENCIA')
                    ->where('cedula_paciente', $cedula)
                    ->where('es_principal', 1)
                    ->delete();

                DB::table('CONTACTO_EMERGENCIA')->insert([
                    'cedula_paciente' => $cedula,
                    'nombre'          => $request->contacto_nombre,
                    'parentesco'      => $request->contacto_parentesco,
                    'telefono'        => $request->contacto_telefono,
                    'es_principal'    => 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.pacientes.show', $cedula)
                ->with('exito', 'Paciente actualizado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar paciente: ' . $e->getMessage());
        }
    }

    // ── Eliminar paciente y todos sus registros relacionados ─────
    public function destroy(string $cedula)
    {
        $paciente = DB::table('PACIENTE')->where('cedula', $cedula)->first();

        if (!$paciente) {
            abort(404, 'Paciente no encontrado.');
        }

        $persona  = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $nombre   = $persona ? "{$persona->nombres} {$persona->apellido1}" : $cedula;

        // IDs de citas del paciente (SIGNOS_VITALES y EXAMENES apuntan a id_cita)
        $citasIds = DB::table('CITAS')
            ->where('cedula_paciente', $cedula)
            ->pluck('id_cita')
            ->toArray();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            if (!empty($citasIds)) {
                DB::table('SIGNOS_VITALES')->whereIn('id_cita', $citasIds)->delete();
                DB::table('EXAMENES')->whereIn('id_cita', $citasIds)->delete();
            }
            DB::table('CONTACTO_EMERGENCIA')->where('cedula_paciente', $cedula)->delete();
            DB::table('AGENDA')
                ->where('cedula_paciente', $cedula)
                ->update(['cedula_paciente' => null, 'id_cita' => null, 'estado' => 'disponible']);
            DB::table('CITAS')->where('cedula_paciente', $cedula)->delete();
            DB::table('PACIENTE')->where('cedula', $cedula)->delete();
            DB::table('PERSONAS')->where('cedula', $cedula)->delete();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        \App\Services\Auditoria::registrar('eliminar_paciente', "Paciente eliminado: {$cedula} — {$nombre}");

        return redirect()
            ->route('admin.pacientes.index')
            ->with('exito', "Paciente «{$nombre}» eliminado permanentemente.");
    }

    // ── Activar / Desactivar paciente ────────────────────────────
    public function toggleEstado(string $cedula)
    {
        $paciente = DB::table('PERSONAS')->where('cedula', $cedula)->first();

        if (!$paciente) {
            abort(404, 'Paciente no encontrado.');
        }

        $nuevoEstado = $paciente->estado === 'activo' ? 'inactivo' : 'activo';

        DB::table('PERSONAS')->where('cedula', $cedula)->update(['estado' => $nuevoEstado]);

        return back()->with('exito', "Paciente marcado como {$nuevoEstado}.");
    }

    // ── Reagendar: transferir citas futuras a otro médico ─────────
    public function reagendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula_paciente'      => 'required|string|exists:PACIENTE,cedula',
            'cedula_medico_nuevo'  => 'required|string|exists:PERSONAL_MEDICO,cedula',
            'fecha'                => 'required|date',
            'hora'                 => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $cedulaPaciente    = $request->cedula_paciente;
        $cedulaMedicoNuevo = $request->cedula_medico_nuevo;
        $motivo            = trim($request->motivo_reagendacion ?: 'Asignación de cita');

        DB::beginTransaction();
        try {
            $citasFuturas = DB::table('CITAS')
                ->where('cedula_paciente', $cedulaPaciente)
                ->where('fecha_hora', '>', now())
                ->get();

            $esNuevaCita = $citasFuturas->isEmpty();

            // ── Buscar o crear slot de agenda para el médico / fecha / hora indicados ──
            $slotExistente = DB::table('AGENDA')
                ->where('cedula_medico', $cedulaMedicoNuevo)
                ->where('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->first();

            if ($slotExistente) {
                if ($slotExistente->estado === 'ocupado' && $slotExistente->cedula_paciente !== $cedulaPaciente) {
                    throw new \Exception("El horario {$request->hora} del {$request->fecha} ya está ocupado por otro paciente.");
                }
                DB::table('AGENDA')->where('id_agenda', $slotExistente->id_agenda)->update([
                    'cedula_paciente' => $cedulaPaciente,
                    'estado'          => 'ocupado',
                ]);
                $nuevaAgendaId = $slotExistente->id_agenda;
            } else {
                $nuevaAgendaId = DB::table('AGENDA')->insertGetId([
                    'cedula_medico'   => $cedulaMedicoNuevo,
                    'cedula_paciente' => $cedulaPaciente,
                    'fecha'           => $request->fecha,
                    'hora'            => $request->hora,
                    'estado'          => 'ocupado',
                ]);
            }

            if ($esNuevaCita) {
                // Sin citas previas → crear la primera cita del paciente
                $idCita = DB::table('CITAS')->insertGetId([
                    'cedula_paciente' => $cedulaPaciente,
                    'cedula_medico'   => $cedulaMedicoNuevo,
                    'id_agenda'       => $nuevaAgendaId,
                    'fecha_hora'      => $request->fecha . ' ' . $request->hora,
                    'tipo_cita'       => $request->input('tipo_cita', 'presencial'),
                    'motivo_consulta' => $motivo,
                ]);
                DB::table('AGENDA')->where('id_agenda', $nuevaAgendaId)->update(['id_cita' => $idCita]);
                $mensajeWa  = 'le informa su cita agendada:';
                $exitoMsg   = 'Cita agendada exitosamente.';
            } else {
                // Tiene citas futuras → reagendarlas al nuevo médico / horario
                foreach ($citasFuturas as $cita) {
                    if ($cita->id_agenda) {
                        DB::table('AGENDA')->where('id_agenda', $cita->id_agenda)->update(['estado' => 'cancelado']);
                    }
                    DB::table('CITAS')->where('id_cita', $cita->id_cita)->update([
                        'cedula_medico'   => $cedulaMedicoNuevo,
                        'fecha_hora'      => $request->fecha . ' ' . $request->hora,
                        'id_agenda'       => $nuevaAgendaId,
                        'tipo_cita'       => $request->input('tipo_cita', 'presencial'),
                        'motivo_consulta' => $motivo,
                    ]);
                }
                $idCita    = $citasFuturas->first()->id_cita;
                $mensajeWa = 'le informa que su cita ha sido reagendada:';
                $exitoMsg  = "Paciente reagendado exitosamente. {$citasFuturas->count()} cita(s) transferidas.";
            }

            DB::commit();

            if (SmsService::habilitado()) {
                $paciente    = DB::table('PERSONAS')->where('cedula', $cedulaPaciente)->first();
                $medicoNuevo = DB::table('PERSONAS')->where('cedula', $cedulaMedicoNuevo)->first();
                if ($paciente && $paciente->telefono) {
                    $especialidad = DB::table('MEDICO_ESPECIALIDAD as me')
                        ->join('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
                        ->where('me.cedula_medico', $cedulaMedicoNuevo)
                        ->value('e.nombre') ?? 'General';
                    $fechaFmt  = \Carbon\Carbon::parse("{$request->fecha} {$request->hora}")->isoFormat('D [de] MMMM [de] YYYY');
                    $horaFmt   = \Carbon\Carbon::parse("{$request->fecha} {$request->hora}")->format('H:i');
                    $nombreMed = strtoupper(trim(($medicoNuevo->nombres ?? '') . ' ' . ($medicoNuevo->apellido1 ?? '')));
                    $nombrePac = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellido1 ?? ''));
                    SmsService::enviar(
                        $paciente->telefono,
                        SmsService::formatearCita($mensajeWa, $especialidad, $nombreMed, $fechaFmt, $horaFmt, $idCita, $nombrePac)
                    );
                }
            }

            return redirect()->route('admin.pacientes.index')->with('exito', $exitoMsg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al reagendar: ' . $e->getMessage());
        }
    }

    // ── AJAX: médicos disponibles por especialidad ────────────────
    public function medicosPorEspecialidad(int $especialidadId)
    {
        $medicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->join('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->where('me.id_especialidad', $especialidadId)
            ->where('p.estado', 'activo')
            ->select('p.cedula', 'p.nombres', 'p.apellido1')
            ->orderBy('p.nombres')
            ->get();

        return response()->json($medicos);
    }

    // ── AJAX: días reales de la agenda de un médico ──────────────
    public function diasDisponibles(Request $request)
    {
        $medicoId = $request->input('medico');
        if (!$medicoId) {
            return response()->json([]);
        }

        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses      = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $fechas = DB::table('AGENDA')
            ->where('cedula_medico', $medicoId)
            ->where('fecha', '>=', now()->format('Y-m-d'))
            ->whereIn('estado', ['disponible', 'ocupado'])
            ->select(
                'fecha',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles")
            )
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->limit(30)
            ->get();

        $dias = $fechas->map(function ($row) use ($diasSemana, $meses) {
            $carbon = \Carbon\Carbon::parse($row->fecha);
            return [
                'fecha'       => $row->fecha,
                'dia_semana'  => $diasSemana[$carbon->dayOfWeek],
                'dia_num'     => (int) $carbon->format('j'),
                'mes'         => $meses[(int) $carbon->format('n')],
                'disponibles' => (int) $row->disponibles,
                'total'       => (int) $row->total,
            ];
        });

        return response()->json($dias);
    }

    // ── AJAX: slots reales de un médico en una fecha ─────────────
    public function agendaDisponible(Request $request)
    {
        $medicoId = $request->input('medico');
        $fecha    = $request->input('fecha');

        $medico = DB::table('PERSONAS as p')
            ->join('MEDICO_ESPECIALIDAD as me', 'p.cedula', '=', 'me.cedula_medico')
            ->join('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('p.cedula', $medicoId)
            ->select('p.nombres', 'p.apellido1', 'e.nombre as especialidad')
            ->first();

        // Solo slots que el médico registró en su agenda para esa fecha
        $slots = DB::table('AGENDA')
            ->where('cedula_medico', $medicoId)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['disponible', 'ocupado'])
            ->orderBy('hora')
            ->get(['id_agenda', 'hora', 'estado', 'cedula_paciente']);

        $horarios = $slots->map(fn ($s) => [
            'hora'          => substr($s->hora, 0, 5),
            'hora_completa' => $s->hora,
            'estado'        => $s->estado,
            'paciente'      => $s->cedula_paciente ? ['nombre' => $s->cedula_paciente] : null,
        ]);

        return response()->json([
            'medico'         => $medico,
            'horarios'       => $horarios,
            'hora_servidor'  => now()->format('H:i:s'),
            'fecha_servidor' => now()->format('Y-m-d'),
        ]);
    }

    // ── Helper: médico asignado a un paciente ─────────────────────
    private function medicoAsignadoDe(string $cedula): ?object
    {
        $base = DB::table('CITAS as c')
            ->join('PERSONAS as per', 'c.cedula_medico', '=', 'per.cedula')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('c.cedula_paciente', $cedula)
            ->where('pm.tipo', 'Medico')
            ->select('per.cedula', 'per.nombres', 'per.apellido1', 'e.nombre as especialidad', 'c.fecha_hora');

        // Próxima cita programada
        $medico = (clone $base)->where('c.fecha_hora', '>', now())->orderBy('c.fecha_hora')->first();

        // Si no hay cita futura, el último que lo atendió
        return $medico ?? (clone $base)->orderByDesc('c.fecha_hora')->first();
    }
}