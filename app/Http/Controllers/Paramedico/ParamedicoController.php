<?php

namespace App\Http\Controllers\Paramedico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ParamedicoController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────
    public function dashboard()
    {
        $cedula = Session::get('usuario_cedula');

        $stats = [
            'atenciones_hoy'  => DB::table('CITAS')->where('cedula_medico', $cedula)->whereDate('fecha_hora', today())->count(),
            'atenciones_mes'  => DB::table('CITAS')->where('cedula_medico', $cedula)->whereMonth('fecha_hora', now()->month)->count(),
            'total_pacientes' => DB::table('CITAS')->where('cedula_medico', $cedula)->distinct('cedula_paciente')->count('cedula_paciente'),
            'emergencias_hoy' => DB::table('AGENDA')->where('cedula_medico', $cedula)->where('es_emergencia', 1)->where('fecha', today())->count(),
        ];

        $citasHoy = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->whereDate('c.fecha_hora', today())
            ->select('c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta', 'p.nombres', 'p.apellido1')
            ->orderBy('c.fecha_hora')->get();

        $todosModulos = [
            ['clave'=>'pacientes',   'icono'=>'users',                'titulo'=>'Mis Pacientes',    'descripcion'=>'Gestiona los pacientes a tu cargo.',         'ruta'=>route('paramedico.pacientes.index'),  'color'=>'blue'],
            ['clave'=>'citas',       'icono'=>'calendar-plus',        'titulo'=>'Agendar Cita',     'descripcion'=>'Agenda cita con médico especialista.',        'ruta'=>route('paramedico.citas.agendar'),    'color'=>'green'],
            ['clave'=>'atencion',    'icono'=>'user-plus',            'titulo'=>'Primera Atención', 'descripcion'=>'Registra signos vitales y diagnóstico.',      'ruta'=>route('paramedico.atencion.index'),   'color'=>'teal'],
            ['clave'=>'emergencias', 'icono'=>'triangle-exclamation', 'titulo'=>'Emergencias',      'descripcion'=>'Gestiona alertas y emergencias activas.',     'ruta'=>route('paramedico.emergencias.index'),'color'=>'red'],
            ['clave'=>'video',       'icono'=>'video',                'titulo'=>'Videollamadas',    'descripcion'=>'Accede a consultas virtuales en línea.',      'ruta'=>route('paramedico.video.index'),      'color'=>'purple'],
        ];
        $modulos = array_filter($todosModulos,
            fn($m) => \App\Services\Permisos::tienePermiso($cedula, $m['clave']));

        return view('paramedico.dashboard', [
            'stats'       => $stats,
            'citasHoy'    => $citasHoy,
            'modulos'     => $modulos,
            'showWelcome' => Session::pull('login_success', false),
        ]);
    }

    // ── Listado pacientes ─────────────────────────────────────────
    public function pacientes(Request $request)
    {
        $cedula   = Session::get('usuario_cedula');
        $busqueda = trim($request->get('busqueda', ''));

        $query = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->whereExists(function ($sub) use ($cedula) {
                $sub->select(DB::raw(1))->from('CITAS')
                    ->whereColumn('cedula_paciente', 'p.cedula')
                    ->where('cedula_medico', $cedula);
            })
            ->select('p.cedula','p.nombres','p.apellido1','p.apellido2',
                     'p.telefono','p.estado','pac.tipo_sangre','pac.alergias',
                DB::raw('(SELECT MAX(fecha_hora) FROM CITAS WHERE cedula_paciente=p.cedula AND cedula_medico="'.$cedula.'") as ultima_atencion'));

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres','like',"%{$busqueda}%")
                  ->orWhere('p.apellido1','like',"%{$busqueda}%")
                  ->orWhere('p.cedula','like',"%{$busqueda}%");
            });
        }

        return view('paramedico.pacientes', [
            'pacientes' => $query->orderBy('p.nombres')->get(),
            'busqueda'  => $busqueda,
        ]);
    }

    // ── Ver detalle paciente ──────────────────────────────────────
    public function verPaciente(string $cedula)
    {
        $paramedicoCedula = Session::get('usuario_cedula');

        $paciente = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->leftJoin('PARROQUIA as par', 'pac.codigo_parroquia', '=', 'par.codigo_parroquia')
            ->leftJoin('CANTON as can', 'par.codigo_canton', '=', 'can.codigo_canton')
            ->leftJoin('PROVINCIA as pro', 'can.codigo_provincia', '=', 'pro.codigo_provincia')
            ->where('p.cedula', $cedula)
            ->select('p.*', 'pac.*', 'par.nombre_parroquia', 'can.nombre_canton', 'pro.nombre_provincia')
            ->first();

        if (!$paciente) abort(404, 'Paciente no encontrado.');

        $contactos = DB::table('CONTACTO_EMERGENCIA')->where('cedula_paciente', $cedula)->orderByDesc('es_principal')->get();

        $historial = DB::table('CITAS as c')
            ->join('PERSONAS as per', 'c.cedula_medico', '=', 'per.cedula')
            ->leftJoin('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_paciente', $cedula)
            ->select('c.id_cita','c.fecha_hora','c.tipo_cita','c.motivo_consulta','c.diagnostico','c.receta',
                     'c.cedula_medico',
                     'per.nombres as prof_nombres', 'per.apellido1 as prof_apellido1',
                     'pm.tipo as tipo_profesional',
                     'sv.presion','sv.frecuencia_cardiaca','sv.temperatura','sv.peso','sv.altura')
            ->orderByDesc('c.fecha_hora')->get();

        return view('paramedico.ver_paciente', [
            'paciente'         => $paciente,
            'contactos'        => $contactos,
            'historial'        => $historial,
            'edad'             => $paciente->fecha_nac ? \Carbon\Carbon::parse($paciente->fecha_nac)->age : null,
            'paramedicoCedula' => $paramedicoCedula,
        ]);
    }

    // ── Primera atención ──────────────────────────────────────────
    public function primeraAtencion(Request $request)
    {
        $cedula = Session::get('usuario_cedula');

        $pacientes = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->whereExists(function ($sub) use ($cedula) {
                $sub->select(DB::raw(1))->from('CITAS')
                    ->whereColumn('cedula_paciente', 'p.cedula')
                    ->where('cedula_medico', $cedula);
            })
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                     'pac.tipo_sangre', 'pac.alergias')
            ->orderBy('p.nombres')
            ->get();

        $paciente = null;
        if ($request->filled('cedula')) {
            $paciente = DB::table('PERSONAS as p')
                ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
                ->where('p.cedula', $request->cedula)
                ->select('p.*', 'pac.tipo_sangre', 'pac.alergias', 'pac.antecedentes')
                ->first();
        }

        return view('paramedico.primera_atencion', compact('pacientes', 'paciente'));
    }

    // ── Guardar primera atención ──────────────────────────────────
    public function guardarAtencion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula_paciente' => 'required|exists:PERSONAS,cedula',
            'motivo_consulta' => 'required|string|max:200',
            'tipo_cita'       => 'required|in:presencial,virtual',
        ]);

        if ($validator->fails()) return back()->withInput()->with('error', $validator->errors()->first());

        $cedula = Session::get('usuario_cedula');

        DB::beginTransaction();
        try {
            // Verificar si ya existe cita hoy para no duplicar
            $citaHoy = DB::table('CITAS')
                ->where('cedula_paciente', $request->cedula_paciente)
                ->where('cedula_medico', $cedula)
                ->whereDate('fecha_hora', today())
                ->first();

            if ($citaHoy) {
                // Actualizar cita existente
                DB::table('CITAS')->where('id_cita', $citaHoy->id_cita)->update([
                    'motivo_consulta' => $request->motivo_consulta,
                    'diagnostico'     => $request->diagnostico,
                    'receta'          => $request->receta,
                    'tipo_cita'       => $request->tipo_cita,
                    'fecha_hora'      => now(),
                ]);
                $idCita = $citaHoy->id_cita;

                // Actualizar o crear signos vitales
                $svExiste = DB::table('SIGNOS_VITALES')->where('id_cita', $idCita)->exists();
                if ($svExiste) {
                    DB::table('SIGNOS_VITALES')->where('id_cita', $idCita)->update([
                        'presion'             => $request->presion ?: null,
                        'frecuencia_cardiaca' => $request->frecuencia_cardiaca ?: null,
                        'temperatura'         => $request->temperatura ?: null,
                        'peso'                => $request->peso ?: null,
                        'altura'              => $request->altura ? $request->altura / 100 : null,
                    ]);
                } else {
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
                }
            } else {
                // Crear nueva cita
                $idCita = DB::table('CITAS')->insertGetId([
                    'cedula_paciente' => $request->cedula_paciente,
                    'cedula_medico'   => $cedula,
                    'fecha_hora'      => now(),
                    'tipo_cita'       => $request->tipo_cita,
                    'motivo_consulta' => $request->motivo_consulta,
                    'diagnostico'     => $request->diagnostico,
                    'receta'          => $request->receta,
                ]);

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
            }

            DB::commit();
            return redirect()->route('paramedico.pacientes.index')->with('exito', 'Atención registrada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ── Agendar cita con médico ───────────────────────────────────
    public function agendarCita(Request $request)
    {
        $especialidades = DB::table('ESPECIALIDAD')->orderBy('nombre')->get();
        $paciente = null;
        if ($request->filled('paciente')) {
            $paciente = DB::table('PERSONAS')->where('cedula', $request->paciente)->first();
        }
        return view('paramedico.agendar_cita', compact('especialidades', 'paciente'));
    }

    public function guardarCita(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula_paciente' => 'required|exists:PERSONAS,cedula',
            'cedula_medico'   => 'required|exists:PERSONAL_MEDICO,cedula',
            'fecha'           => 'required|date',
            'hora'            => 'required',
            'tipo_cita'       => 'required|in:presencial,virtual',
            'motivo'          => 'required|string|max:200',
        ]);

        if ($validator->fails()) return back()->withInput()->with('error', $validator->errors()->first());

        DB::beginTransaction();
        try {
            $slot = DB::table('AGENDA')
                ->where('cedula_medico', $request->cedula_medico)
                ->where('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->first();

            if ($slot) {
                if ($slot->estado === 'ocupado' && $slot->cedula_paciente !== $request->cedula_paciente) {
                    throw new \Exception("El horario {$request->hora} del {$request->fecha} ya está ocupado por otro paciente.");
                }
                DB::table('AGENDA')->where('id_agenda', $slot->id_agenda)->update([
                    'cedula_paciente' => $request->cedula_paciente,
                    'estado'          => 'ocupado',
                ]);
                $agendaId = $slot->id_agenda;
            } else {
                $agendaId = DB::table('AGENDA')->insertGetId([
                    'cedula_medico'   => $request->cedula_medico,
                    'cedula_paciente' => $request->cedula_paciente,
                    'fecha'           => $request->fecha,
                    'hora'            => $request->hora,
                    'estado'          => 'ocupado',
                ]);
            }

            DB::table('CITAS')->insert([
                'id_agenda'       => $agendaId,
                'cedula_paciente' => $request->cedula_paciente,
                'cedula_medico'   => $request->cedula_medico,
                'fecha_hora'      => $request->fecha . ' ' . $request->hora,
                'tipo_cita'       => $request->tipo_cita,
                'motivo_consulta' => $request->motivo,
            ]);

            DB::commit();
            return redirect()->route('paramedico.pacientes.index')->with('exito', 'Cita agendada exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ── Emergencias ───────────────────────────────────────────────
    public function emergencias()
    {
        // Ver emergencias disponibles (sin asignar) + las del paramédico activo
        $emergencias = DB::table('AGENDA as a')
            ->leftJoin('PERSONAS as p', 'a.cedula_paciente', '=', 'p.cedula')
            ->where('a.es_emergencia', 1)
            ->where('a.fecha', '>=', today())
            ->whereIn('a.estado', ['disponible', 'ocupado'])
            ->select('a.*', 'p.nombres', 'p.apellido1', 'p.telefono')
            ->orderBy('a.estado')
            ->orderByDesc('a.fecha')
            ->orderByDesc('a.hora')
            ->get();

        return view('paramedico.emergencias', compact('emergencias'));
    }

    // ── Atender emergencia (AJAX POST) ────────────────────────────
    public function atenderEmergencia(Request $request)
    {
        $cedula   = Session::get('usuario_cedula');
        $idAgenda = $request->id_agenda;

        $slot = DB::table('AGENDA')
            ->where('id_agenda', $idAgenda)
            ->where('es_emergencia', 1)
            ->where('estado', 'disponible')
            ->first();

        if (!$slot) {
            return response()->json(['success' => false, 'message' => 'Esta emergencia ya fue atendida.']);
        }

        DB::table('AGENDA')->where('id_agenda', $idAgenda)->update([
            'estado'        => 'ocupado',
            'cedula_medico' => $cedula,
        ]);

        $cedulaPaciente = $slot->cedula_paciente;
        $roomId = 'vc_emerg_' . substr(md5($cedula . $cedulaPaciente . time()), 0, 10);

        \Illuminate\Support\Facades\Cache::put("vc_{$roomId}", [
            'status'     => 'calling',
            'caller'     => $cedula,
            'callee'     => $cedulaPaciente,
            'offer'      => null,
            'answer'     => null,
            'ice_caller' => [],
            'ice_callee' => [],
            'chat'       => [],
        ], now()->addHours(2));

        \Illuminate\Support\Facades\Cache::put("vc_incoming_{$cedulaPaciente}", $roomId, now()->addMinutes(30));

        return response()->json(['success' => true, 'room' => $roomId]);
    }

    // ── Mi Perfil ─────────────────────────────────────────────────
    public function perfil()
    {
        $cedula  = Session::get('usuario_cedula');
        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();

        return view('paramedico.perfil', compact('persona'));
    }

    public function actualizarPerfil(Request $request)
    {
        $cedula = Session::get('usuario_cedula');

        $validator = Validator::make($request->all(), [
            'nombres'   => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'correo'    => 'required|email|max:100',
            'telefono'  => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) return back()->withInput()->with('error', $validator->errors()->first());

        DB::beginTransaction();
        try {
            $datos = [
                'nombres'   => trim($request->nombres),
                'apellido1' => trim($request->apellido1),
                'apellido2' => trim($request->apellido2 ?? ''),
                'correo'    => trim($request->correo),
                'telefono'  => trim($request->telefono ?? ''),
            ];

            // Cambio de contraseña
            if ($request->filled('new_password')) {
                $minPass = (int) (\App\Http\Controllers\Admin\ConfiguracionController::leerConfig()['seguridad']['pass_min_length'] ?? 8);
                if (!$request->filled('current_password')) {
                    throw new \Exception('Ingresa tu contraseña actual.');
                }
                if ($request->new_password !== $request->confirm_password) {
                    throw new \Exception('Las contraseñas nuevas no coinciden.');
                }
                if (strlen($request->new_password) < $minPass) {
                    throw new \Exception("La contraseña debe tener al menos {$minPass} caracteres.");
                }

                $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
                if (!Hash::check($request->current_password, $persona->contrasena)) {
                    throw new \Exception('Contraseña actual incorrecta.');
                }

                $datos['contrasena'] = Hash::make($request->new_password);
            }

            DB::table('PERSONAS')->where('cedula', $cedula)->update($datos);
            Session::put('usuario_nombre', trim($request->nombres . ' ' . $request->apellido1));

            DB::commit();
            return back()->with('exito', 'Perfil actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── AJAX médicos por especialidad ─────────────────────────────
    public function medicosPorEspecialidad(int $id)
    {
        $medicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->join('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->where('me.id_especialidad', $id)
            ->where('p.estado', 'activo')
            ->select('p.cedula', 'p.nombres', 'p.apellido1')
            ->orderBy('p.nombres')->get();

        return response()->json($medicos);
    }

    // ── AJAX agenda disponible ────────────────────────────────────
    public function agendaDisponible(Request $request)
    {
        $medicoId = $request->get('medico');
        $fecha    = $request->get('fecha');

        $ocupados = DB::table('AGENDA')
            ->where('cedula_medico', $medicoId)
            ->where('fecha', $fecha)
            ->where('estado', 'ocupado')
            ->pluck('hora');

        $horarios = [];
        $t   = Carbon::parse('08:00');
        $fin = Carbon::parse('17:00');
        while ($t < $fin) {
            $h = $t->format('H:i:s');
            $horarios[] = ['hora'=>$t->format('H:i'), 'hora_completa'=>$h, 'estado'=>$ocupados->contains($h) ? 'ocupado' : 'disponible'];
            $t->addMinutes(30);
        }

        return response()->json(['horarios'=>$horarios, 'hora_servidor'=>now()->format('H:i:s'), 'fecha_servidor'=>now()->format('Y-m-d')]);
    }

    // ── Formulario agregar paciente ───────────────────────────────
    public function crearPaciente()
    {
        $etnias     = DB::table('ETNIA')->orderBy('tipo_etnia')->get();
        $parroquias = DB::table('PARROQUIA as pa')
            ->join('CANTON as ca', 'pa.codigo_canton', '=', 'ca.codigo_canton')
            ->join('PROVINCIA as pr', 'ca.codigo_provincia', '=', 'pr.codigo_provincia')
            ->select('pa.codigo_parroquia', 'pa.nombre_parroquia',
                     'ca.nombre_canton', 'pr.nombre_provincia')
            ->orderBy('pr.nombre_provincia')
            ->orderBy('ca.nombre_canton')
            ->get();

        return view('paramedico.pacientes.crear_paciente', compact('etnias', 'parroquias'));
    }

    // ── Guardar nuevo paciente ────────────────────────────────────
    public function guardarPaciente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula'           => 'required|string|size:10|unique:PERSONAS,cedula',
            'nombres'          => 'required|string|max:40',
            'apellido1'        => 'required|string|max:40',
            'correo'           => 'required|email|max:100|unique:PERSONAS,correo',
            'fecha_nac'        => 'required|date',
            'codigo_parroquia' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $cedula = trim($request->cedula);

            $idEtnia = $request->id_etnia;
            if ($request->filled('nueva_etnia')) {
                $idEtnia = DB::table('ETNIA')->insertGetId([
                    'tipo_etnia' => strtoupper(trim($request->nueva_etnia)),
                ]);
            }

            DB::table('PERSONAS')->insert([
                'cedula'       => $cedula,
                'nombres'      => trim($request->nombres),
                'apellido1'    => trim($request->apellido1),
                'apellido2'    => trim($request->apellido2 ?? ''),
                'tipo_cedula'  => $request->tipo_cedula ?? 'cedula',
                'fecha_nac'    => $request->fecha_nac,
                'estado_civil' => $request->estado_civil ?? 'S',
                'correo'       => trim($request->correo),
                'contrasena'   => Hash::make($cedula),
                'telefono'     => trim($request->telefono ?? ''),
                'genero'       => $request->genero ?? 'M',
                'estado'       => 'activo',
            ]);

            DB::table('PACIENTE')->insert([
                'cedula'              => $cedula,
                'codigo_parroquia'    => $request->codigo_parroquia,
                'id_etnia'            => $idEtnia ?: null,
                'nacionalidad'        => $request->nacionalidad ?? 'Ecuatoriana',
                'barrio'              => $request->barrio,
                'direccion_1'         => $request->direccion_1,
                'tipo_sangre'         => $request->tipo_sangre ?? 'Desconocido',
                'alergias'            => $request->alergias,
                'antecedentes'        => $request->antecedentes,
                'medicacion_habitual' => $request->medicacion_habitual,
                'discapacidad'        => $request->has('discapacidad') ? 1 : 0,
                'tipo_discapacidad'   => $request->tipo_discapacidad,
                'gestante'            => $request->has('gestante') ? 1 : 0,
                'semanas_gestacion'   => $request->semanas_gestacion,
                'fumador'             => $request->has('fumador') ? 1 : 0,
                'consume_alcohol'     => $request->has('consume_alcohol') ? 1 : 0,
            ]);

            if ($request->filled('contacto_nombre')) {
                DB::table('CONTACTO_EMERGENCIA')->insert([
                    'cedula_paciente' => $cedula,
                    'nombre'          => $request->contacto_nombre,
                    'parentesco'      => $request->contacto_parentesco,
                    'telefono'        => $request->contacto_telefono,
                    'es_principal'    => 1,
                ]);
            }

            // Vincular paciente al paramédico que lo registró
            DB::table('CITAS')->insert([
                'cedula_paciente' => $cedula,
                'cedula_medico'   => Session::get('usuario_cedula'),
                'fecha_hora'      => now(),
                'tipo_cita'       => 'presencial',
                'motivo_consulta' => 'Registro inicial de paciente',
            ]);

            DB::commit();
            return redirect()
                ->route('paramedico.pacientes.index')
                ->with('exito', "Paciente registrado. Contraseña temporal: {$cedula}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}