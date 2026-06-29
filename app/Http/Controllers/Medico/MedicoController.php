<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MedicoController extends Controller
{
    public function dashboard()
    {
        $cedula = Session::get('usuario_cedula');

        $stats = [
            'citas_hoy'       => DB::table('CITAS')
                                   ->where('cedula_medico', $cedula)
                                   ->whereDate('fecha_hora', today())
                                   ->count(),
            'citas_mes'       => DB::table('CITAS')
                                   ->where('cedula_medico', $cedula)
                                   ->whereMonth('fecha_hora', now()->month)
                                   ->count(),
            'total_pacientes' => DB::table('CITAS')
                                   ->where('cedula_medico', $cedula)
                                   ->distinct('cedula_paciente')
                                   ->count('cedula_paciente'),
            'pendientes'      => DB::table('CITAS')
                                   ->where('cedula_medico', $cedula)
                                   ->where('fecha_hora', '>', now())
                                   ->count(),
        ];

        $citasHoy = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->whereDate('c.fecha_hora', today())
            ->where('c.fecha_hora', '>=', now())
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta',
                'p.nombres', 'p.apellido1', 'p.cedula as cedula_paciente'
            )
            ->orderBy('c.fecha_hora')
            ->get();

        $todosModulos = [
            ['clave' => 'pacientes', 'icono' => 'users',         'titulo' => 'Mis Pacientes',    'descripcion' => 'Gestiona tus pacientes e historial.',        'ruta' => route('medico.pacientes.index'), 'color' => 'blue'],
            ['clave' => 'agenda',    'icono' => 'calendar-alt',  'titulo' => 'Mi Agenda',        'descripcion' => 'Visualiza tu agenda y disponibilidad.',       'ruta' => route('medico.agenda.index'),    'color' => 'green'],
            ['clave' => 'agenda',    'icono' => 'calendar-check','titulo' => 'Gestionar Agenda', 'descripcion' => 'Crea y administra tus slots de atención.',    'ruta' => route('medico.agenda.gestionar'),'color' => 'teal'],
            ['clave' => 'historial', 'icono' => 'file-medical',  'titulo' => 'Historial Clínico','descripcion' => 'Consulta el historial de tus pacientes.',     'ruta' => route('medico.historial.index'), 'color' => 'purple'],
            ['clave' => 'citas',     'icono' => 'stethoscope',   'titulo' => 'Mis Citas',        'descripcion' => 'Gestiona y registra tus consultas médicas.',  'ruta' => route('medico.citas.index'),     'color' => 'orange'],
            ['clave' => 'video',     'icono' => 'video',         'titulo' => 'Videollamadas',    'descripcion' => 'Accede a tus consultas virtuales en línea.',  'ruta' => route('medico.video.index'),     'color' => 'red'],
        ];
        $modulos = array_filter($todosModulos,
            fn($m) => \App\Services\Permisos::tienePermiso($cedula, $m['clave']));

        return view('medico.dashboard', [
            'stats'       => $stats,
            'citasHoy'    => $citasHoy,
            'modulos'     => $modulos,
            'showWelcome' => Session::pull('login_success', false),
        ]);
    }

    // ── Dashboard AJAX ───────────────────────────────────────────
    public function dashboardData()
    {
        $cedula = Session::get('usuario_cedula');

        $stats = [
            'citas_hoy'       => DB::table('CITAS')->where('cedula_medico', $cedula)->whereDate('fecha_hora', today())->count(),
            'citas_mes'       => DB::table('CITAS')->where('cedula_medico', $cedula)->whereMonth('fecha_hora', now()->month)->count(),
            'total_pacientes' => DB::table('CITAS')->where('cedula_medico', $cedula)->distinct('cedula_paciente')->count('cedula_paciente'),
            'pendientes'      => DB::table('CITAS')->where('cedula_medico', $cedula)->where('fecha_hora', '>', now())->count(),
        ];

        $citasHoy = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'c.cedula_paciente', '=', 'p.cedula')
            ->where('c.cedula_medico', $cedula)
            ->whereDate('c.fecha_hora', today())
            ->where('c.fecha_hora', '>=', now())
            ->select('c.id_cita', 'c.fecha_hora', 'c.tipo_cita', 'c.motivo_consulta', 'p.nombres', 'p.apellido1')
            ->orderBy('c.fecha_hora')
            ->get();

        return response()->json(compact('stats', 'citasHoy'));
    }

    // ── Perfil del médico ─────────────────────────────────────────
    public function perfil()
    {
        $cedula  = Session::get('usuario_cedula');
        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        return view('medico.perfil', compact('persona'));
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

            if ($request->filled('new_password')) {
                $minPass = (int) (\App\Http\Controllers\Admin\ConfiguracionController::leerConfig()['seguridad']['pass_min_length'] ?? 8);
                if (!$request->filled('current_password')) throw new \Exception('Ingresa tu contraseña actual.');
                if ($request->new_password !== $request->confirm_password) throw new \Exception('Las contraseñas nuevas no coinciden.');
                if (strlen($request->new_password) < $minPass) throw new \Exception("La contraseña debe tener al menos {$minPass} caracteres.");

                $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
                if (!Hash::check($request->current_password, $persona->contrasena)) throw new \Exception('Contraseña actual incorrecta.');

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
}