<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * ParamedicoController
 *
 * Gestión completa de paramédicos: listado con búsqueda, crear,
 * editar y eliminar. A diferencia de Médico, no maneja especialidades
 * ni títulos académicos.
 */
class ParamedicoController extends Controller
{
    // ── Listado de paramédicos con búsqueda ───────────────────────
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $query = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Paramedico')
            ->select(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.correo', 'p.telefono', 'p.estado', 'pm.anios_experiencia'
            );

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido2', 'like', "%{$busqueda}%")
                  ->orWhere('p.cedula', 'like', "%{$busqueda}%");
            });
        }

        $paramedicos = $query->orderBy('p.nombres')->orderBy('p.apellido1')->get();

        return view('admin.paramedicos.index', [
            'paramedicos' => $paramedicos,
            'busqueda'    => $busqueda,
        ]);
    }

    // ── Formulario de creación ────────────────────────────────────
    public function create()
    {
        return view('admin.paramedicos.crear');
    }

    // ── Guardar nuevo paramédico ───────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula'            => 'required|string|size:10|unique:PERSONAS,cedula',
            'nombres'           => 'required|string|max:40',
            'apellido1'         => 'required|string|max:40',
            'apellido2'         => 'nullable|string|max:40',
            'correo'            => 'required|email|max:100|unique:PERSONAS,correo',
            'telefono'          => 'nullable|string|max:15',
            'fecha_nac'         => 'nullable|date',
            'anios_experiencia' => 'nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $cedula = trim($request->cedula);

            DB::table('PERSONAS')->insert([
                'cedula'       => $cedula,
                'nombres'      => trim($request->nombres),
                'apellido1'    => trim($request->apellido1),
                'apellido2'    => trim($request->apellido2 ?? ''),
                'tipo_cedula'  => $request->tipo_cedula ?? 'cedula',
                'fecha_nac'    => $request->fecha_nac,
                'estado_civil' => $request->estado_civil ?? 'S',
                'correo'       => trim($request->correo),
                'contrasena'   => Hash::make($cedula), // contraseña temporal = cédula
                'telefono'     => trim($request->telefono ?? ''),
                'genero'       => $request->genero ?? 'M',
                'estado'       => 'activo',
            ]);

            DB::table('PERSONAL_MEDICO')->insert([
                'cedula'            => $cedula,
                'anios_experiencia' => $request->anios_experiencia ?? 0,
                'tipo'              => 'Paramedico',
            ]);

            DB::commit();

            return redirect()
                ->route('admin.paramedicos.index')
                ->with('exito', "Paramédico agregado exitosamente. Contraseña temporal: {$cedula}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al agregar paramédico: ' . $e->getMessage());
        }
    }

    // ── Formulario de edición ─────────────────────────────────────
    public function edit(string $cedula)
    {
        $paramedico = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('p.cedula', $cedula)
            ->where('pm.tipo', 'Paramedico')
            ->select('p.*', 'pm.anios_experiencia')
            ->first();

        if (!$paramedico) {
            abort(404, 'Paramédico no encontrado.');
        }

        return view('admin.paramedicos.editar', [
            'paramedico' => $paramedico,
        ]);
    }

    // ── Actualizar paramédico ───────────────────────────────────────
    public function update(Request $request, string $cedula)
    {
        $validator = Validator::make($request->all(), [
            'nombres'   => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'apellido2' => 'nullable|string|max:40',
            'correo'    => 'required|email|max:100',
            'telefono'  => 'nullable|string|max:15',
            'fecha_nac' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $datosPersona = [
                'nombres'      => trim($request->nombres),
                'apellido1'    => trim($request->apellido1),
                'apellido2'    => trim($request->apellido2 ?? ''),
                'tipo_cedula'  => $request->tipo_cedula ?? 'cedula',
                'fecha_nac'    => $request->fecha_nac,
                'estado_civil' => $request->estado_civil ?? 'S',
                'correo'       => trim($request->correo),
                'telefono'     => trim($request->telefono ?? ''),
                'genero'       => $request->genero ?? 'M',
                'estado'       => $request->estado ?? 'activo',
            ];

            if (!empty($request->contrasena)) {
                $datosPersona['contrasena'] = Hash::make($request->contrasena);
            }

            DB::table('PERSONAS')->where('cedula', $cedula)->update($datosPersona);

            DB::table('PERSONAL_MEDICO')->where('cedula', $cedula)->update([
                'anios_experiencia' => $request->anios_experiencia ?? 0,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.paramedicos.index')
                ->with('exito', 'Paramédico actualizado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar paramédico: ' . $e->getMessage());
        }
    }

    // ── Eliminar paramédico ──────────────────────────────────────
    public function destroy(string $cedula)
    {
        DB::beginTransaction();
        try {
            $esParamedico = DB::table('PERSONAL_MEDICO')
                ->where('cedula', $cedula)
                ->where('tipo', 'Paramedico')
                ->exists();

            if (!$esParamedico) {
                throw new \Exception('El usuario no es un paramédico válido.');
            }

            DB::table('PERSONAL_MEDICO')->where('cedula', $cedula)->delete();
            DB::table('PERSONAS')->where('cedula', $cedula)->delete();

            DB::commit();

            return redirect()
                ->route('admin.paramedicos.index')
                ->with('exito', 'Paramédico eliminado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar paramédico: ' . $e->getMessage());
        }
    }
}