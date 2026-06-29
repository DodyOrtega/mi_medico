<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class PacienteController extends Controller
{
    // ================================================================
    // HELPER — cédula desde sesión (el login NO usa Auth::login())
    // ================================================================

    private function cedula(): string
    {
        return Session::get('usuario_cedula');
    }

    private function datosLayout(): array
    {
        $cedula   = $this->cedula();
        $persona  = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $paciente = DB::table('PACIENTE')->where('cedula', $cedula)->first();

        $nombreCompleto = trim(
            $persona->nombres . ' ' . $persona->apellido1 . ' ' . ($persona->apellido2 ?? '')
        );
        $iniciales = strtoupper(
            substr($persona->nombres, 0, 1) . substr($persona->apellido1, 0, 1)
        );

        return [$persona, $paciente, $nombreCompleto, $iniciales];
    }

    // ================================================================
    // 6.1 DASHBOARD
    // ================================================================

    public function dashboard()
    {
        $cedula = $this->cedula();

        $persona  = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $paciente = DB::table('PACIENTE')->where('cedula', $cedula)->first();

        $proximaCita = DB::table('CITAS as c')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->join('PERSONAS as m', 'pm.cedula', '=', 'm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('c.cedula_paciente', $cedula)
            ->where('c.fecha_hora', '>=', now())
            ->orderBy('c.fecha_hora')
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad'
            )
            ->first();

        $examenes = DB::table('EXAMENES')
            ->where('cedula_paciente', $cedula)
            ->orderByDesc('fecha')
            ->limit(5)
            ->get();

        $recetas = DB::table('CITAS as c')
            ->join('PERSONAS as m', 'c.cedula_medico', '=', 'm.cedula')
            ->where('c.cedula_paciente', $cedula)
            ->whereNotNull('c.receta')
            ->where('c.receta', '!=', '')
            ->orderByDesc('c.fecha_hora')
            ->limit(5)
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.receta', 'c.diagnostico',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1'
            )
            ->get();

        $notifCount = DB::table('AGENDA')
            ->where('cedula_paciente', $cedula)
            ->where('fecha', '>=', today())
            ->where('estado', 'ocupado')
            ->count();

        $nombreCompleto = trim(
            $persona->nombres . ' ' . $persona->apellido1 . ' ' . ($persona->apellido2 ?? '')
        );
        $iniciales = strtoupper(
            substr($persona->nombres, 0, 1) . substr($persona->apellido1, 0, 1)
        );

        $showWelcome = Session::pull('login_success', false);

        return view('paciente.dashboard', compact(
            'persona', 'paciente', 'proximaCita',
            'examenes', 'recetas', 'notifCount',
            'nombreCompleto', 'iniciales', 'showWelcome'
        ));
    }

    // ================================================================
    // 6.2 MIS CITAS
    // ================================================================

    public function misCitas()
    {
        $cedula = $this->cedula();

        $citas = DB::table('CITAS as c')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->join('PERSONAS as m', 'pm.cedula', '=', 'm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_paciente', $cedula)
            ->where('c.fecha_hora', '>=', now())
            ->orderBy('c.fecha_hora')
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta',
                'c.diagnostico', 'c.receta',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura',
                'sv.peso', 'sv.altura',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad'
            )
            ->get();

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.mis_citas', compact(
            'citas', 'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    public function detalleCita($id)
    {
        $cedula = $this->cedula();

        $cita = DB::table('CITAS as c')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->join('PERSONAS as m', 'pm.cedula', '=', 'm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.id_cita', $id)
            ->where('c.cedula_paciente', $cedula)
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta',
                'c.diagnostico', 'c.receta', 'c.examen_laboratorio', 'c.examen_imagenes',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura',
                'sv.peso', 'sv.altura',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad'
            )
            ->first();

        if (! $cita) {
            return redirect()->route('paciente.citas')
                             ->with('error', 'Cita no encontrada.');
        }

        $imc       = null;
        $clasifImc = null;
        if ($cita->peso && $cita->altura && $cita->altura > 0) {
            $imc = round($cita->peso / ($cita->altura ** 2), 1);
            $clasifImc = match(true) {
                $imc < 18.5  => 'Bajo peso',
                $imc <= 24.9 => 'Normal',
                $imc <= 29.9 => 'Sobrepeso',
                default      => 'Obesidad',
            };
        }

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.detalle_cita', compact(
            'cita', 'imc', 'clasifImc',
            'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    public function cancelarCita(Request $request)
    {
        $cedula   = $this->cedula();
        $idCita   = $request->input('id_cita');
        $isAgenda = $request->boolean('is_agenda');

        try {
            if ($isAgenda) {
                $agenda = DB::table('AGENDA')
                    ->where('id_agenda', $idCita)
                    ->where('cedula_paciente', $cedula)
                    ->where('estado', 'ocupado')
                    ->first();

                if (! $agenda) {
                    return response()->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
                }

                DB::table('AGENDA')
                    ->where('id_agenda', $idCita)
                    ->update(['estado' => 'disponible', 'cedula_paciente' => null]);

                return response()->json(['success' => true, 'message' => 'Reserva cancelada correctamente.']);
            }

            $cita = DB::table('CITAS as c')
                ->leftJoin('AGENDA as a', function ($join) {
                    $join->on('c.id_agenda', '=', 'a.id_agenda');
                })
                ->where('c.id_cita', $idCita)
                ->where('c.cedula_paciente', $cedula)
                ->select('c.id_cita', 'a.id_agenda')
                ->first();

            if (! $cita) {
                return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
            }

            DB::transaction(function () use ($idCita, $cita) {
                DB::table('CITAS')->where('id_cita', $idCita)->delete();

                if ($cita->id_agenda) {
                    DB::table('AGENDA')
                        ->where('id_agenda', $cita->id_agenda)
                        ->update(['estado' => 'disponible', 'cedula_paciente' => null]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Cita cancelada correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ================================================================
    // 6.3 AGENDAR CITA
    // ================================================================

    public function agendarCita()
    {
        $medicos = DB::table('PERSONAL_MEDICO as pm')
            ->join('PERSONAS as p', 'pm.cedula', '=', 'p.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('pm.tipo', 'Medico')
            ->where('p.estado', 'activo')
            ->orderBy('p.apellido1')
            ->select(
                'pm.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                DB::raw("GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') as especialidades")
            )
            ->groupBy('pm.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2')
            ->get();

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.agendar_cita', compact(
            'medicos', 'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    public function guardarCita(Request $request)
    {
        $request->validate([
            'medico'    => 'required|string',
            'fecha'     => 'required|date|after_or_equal:today',
            'hora'      => 'required',
            'tipo_cita' => 'required|in:presencial,virtual',
            'motivo'    => 'required|string|min:5|max:200',
        ]);

        $cedula       = $this->cedula();
        $cedulaMedico = $request->medico;
        $fecha        = $request->fecha;
        $hora         = $request->hora;
        $fechaHora    = $fecha . ' ' . $hora;

        $pendiente = DB::table('CITAS')
            ->where('cedula_paciente', $cedula)
            ->where('cedula_medico', $cedulaMedico)
            ->where('fecha_hora', '>=', now())
            ->exists();

        if ($pendiente) {
            return back()->withErrors(['medico' => 'Ya tienes una cita pendiente con este médico.'])->withInput();
        }

        $slot = DB::table('AGENDA')
            ->where('cedula_medico', $cedulaMedico)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('estado', 'disponible')
            ->first();

        if (! $slot) {
            return back()->withErrors(['hora' => 'El horario seleccionado ya no está disponible.'])->withInput();
        }

        DB::transaction(function () use ($cedula, $cedulaMedico, $fechaHora, $request, $slot) {
            $idCita = DB::table('CITAS')->insertGetId([
                'id_agenda'       => $slot->id_agenda,
                'cedula_paciente' => $cedula,
                'cedula_medico'   => $cedulaMedico,
                'fecha_hora'      => $fechaHora,
                'tipo_cita'       => $request->tipo_cita,
                'motivo_consulta' => trim($request->motivo),
            ]);

            DB::table('AGENDA')
                ->where('id_agenda', $slot->id_agenda)
                ->update([
                    'estado'          => 'ocupado',
                    'cedula_paciente' => $cedula,
                    'id_cita'         => $idCita,
                ]);
        });

        if (SmsService::habilitado()) {
            $paciente = DB::table('PERSONAS')->where('cedula', $cedula)->first();
            $medico   = DB::table('PERSONAS')->where('cedula', $cedulaMedico)->first();
            if ($paciente && $paciente->telefono) {
                $especialidad = DB::table('MEDICO_ESPECIALIDAD as me')
                    ->join('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
                    ->where('me.cedula_medico', $cedulaMedico)
                    ->value('e.nombre') ?? 'General';

                $idCita      = DB::table('CITAS')->where('cedula_paciente', $cedula)->where('fecha_hora', $fechaHora)->value('id_cita') ?? 0;
                $fechaFmt    = \Carbon\Carbon::parse($fechaHora)->isoFormat('D [de] MMMM [de] YYYY');
                $horaFmt     = \Carbon\Carbon::parse($fechaHora)->format('H:i');
                $nombreMed   = strtoupper(trim(($medico->nombres ?? '') . ' ' . ($medico->apellido1 ?? '')));
                $nombrePac   = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellido1 ?? ''));

                SmsService::enviar(
                    $paciente->telefono,
                    SmsService::formatearCita('le informa su cita agendada:', $especialidad, $nombreMed, $fechaFmt, $horaFmt, $idCita, $nombrePac)
                );
            }
        }

        return redirect()->route('paciente.citas')
                         ->with('success', 'Cita agendada correctamente.');
    }

    public function horariosDisponibles(Request $request)
    {
        $medico = $request->query('medico');
        $fecha  = $request->query('fecha');

        $slots = DB::table('AGENDA')
            ->where('cedula_medico', $medico)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['disponible', 'ocupado'])
            ->orderBy('hora')
            ->get(['hora', 'estado']);

        return response()->json($slots);
    }

    // ================================================================
    // 6.4 HISTORIAL
    // ================================================================

    public function historial()
    {
        $cedula = $this->cedula();

        $pacienteInfo = DB::table('PERSONAS as p')
            ->leftJoin('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->where('p.cedula', $cedula)
            ->select(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.fecha_nac', 'p.genero', 'p.estado_civil', 'p.telefono',
                'pac.tipo_sangre', 'pac.nacionalidad', 'pac.tipo_afiliacion',
                'pac.numero_afiliado', 'pac.alergias', 'pac.antecedentes',
                'pac.medicacion_habitual', 'pac.discapacidad', 'pac.tipo_discapacidad',
                'pac.gestante', 'pac.semanas_gestacion',
                'pac.fumador', 'pac.consume_alcohol',
                'pac.direccion_1', 'pac.barrio'
            )
            ->first();

        $contactos = DB::table('CONTACTO_EMERGENCIA')
            ->where('cedula_paciente', $cedula)
            ->get();

        $historial = DB::table('CITAS as c')
            ->join('PERSONAS as m', 'c.cedula_medico', '=', 'm.cedula')
            ->leftJoin(DB::raw('(SELECT cedula_medico, MIN(id_especialidad) as id_esp FROM MEDICO_ESPECIALIDAD GROUP BY cedula_medico) as me_sub'), 'c.cedula_medico', '=', 'me_sub.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me_sub.id_esp', '=', 'e.id_especialidad')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_paciente', $cedula)
            ->where('c.fecha_hora', '<', now())
            ->orderByDesc('c.fecha_hora')
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita',
                'c.motivo_consulta', 'c.diagnostico', 'c.receta',
                'c.examen_laboratorio', 'c.examen_imagenes',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura',
                'sv.peso', 'sv.altura'
            )
            ->get();

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.historial', compact(
            'pacienteInfo', 'contactos', 'historial',
            'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    // ================================================================
    // 6.5 RECETAS
    // ================================================================

    public function recetas()
    {
        $cedula = $this->cedula();

        $recetas = DB::table('CITAS as c')
            ->join('PERSONAS as m', 'c.cedula_medico', '=', 'm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'c.cedula_medico', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('c.cedula_paciente', $cedula)
            ->whereNotNull('c.receta')
            ->where('c.receta', '!=', '')
            ->orderByDesc('c.fecha_hora')
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.receta', 'c.diagnostico',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad'
            )
            ->get();

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.recetas', compact(
            'recetas', 'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    // ================================================================
    // 6.6 DOCUMENTOS (usa EXAMENES — tabla documentos_paciente no existe)
    // ================================================================

    public function documentos()
    {
        $cedula = $this->cedula();

        $examenesBd = DB::table('EXAMENES')
            ->where('cedula_paciente', $cedula)
            ->orderByDesc('fecha')
            ->get();

        $archivos = \App\Services\ExamenService::listar($cedula);

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.documentos', compact(
            'examenesBd', 'archivos', 'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    // ================================================================
    // 6.7 TELECONSULTAS
    // ================================================================

    public function teleconsultas()
    {
        $cedula = $this->cedula();

        $teleconsultas = DB::table('CITAS as c')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->join('PERSONAS as m', 'pm.cedula', '=', 'm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('c.cedula_paciente', $cedula)
            ->where('c.tipo_cita', 'virtual')
            ->orderByDesc('c.fecha_hora')
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.motivo_consulta',
                'm.nombres as medico_nombres', 'm.apellido1 as medico_apellido1',
                'e.nombre as especialidad'
            )
            ->get();

        [$persona, $paciente, $nombreCompleto, $iniciales] = $this->datosLayout();

        return view('paciente.teleconsultas', compact(
            'teleconsultas', 'persona', 'paciente', 'nombreCompleto', 'iniciales'
        ));
    }

    // ================================================================
    // 6.8 PERFIL
    // ================================================================

    public function perfil()
    {
        $cedula = $this->cedula();

        $persona  = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $paciente = DB::table('PACIENTE')->where('cedula', $cedula)->first();

        [, , $nombreCompleto, $iniciales] = $this->datosLayout();

        $base = DB::table('CITAS as c')
            ->join('PERSONAS as per', 'c.cedula_medico', '=', 'per.cedula')
            ->join('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('c.cedula_paciente', $cedula)
            ->where('pm.tipo', 'Medico')
            ->select('per.nombres', 'per.apellido1', 'e.nombre as especialidad', 'c.fecha_hora');

        $medicoAsignado = (clone $base)->where('c.fecha_hora', '>', now())->orderBy('c.fecha_hora')->first()
                       ?? (clone $base)->orderByDesc('c.fecha_hora')->first();

        return view('paciente.perfil', compact(
            'persona', 'paciente', 'nombreCompleto', 'iniciales', 'medicoAsignado'
        ));
    }

    public function actualizarPerfil(Request $request)
    {
        $request->validate([
            'nombres'     => 'required|string|max:40',
            'apellido1'   => 'required|string|max:40',
            'apellido2'   => 'nullable|string|max:40',
            'correo'      => 'required|email|max:100',
            'telefono'    => 'nullable|string|max:15',
            'fecha_nac'   => 'nullable|date',
            'estado_civil' => 'nullable|in:S,C,V,D',
        ]);

        $cedula = $this->cedula();

        $data = [
            'nombres'      => $request->nombres,
            'apellido1'    => $request->apellido1,
            'apellido2'    => $request->apellido2,
            'correo'       => $request->correo,
            'telefono'     => $request->telefono,
            'fecha_nac'    => $request->fecha_nac ?: null,
            'estado_civil' => $request->estado_civil ?: null,
        ];

        if ($request->filled('new_password')) {
            $minPass = (int) (ConfiguracionController::leerConfig()['seguridad']['pass_min_length'] ?? 8);
            $request->validate([
                'current_password' => 'required',
                'new_password'     => "required|min:{$minPass}|confirmed",
            ], [
                'new_password.min' => "La contraseña debe tener al menos {$minPass} caracteres.",
            ]);

            $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
            if (! Hash::check($request->current_password, $persona->contrasena)) {
                return back()->withErrors(['current_password' => 'Contraseña actual incorrecta.']);
            }

            $data['contrasena'] = Hash::make($request->new_password);
        }

        DB::table('PERSONAS')->where('cedula', $cedula)->update($data);

        Session::put('usuario_nombre', trim($request->nombres . ' ' . $request->apellido1));

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    // ================================================================
    // 6.9 EMERGENCIA (AJAX)
    // ================================================================

    public function solicitarEmergencia(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|min:3|max:500',
            'prioridad'   => 'nullable|in:baja,media,alta,urgente',
        ]);

        $cedula    = $this->cedula();
        $prioridad = $request->input('prioridad', 'media');

        // Médico de la cita más reciente del paciente
        $medico = DB::table('CITAS')
            ->where('cedula_paciente', $cedula)
            ->whereNotNull('cedula_medico')
            ->orderByDesc('fecha_hora')
            ->value('cedula_medico');

        // Fallback: cualquier médico activo si el paciente no tiene citas previas
        if (! $medico) {
            $medico = DB::table('PERSONAL_MEDICO as pm')
                ->join('PERSONAS as p', 'pm.cedula', '=', 'p.cedula')
                ->where('pm.tipo', 'Medico')
                ->where('p.estado', 'activo')
                ->value('pm.cedula');
        }

        if (! $medico) {
            return response()->json([
                'success' => false,
                'message' => 'No hay médicos disponibles en este momento.'
            ], 422);
        }

        DB::table('AGENDA')->insert([
            'cedula_medico'     => $medico,
            'cedula_paciente'   => $cedula,
            'fecha'             => today(),
            'hora'              => now()->format('H:i:s'),
            'estado'            => 'disponible',
            'es_emergencia'     => 1,
            'motivo_emergencia' => "[{$prioridad}] " . $request->descripcion,
        ]);

        // Notificar al médico por WhatsApp
        $paciente   = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $medicoData = DB::table('PERSONAS')->where('cedula', $medico)->first();

        if ($medicoData && $medicoData->telefono) {
            $nombrePac   = strtoupper(trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellido1 ?? '')));
            $telPac      = $paciente->telefono ?? 'No registrado';
            $linkPaciente = url("/medico/pacientes/{$cedula}");

            $mensaje = implode("\n", [
                "🚨 *EMERGENCIA - Mi Médico*",
                "",
                "👤 Paciente: {$nombrePac}",
                "📋 Cédula: {$cedula}",
                "📞 Teléfono: {$telPac}",
                "",
                "📝 Motivo: " . $request->descripcion,
                "",
                "⏰ " . now()->isoFormat('D [de] MMMM [de] YYYY · HH:mm'),
                "",
                "🔗 Ver historial del paciente:",
                $linkPaciente,
            ]);

            SmsService::enviar($medicoData->telefono, $mensaje);
        }

        return response()->json([
            'success' => true,
            'message' => 'Emergencia registrada. El personal fue notificado.'
        ]);
    }
}