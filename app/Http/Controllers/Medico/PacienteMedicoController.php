<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * PacienteMedicoController
 *
 * Módulo médico — gestión de sus propios pacientes:
 * listado (solo pacientes que han tenido cita con este médico),
 * ver detalle completo con historial, y agregar nuevo paciente.
 */
class PacienteMedicoController extends Controller
{
    // ── Listado de mis pacientes ──────────────────────────────────
    public function index(Request $request)
    {
        $cedula  = Session::get('usuario_cedula');
        $busqueda = trim($request->get('busqueda', ''));

        $query = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->where(function ($q) use ($cedula) {
                $q->whereExists(function ($sub) use ($cedula) {
                    $sub->select(DB::raw(1))
                        ->from('CITAS')
                        ->whereColumn('cedula_paciente', 'p.cedula')
                        ->where('cedula_medico', $cedula);
                })->orWhereExists(function ($sub) use ($cedula) {
                    $sub->select(DB::raw(1))
                        ->from('AGENDA')
                        ->whereColumn('cedula_paciente', 'p.cedula')
                        ->where('cedula_medico', $cedula)
                        ->whereIn('estado', ['ocupado', 'asistio']);
                });
            })
            ->select(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.correo', 'p.telefono', 'p.fecha_nac', 'p.genero', 'p.estado',
                'pac.tipo_sangre',
                DB::raw('(SELECT MAX(fecha_hora) FROM CITAS WHERE cedula_paciente = p.cedula AND cedula_medico = "' . $cedula . '") as ultima_cita'),
                DB::raw('(SELECT COUNT(*) FROM CITAS WHERE cedula_paciente = p.cedula AND cedula_medico = "' . $cedula . '" AND fecha_hora > NOW()) as citas_pendientes')
            );

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1', 'like', "%{$busqueda}%")
                  ->orWhere('p.cedula', 'like', "%{$busqueda}%");
            });
        }

        $pacientes = $query->orderBy('p.nombres')->get();

        return view('medico.pacientes.index', [
            'pacientes' => $pacientes,
            'busqueda'  => $busqueda,
        ]);
    }

    // ── Ver detalle de un paciente ────────────────────────────────
    public function show(string $cedula)
    {
        $medicoCedula = Session::get('usuario_cedula');

        $paciente = DB::table('PERSONAS as p')
            ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
            ->leftJoin('PARROQUIA as par', 'pac.codigo_parroquia', '=', 'par.codigo_parroquia')
            ->leftJoin('CANTON as can', 'par.codigo_canton', '=', 'can.codigo_canton')
            ->leftJoin('PROVINCIA as pro', 'can.codigo_provincia', '=', 'pro.codigo_provincia')
            ->leftJoin('ETNIA as e', 'pac.id_etnia', '=', 'e.id_etnia')
            ->where('p.cedula', $cedula)
            ->select('p.*', 'pac.*', 'par.nombre_parroquia',
                     'can.nombre_canton', 'pro.nombre_provincia', 'e.tipo_etnia')
            ->first();

        if (!$paciente) abort(404, 'Paciente no encontrado.');

        $contactos = DB::table('CONTACTO_EMERGENCIA')
            ->where('cedula_paciente', $cedula)
            ->orderByDesc('es_principal')
            ->get();

        // Historial completo: este médico + otros médicos + paramédicos
        $historial = DB::table('CITAS as c')
            ->join('PERSONAS as per', 'c.cedula_medico', '=', 'per.cedula')
            ->leftJoin('PERSONAL_MEDICO as pm', 'c.cedula_medico', '=', 'pm.cedula')
            ->leftJoin('SIGNOS_VITALES as sv', 'c.id_cita', '=', 'sv.id_cita')
            ->where('c.cedula_paciente', $cedula)
            ->select(
                'c.id_cita', 'c.fecha_hora', 'c.tipo_cita',
                'c.motivo_consulta', 'c.diagnostico', 'c.receta',
                'c.cedula_medico',
                'per.nombres as prof_nombres', 'per.apellido1 as prof_apellido1',
                'pm.tipo as tipo_profesional',
                'sv.presion', 'sv.frecuencia_cardiaca', 'sv.temperatura',
                'sv.peso', 'sv.altura'
            )
            ->orderByDesc('c.fecha_hora')
            ->get();

        $edad = $paciente->fecha_nac
            ? \Carbon\Carbon::parse($paciente->fecha_nac)->age
            : null;

        return view('medico.pacientes.detalle', [
            'paciente'     => $paciente,
            'contactos'    => $contactos,
            'historial'    => $historial,
            'edad'         => $edad,
            'medicoCedula' => $medicoCedula,
        ]);
    }

    // ── Formulario agregar paciente ───────────────────────────────
    public function create()
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

        return view('medico.pacientes.crear', [
            'etnias'     => $etnias,
            'parroquias' => $parroquias,
        ]);
    }

    // ── Guardar nuevo paciente ────────────────────────────────────
    public function store(Request $request)
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

            // Manejar etnia nueva si se ingresó
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

            DB::table('CITAS')->insert([
                'cedula_paciente' => $cedula,
                'cedula_medico'   => Session::get('usuario_cedula'),
                'fecha_hora'      => now(),
                'tipo_cita'       => 'presencial',
                'motivo_consulta' => 'Registro inicial de paciente',
            ]);

            DB::commit();

            return redirect()
                ->route('medico.pacientes.index')
                ->with('exito', "Paciente registrado. Contraseña temporal: {$cedula}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar paciente: ' . $e->getMessage());
        }
    }
}