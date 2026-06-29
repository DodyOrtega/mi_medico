<?php

namespace App\Http\Controllers\Paramedico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class PacienteController extends Controller
{
    // ── Listado ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $cedula = Session::get('usuario_cedula');
        $buscar = $request->input('buscar', '');

        $pacientes = DB::select("
            SELECT DISTINCT
                p.cedula, p.nombres, p.apellido1, p.apellido2,
                p.correo, p.telefono, p.estado,
                MAX(c.fecha_hora) AS ultima_cita
            FROM PERSONAS p
            JOIN PACIENTE pac ON pac.cedula = p.cedula
            JOIN CITAS c      ON c.cedula_paciente = p.cedula
            WHERE c.cedula_medico = ?
              AND (
                p.nombres   LIKE ? OR
                p.apellido1 LIKE ? OR
                p.cedula    LIKE ? OR
                p.correo    LIKE ?
              )
            GROUP BY p.cedula, p.nombres, p.apellido1, p.apellido2,
                     p.correo, p.telefono, p.estado
            ORDER BY ultima_cita DESC
        ", [$cedula, "%$buscar%", "%$buscar%", "%$buscar%", "%$buscar%"]);

        return view('paramedico.pacientes.index', compact('pacientes', 'buscar'));
    }

    // ── Ver detalle ───────────────────────────────────────────────
    public function show($cedula)
    {
        $paciente = DB::selectOne("
            SELECT p.*, pac.tipo_sangre, pac.alergias, pac.antecedentes,
                   pac.medicacion_habitual, pac.discapacidad, pac.tipo_discapacidad,
                   pac.gestante, pac.fumador, pac.consume_alcohol,
                   par.nombre_parroquia, c.nombre_canton, pr.nombre_provincia
            FROM PERSONAS p
            JOIN PACIENTE pac    ON pac.cedula = p.cedula
            LEFT JOIN PARROQUIA par ON par.codigo_parroquia = pac.codigo_parroquia
            LEFT JOIN CANTON c      ON c.codigo_canton = par.codigo_canton
            LEFT JOIN PROVINCIA pr  ON pr.codigo_provincia = c.codigo_provincia
            WHERE p.cedula = ?
        ", [$cedula]);

        abort_if(!$paciente, 404);

        $contactos = DB::select("
            SELECT * FROM CONTACTO_EMERGENCIA
            WHERE cedula_paciente = ? ORDER BY es_principal DESC
        ", [$cedula]);

        $historial = DB::select("
            SELECT c.id_cita, c.fecha_hora, c.tipo_cita, c.motivo_consulta,
                   c.diagnostico, c.receta,
                   sv.presion, sv.frecuencia_cardiaca, sv.temperatura, sv.peso, sv.altura,
                   pm.nombres AS med_nombres, pm.apellido1 AS med_apellido
            FROM CITAS c
            JOIN PERSONAS pm ON pm.cedula = c.cedula_medico
            LEFT JOIN SIGNOS_VITALES sv ON sv.id_cita = c.id_cita
            WHERE c.cedula_paciente = ?
            ORDER BY c.fecha_hora DESC
            LIMIT 20
        ", [$cedula]);

        return view('paramedico.pacientes.show',
            compact('paciente', 'contactos', 'historial'));
    }

    // ── Formulario agregar ────────────────────────────────────────
    public function create()
    {
        $provincias = DB::select("SELECT * FROM PROVINCIA ORDER BY nombre_provincia");
        $etnias     = DB::select("SELECT * FROM ETNIA ORDER BY tipo_etnia");
        return view('paramedico.pacientes.create', compact('provincias', 'etnias'));
    }

    // ── Guardar nuevo paciente ────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'cedula'            => 'required|size:10',
            'nombres'           => 'required|string|max:40',
            'apellido1'         => 'required|string|max:40',
            'apellido2'         => 'nullable|string|max:40',
            'correo'            => 'required|email|max:100',
            'telefono'          => 'nullable|string|max:15',
            'fecha_nac'         => 'nullable|date',
            'genero'            => 'nullable|in:M,F,Otro',
            'estado_civil'      => 'nullable|in:S,C,V,D',
            'codigo_parroquia'  => 'required|integer',
            'tipo_sangre'       => 'nullable|string',
        ]);

        // Verificar que no exista
        $existe = DB::selectOne("SELECT cedula FROM PERSONAS WHERE cedula = ?", [$request->cedula]);
        if ($existe) {
            return back()->withInput()->with('error', 'Ya existe una persona con esa cédula.');
        }

        $contrasena = Hash::make($request->cedula); // contraseña inicial = cédula

        DB::statement("
            INSERT INTO PERSONAS
                (cedula, nombres, apellido1, apellido2, tipo_cedula,
                 fecha_nac, estado_civil, correo, contrasena, telefono, genero, estado)
            VALUES (?, ?, ?, ?, 'cedula', ?, ?, ?, ?, ?, ?, 'activo')
        ", [
            $request->cedula, $request->nombres, $request->apellido1,
            $request->apellido2, $request->fecha_nac, $request->estado_civil,
            $request->correo, $contrasena, $request->telefono, $request->genero,
        ]);

        DB::statement("
            INSERT INTO PACIENTE
                (cedula, codigo_parroquia, id_etnia, tipo_sangre,
                 alergias, antecedentes, medicacion_habitual,
                 discapacidad, gestante, fumador, consume_alcohol)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $request->cedula, $request->codigo_parroquia,
            $request->id_etnia ?: null,
            $request->tipo_sangre ?: 'Desconocido',
            $request->alergias, $request->antecedentes,
            $request->medicacion_habitual,
            $request->has('discapacidad') ? 1 : 0,
            $request->has('gestante')     ? 1 : 0,
            $request->has('fumador')      ? 1 : 0,
            $request->has('consume_alcohol') ? 1 : 0,
        ]);

        return redirect()->route('paramedico.pacientes.show', $request->cedula)
            ->with('success', 'Paciente registrado correctamente. Contraseña inicial: ' . $request->cedula);
    }

    // ── Formulario editar ─────────────────────────────────────────
    public function edit($cedula)
    {
        $paciente = DB::selectOne("
            SELECT p.*, pac.tipo_sangre, pac.alergias, pac.antecedentes,
                   pac.medicacion_habitual, pac.discapacidad, pac.gestante,
                   pac.fumador, pac.consume_alcohol,
                   pac.codigo_parroquia, pac.id_etnia
            FROM PERSONAS p
            JOIN PACIENTE pac ON pac.cedula = p.cedula
            WHERE p.cedula = ?
        ", [$cedula]);

        abort_if(!$paciente, 404);

        $provincias = DB::select("SELECT * FROM PROVINCIA ORDER BY nombre_provincia");
        $etnias     = DB::select("SELECT * FROM ETNIA ORDER BY tipo_etnia");

        // Para pre-seleccionar canton y parroquia
        $parroquia = DB::selectOne("
            SELECT par.*, c.codigo_canton, c.nombre_canton, pr.codigo_provincia
            FROM PARROQUIA par
            JOIN CANTON c   ON c.codigo_canton    = par.codigo_canton
            JOIN PROVINCIA pr ON pr.codigo_provincia = c.codigo_provincia
            WHERE par.codigo_parroquia = ?
        ", [$paciente->codigo_parroquia]);

        return view('paramedico.pacientes.edit',
            compact('paciente', 'provincias', 'etnias', 'parroquia'));
    }

    // ── Actualizar paciente ───────────────────────────────────────
    public function update(Request $request, $cedula)
    {
        $request->validate([
            'nombres'   => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'correo'    => 'required|email|max:100',
        ]);

        DB::statement("
            UPDATE PERSONAS SET
                nombres = ?, apellido1 = ?, apellido2 = ?,
                correo = ?, telefono = ?, fecha_nac = ?,
                genero = ?, estado_civil = ?
            WHERE cedula = ?
        ", [
            $request->nombres, $request->apellido1, $request->apellido2,
            $request->correo, $request->telefono, $request->fecha_nac,
            $request->genero, $request->estado_civil, $cedula,
        ]);

        DB::statement("
            UPDATE PACIENTE SET
                codigo_parroquia = ?, id_etnia = ?,
                tipo_sangre = ?, alergias = ?, antecedentes = ?,
                medicacion_habitual = ?,
                discapacidad = ?, gestante = ?,
                fumador = ?, consume_alcohol = ?
            WHERE cedula = ?
        ", [
            $request->codigo_parroquia, $request->id_etnia ?: null,
            $request->tipo_sangre ?: 'Desconocido',
            $request->alergias, $request->antecedentes,
            $request->medicacion_habitual,
            $request->has('discapacidad') ? 1 : 0,
            $request->has('gestante')     ? 1 : 0,
            $request->has('fumador')      ? 1 : 0,
            $request->has('consume_alcohol') ? 1 : 0,
            $cedula,
        ]);

        return redirect()->route('paramedico.pacientes.show', $cedula)
            ->with('success', 'Paciente actualizado correctamente.');
    }

    // ── AJAX: cantones por provincia ──────────────────────────────
    public function cantones($provincia)
    {
        $cantones = DB::select("
            SELECT codigo_canton, nombre_canton FROM CANTON
            WHERE codigo_provincia = ? ORDER BY nombre_canton
        ", [$provincia]);
        return response()->json($cantones);
    }

    // ── AJAX: parroquias por canton ───────────────────────────────
    public function parroquias($canton)
    {
        $parroquias = DB::select("
            SELECT codigo_parroquia, nombre_parroquia FROM PARROQUIA
            WHERE codigo_canton = ? ORDER BY nombre_parroquia
        ", [$canton]);
        return response()->json($parroquias);
    }
}