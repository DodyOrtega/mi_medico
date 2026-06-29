<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * AdminController
 *
 * Lógica del panel de administración. Cero HTML aquí — solo datos
 * que se pasan a las vistas Blade.
 */
class AdminController extends Controller
{
    // ── Dashboard principal — tarjetas de acceso a cada módulo ──
    public function dashboard()
    {
        $stats = $this->obtenerEstadisticas();

        $tarjetas = [
            [
                'icono'       => 'user-md',
                'titulo'      => 'Gestión de Paramédicos',
                'descripcion' => 'Administra el personal paramédico: registrar, editar, eliminar y gestionar permisos de acceso al sistema.',
                'boton'       => 'Gestionar',
                'boton_icono' => 'cogs',
                'ruta'        => route('admin.paramedicos.index'),
                'contador'    => $stats['paramedicos'],
            ],
            [
                'icono'       => 'stethoscope',
                'titulo'      => 'Gestión de Médicos',
                'descripcion' => 'Controla el registro de médicos, especialidades, horarios de consulta y asignación de pacientes.',
                'boton'       => 'Gestionar',
                'boton_icono' => 'cogs',
                'ruta'        => route('admin.medicos.index'),
                'contador'    => $stats['medicos'],
            ],
            [
                'icono'       => 'users',
                'titulo'      => 'Gestión de Pacientes',
                'descripcion' => 'Supervisa el registro completo de pacientes, historiales médicos y estadísticas del sistema.',
                'boton'       => 'Gestionar',
                'boton_icono' => 'cogs',
                'ruta'        => route('admin.pacientes.index'),
                'contador'    => $stats['pacientes'],
            ],
            [
                'icono'       => 'chart-bar',
                'titulo'      => 'Reportes y Estadísticas',
                'descripcion' => 'Genera reportes detallados, estadísticas de consultas y métricas de desempeño del sistema.',
                'boton'       => 'Ver Reportes',
                'boton_icono' => 'chart-line',
                'ruta'        => route('admin.reportes.index'),
                'contador'    => null,
            ],
            [
                'icono'       => 'folder-open',
                'titulo'      => 'Documentos de Pacientes',
                'descripcion' => 'Gestiona los archivos subidos por pacientes, realiza backups a Google Drive y restaura documentos.',
                'boton'       => 'Ver Documentos',
                'boton_icono' => 'file-medical',
                'ruta'        => route('admin.examenes.index'),
                'contador'    => null,
            ],
            [
                'icono'       => 'sliders-h',
                'titulo'      => 'Configuración del Sistema',
                'descripcion' => 'Configura parámetros del sistema, permisos de usuario, horarios y políticas de seguridad.',
                'boton'       => 'Configurar',
                'boton_icono' => 'wrench',
                'ruta'        => route('admin.configuracion.index'),
                'contador'    => null,
            ],
        ];

        return view('admin.dashboard', [
            'tarjetas'    => $tarjetas,
            'stats'       => $stats,
            'showWelcome' => Session::pull('login_success', false),
        ]);
    }

    // ── Perfil del administrador ──────────────────────────────────
    public function perfil()
    {
        $cedula  = Session::get('usuario_cedula');
        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        return view('admin.perfil', compact('persona'));
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
                $minPass = (int) (ConfiguracionController::leerConfig()['seguridad']['pass_min_length'] ?? 8);
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

    // ── Conteos reales desde la base de datos ────────────────────
    private function obtenerEstadisticas(): array
    {
        return [
            'medicos'     => DB::table('PERSONAL_MEDICO')->where('tipo', 'Medico')->count(),
            'paramedicos' => DB::table('PERSONAL_MEDICO')->where('tipo', 'Paramedico')->count(),
            'pacientes'   => DB::table('PACIENTE')->count(),
            'citas_hoy'   => DB::table('CITAS')->whereDate('fecha_hora', now())->count(),
        ];
    }
}