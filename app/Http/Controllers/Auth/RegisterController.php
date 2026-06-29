<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegister()
    {
        $parroquias = DB::table('PARROQUIA as pa')
            ->join('CANTON as ca', 'pa.codigo_canton', '=', 'ca.codigo_canton')
            ->join('PROVINCIA as pr', 'ca.codigo_provincia', '=', 'pr.codigo_provincia')
            ->select('pa.codigo_parroquia', 'pa.nombre_parroquia',
                     'ca.nombre_canton', 'pr.nombre_provincia')
            ->orderBy('pr.nombre_provincia')
            ->orderBy('ca.nombre_canton')
            ->orderBy('pa.nombre_parroquia')
            ->get();

        return view('auth.registro', compact('parroquias'));
    }

    public function register(Request $request)
    {
        $minPass = (int) (\App\Http\Controllers\Admin\ConfiguracionController::leerConfig()['seguridad']['pass_min_length'] ?? 8);

        $validator = Validator::make($request->all(), [
            'cedula'                => 'required|string|size:10|unique:PERSONAS,cedula',
            'nombres'               => 'required|string|max:40',
            'apellido1'             => 'required|string|max:40',
            'correo'                => 'required|email|max:100|unique:PERSONAS,correo',
            'contrasena'            => "required|string|min:{$minPass}|confirmed",
            'fecha_nac'             => 'required|date',
            'codigo_parroquia'      => 'required|integer',
        ], [
            'contrasena.min' => "La contraseña debe tener al menos {$minPass} caracteres.",
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
                'estado_civil' => 'S',
                'correo'       => trim($request->correo),
                'contrasena'   => Hash::make($request->contrasena),
                'telefono'     => trim($request->telefono ?? ''),
                'genero'       => $request->genero ?? 'M',
                'estado'       => 'activo',
            ]);

            DB::table('PACIENTE')->insert([
                'cedula'           => $cedula,
                'codigo_parroquia' => $request->codigo_parroquia,
                'tipo_sangre'      => 'Desconocido',
            ]);

            DB::commit();
            return redirect()->route('login')
                ->with('exito', '¡Cuenta creada! Ya puedes iniciar sesión.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }
}
