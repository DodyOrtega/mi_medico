<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * EspecialidadController
 *
 * CRUD de especialidades médicas. Punto de entrada del módulo médicos:
 * cada especialidad agrupa a sus médicos (ver MedicoController).
 */
class EspecialidadController extends Controller
{
    // Iconos Font Awesome sugeridos por nombre de especialidad
    private const ICONOS = [
        'Cardiología'        => 'heartbeat',
        'Neurología'         => 'brain',
        'Medicina General'   => 'stethoscope',
        'Ortopedia'          => 'bone',
        'Neumología'         => 'lungs',
        'Odontología'        => 'tooth',
        'Pediatría'          => 'baby',
        'Oftalmología'       => 'eye',
        'Alergología'        => 'allergies',
        'Genética'           => 'dna',
        'Cirugía'            => 'scalpel',
        'Dermatología'       => 'hand',
        'Ginecología'        => 'venus',
        'Urología'           => 'mars',
        'Psiquiatría'        => 'brain',
        'Endocrinología'     => 'chart-line',
        'Gastroenterología'  => 'stomach',
        'Oncología'          => 'ribbon',
        'Reumatología'       => 'bone',
        'Nefrología'         => 'kidneys',
        'Infectología'       => 'virus',
        'Geriatría'          => 'user',
        'Fisioterapia'       => 'person-walking',
        'Nutrición'          => 'apple-alt',
        'Radiología'         => 'x-ray',
        'Anestesiología'     => 'syringe',
    ];

    // ── Listado de especialidades con conteo de médicos ──────────
    public function index()
    {
        $especialidades = DB::table('ESPECIALIDAD as e')
            ->leftJoin('MEDICO_ESPECIALIDAD as me', 'e.id_especialidad', '=', 'me.id_especialidad')
            ->select(
                'e.id_especialidad',
                'e.nombre',
                'e.formacion_academica',
                DB::raw('COUNT(DISTINCT me.cedula_medico) as total_medicos')
            )
            ->groupBy('e.id_especialidad', 'e.nombre', 'e.formacion_academica')
            ->orderBy('e.nombre')
            ->get()
            ->map(function ($esp) {
                $esp->icono = self::ICONOS[$esp->nombre] ?? 'stethoscope';
                return $esp;
            });

        return view('admin.medicos.especialidades', [
            'especialidades' => $especialidades,
        ]);
    }

    // ── Crear nueva especialidad ──────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_especialidad' => 'required|string|max:50',
            'formacion_academica' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Por favor, ingresa el nombre de la especialidad.');
        }

        $nombre    = trim($request->nombre_especialidad);
        $formacion = trim($request->formacion_academica ?? '');

        $existe = DB::table('ESPECIALIDAD')->where('nombre', $nombre)->exists();

        if ($existe) {
            return back()->with('error', "La especialidad '{$nombre}' ya existe.");
        }

        DB::table('ESPECIALIDAD')->insert([
            'nombre'              => $nombre,
            'formacion_academica' => $formacion,
        ]);

        return back()->with('exito', "Especialidad '{$nombre}' agregada exitosamente.");
    }

    // ── Editar especialidad existente ─────────────────────────────
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre_especialidad' => 'required|string|max:50',
            'formacion_academica' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Datos inválidos para actualizar la especialidad.');
        }

        $nombre    = trim($request->nombre_especialidad);
        $formacion = trim($request->formacion_academica ?? '');

        $duplicada = DB::table('ESPECIALIDAD')
            ->where('nombre', $nombre)
            ->where('id_especialidad', '!=', $id)
            ->exists();

        if ($duplicada) {
            return back()->with('error', "Ya existe otra especialidad con el nombre '{$nombre}'.");
        }

        DB::table('ESPECIALIDAD')
            ->where('id_especialidad', $id)
            ->update([
                'nombre'              => $nombre,
                'formacion_academica' => $formacion,
            ]);

        return back()->with('exito', 'Especialidad actualizada exitosamente.');
    }

    // ── Eliminar especialidad (solo si no tiene médicos) ──────────
    public function destroy(int $id)
    {
        $totalMedicos = DB::table('MEDICO_ESPECIALIDAD')
            ->where('id_especialidad', $id)
            ->count();

        if ($totalMedicos > 0) {
            return back()->with('error', "No se puede eliminar: tiene {$totalMedicos} médico(s) asociado(s).");
        }

        DB::table('ESPECIALIDAD')->where('id_especialidad', $id)->delete();

        return back()->with('exito', 'Especialidad eliminada exitosamente.');
    }
}