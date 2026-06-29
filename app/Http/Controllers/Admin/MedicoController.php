<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * MedicoController
 *
 * Gestión completa de médicos: listado (global o por especialidad),
 * ver detalle, crear, editar y eliminar.
 */
class MedicoController extends Controller
{
    // ── Listado de médicos (todos o filtrados por especialidad) ──
    public function index(Request $request)
    {
        $especialidadId = (int) $request->get('especialidad_id', 0);
        $modoEspecialidad = $especialidadId > 0;
        $busqueda = trim($request->get('busqueda', ''));

        $nombreEspecialidad = 'TODOS LOS MÉDICOS';
        if ($modoEspecialidad) {
            $esp = DB::table('ESPECIALIDAD')->where('id_especialidad', $especialidadId)->first();
            if ($esp) {
                $nombreEspecialidad = $esp->nombre;
            }
        }

        $query = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'pm.cedula', '=', 'me.cedula_medico')
            ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('pm.tipo', 'Medico')
            ->select(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.correo', 'p.telefono', 'p.estado',
                'pm.anios_experiencia',
                DB::raw("GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') as especialidades")
            );

        if ($modoEspecialidad) {
            $query->where('me.id_especialidad', $especialidadId);
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('p.nombres', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido1', 'like', "%{$busqueda}%")
                  ->orWhere('p.apellido2', 'like', "%{$busqueda}%")
                  ->orWhere('p.cedula', 'like', "%{$busqueda}%");
            });
        }

        $medicos = $query->groupBy(
                'p.cedula', 'p.nombres', 'p.apellido1', 'p.apellido2',
                'p.correo', 'p.telefono', 'p.estado', 'pm.anios_experiencia'
            )
            ->orderBy('p.nombres')
            ->orderBy('p.apellido1')
            ->get();

        return view('admin.medicos.index', [
            'medicos'             => $medicos,
            'especialidadId'      => $especialidadId,
            'modoEspecialidad'    => $modoEspecialidad,
            'nombreEspecialidad'  => $nombreEspecialidad,
            'busqueda'            => $busqueda,
        ]);
    }

    // ── Ver detalle completo de un médico ─────────────────────────
    public function show(string $cedula)
    {
        $medico = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('p.cedula', $cedula)
            ->where('pm.tipo', 'Medico')
            ->select('p.*', 'pm.anios_experiencia')
            ->first();

        if (!$medico) {
            abort(404, 'Médico no encontrado.');
        }

        $especialidades = DB::table('MEDICO_ESPECIALIDAD as me')
            ->join('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
            ->where('me.cedula_medico', $cedula)
            ->select('e.nombre', 'me.fecha_especialidad', 'me.registro_senescyt')
            ->get();

        $titulos = DB::table('TITULOS_ACADEMICOS')
            ->where('cedula_medico', $cedula)
            ->orderBy('fecha_registro', 'desc')
            ->get();

        $totalCitas = DB::table('CITAS')->where('cedula_medico', $cedula)->count();

        return view('admin.medicos.detalle', [
            'medico'         => $medico,
            'especialidades' => $especialidades,
            'titulos'        => $titulos,
            'totalCitas'     => $totalCitas,
        ]);
    }

    // ── Formulario de creación ────────────────────────────────────
    public function create(Request $request)
    {
        $especialidades = DB::table('ESPECIALIDAD')->orderBy('nombre')->get();

        return view('admin.medicos.crear', [
            'especialidades'    => $especialidades,
            'especialidadIdUrl' => (int) $request->get('especialidad_id', 0),
        ]);
    }

    // ── Guardar nuevo médico ───────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula'           => 'required|string|size:10|unique:PERSONAS,cedula',
            'nombres'          => 'required|string|max:40',
            'apellido1'        => 'required|string|max:40',
            'apellido2'        => 'nullable|string|max:40',
            'correo'           => 'required|email|max:100|unique:PERSONAS,correo',
            'telefono'         => 'nullable|string|max:15',
            'fecha_nac'        => 'nullable|date',
            'id_especialidad'  => 'required|integer|exists:ESPECIALIDAD,id_especialidad',
            'anios_experiencia'=> 'nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $cedula = trim($request->cedula);

            // 1. PERSONAS — contraseña temporal = la cédula
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

            // 2. PERSONAL_MEDICO
            DB::table('PERSONAL_MEDICO')->insert([
                'cedula'            => $cedula,
                'anios_experiencia' => $request->anios_experiencia ?? 0,
                'tipo'              => 'Medico',
            ]);

            // 3. MEDICO_ESPECIALIDAD
            DB::table('MEDICO_ESPECIALIDAD')->insert([
                'id_especialidad'   => $request->id_especialidad,
                'cedula_medico'     => $cedula,
                'fecha_especialidad'=> now()->format('Y-m-d'),
                'registro_senescyt' => trim($request->registro_senecyt ?? ''),
            ]);

            // 4. TITULOS_ACADEMICOS (opcional, uno por línea)
            if (!empty($request->titulos_academicos)) {
                $titulos = array_filter(array_map('trim', explode("\n", $request->titulos_academicos)));
                foreach ($titulos as $titulo) {
                    DB::table('TITULOS_ACADEMICOS')->insert([
                        'cedula_medico'  => $cedula,
                        'nombre_titulo'  => $titulo,
                        'fecha_registro' => now()->format('Y-m-d'),
                    ]);
                }
            }

            DB::commit();

            Auditoria::registrar('crear_medico', "Médico creado: {$cedula} — {$request->nombres} {$request->apellido1}");

            return redirect()
                ->route('admin.medicos.index')
                ->with('exito', "Médico agregado exitosamente. Contraseña temporal: {$cedula}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al agregar médico: ' . $e->getMessage());
        }
    }

    // ── Formulario de edición ─────────────────────────────────────
    public function edit(string $cedula)
    {
        $medico = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('p.cedula', $cedula)
            ->where('pm.tipo', 'Medico')
            ->select('p.*', 'pm.anios_experiencia')
            ->first();

        if (!$medico) {
            abort(404, 'Médico no encontrado.');
        }

        $especialidades = DB::table('ESPECIALIDAD')->orderBy('nombre')->get();

        $especialidadesAsignadas = DB::table('MEDICO_ESPECIALIDAD')
            ->where('cedula_medico', $cedula)
            ->pluck('id_especialidad')
            ->toArray();

        return view('admin.medicos.editar', [
            'medico'                   => $medico,
            'especialidades'           => $especialidades,
            'especialidadesAsignadas'  => $especialidadesAsignadas,
        ]);
    }

    // ── Actualizar médico ───────────────────────────────────────────
    public function update(Request $request, string $cedula)
    {
        $validator = Validator::make($request->all(), [
            'nombres'    => 'required|string|max:40',
            'apellido1'  => 'required|string|max:40',
            'apellido2'  => 'nullable|string|max:40',
            'correo'     => 'required|email|max:100',
            'telefono'   => 'nullable|string|max:15',
            'fecha_nac'  => 'nullable|date',
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

            // Cambiar contraseña solo si se proporcionó una nueva
            if (!empty($request->contrasena)) {
                $datosPersona['contrasena'] = Hash::make($request->contrasena);
            }

            // Al desactivar, cancelar slots de agenda futuros
            if (($request->estado ?? 'activo') === 'inactivo') {
                $estadoActual = DB::table('PERSONAS')->where('cedula', $cedula)->value('estado');
                if ($estadoActual !== 'inactivo') {
                    DB::table('AGENDA')
                        ->where('cedula_medico', $cedula)
                        ->where('fecha', '>=', today()->toDateString())
                        ->whereIn('estado', ['disponible', 'ocupado'])
                        ->update(['estado' => 'cancelado']);
                }
            }

            DB::table('PERSONAS')->where('cedula', $cedula)->update($datosPersona);

            DB::table('PERSONAL_MEDICO')->where('cedula', $cedula)->update([
                'anios_experiencia' => $request->anios_experiencia ?? 0,
            ]);

            // Sincronizar especialidades seleccionadas
            if ($request->has('especialidades') && is_array($request->especialidades)) {
                DB::table('MEDICO_ESPECIALIDAD')->where('cedula_medico', $cedula)->delete();

                foreach ($request->especialidades as $idEspecialidad) {
                    DB::table('MEDICO_ESPECIALIDAD')->insert([
                        'id_especialidad'    => $idEspecialidad,
                        'cedula_medico'      => $cedula,
                        'fecha_especialidad' => now()->format('Y-m-d'),
                    ]);
                }
            }

            DB::commit();

            Auditoria::registrar('editar_medico', "Médico editado: {$cedula}");

            return redirect()
                ->route('admin.medicos.index', $request->especialidad_id ? ['especialidad_id' => $request->especialidad_id] : [])
                ->with('exito', 'Médico actualizado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar médico: ' . $e->getMessage());
        }
    }

    // ── Eliminar médico (cascada manual en orden correcto) ─────────
    public function destroy(Request $request, string $cedula)
    {
        DB::beginTransaction();
        try {
            $esMedico = DB::table('PERSONAL_MEDICO')
                ->where('cedula', $cedula)
                ->where('tipo', 'Medico')
                ->exists();

            if (!$esMedico) {
                throw new \Exception('El usuario no es un médico válido.');
            }

            // Bloquear si tiene citas registradas
            $totalCitas = DB::table('CITAS')->where('cedula_medico', $cedula)->count();
            if ($totalCitas > 0) {
                return back()->with('error',
                    "No se puede eliminar: el médico tiene {$totalCitas} cita(s) registrada(s). Desactívelo en su lugar."
                );
            }

            // Limpiar agenda antes de eliminar
            DB::table('AGENDA')->where('cedula_medico', $cedula)->delete();

            DB::table('MEDICO_ESPECIALIDAD')->where('cedula_medico', $cedula)->delete();
            DB::table('TITULOS_ACADEMICOS')->where('cedula_medico', $cedula)->delete();
            DB::table('PERSONAL_MEDICO')->where('cedula', $cedula)->delete();
            DB::table('PERSONAS')->where('cedula', $cedula)->delete();

            DB::commit();

            $especialidadId = (int) $request->get('especialidad_id', 0);

            Auditoria::registrar('eliminar_medico', "Médico eliminado: {$cedula}");

            return redirect()
                ->route('admin.medicos.index', $especialidadId ? ['especialidad_id' => $especialidadId] : [])
                ->with('exito', 'Médico eliminado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar médico: ' . $e->getMessage());
        }
    }

    // ── Activar / Desactivar médico ───────────────────────────────
    public function toggleEstado(string $cedula)
    {
        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();

        if (!$persona) {
            abort(404, 'Médico no encontrado.');
        }

        $nuevoEstado = $persona->estado === 'activo' ? 'inactivo' : 'activo';

        DB::table('PERSONAS')->where('cedula', $cedula)->update(['estado' => $nuevoEstado]);

        $accion = ($nuevoEstado === 'activo' ? 'activar' : 'inactivar') . '_medico';
        Auditoria::registrar($accion, "Médico {$cedula} marcado como {$nuevoEstado}");

        return back()->with('exito', "Médico marcado como {$nuevoEstado}.");
    }
}