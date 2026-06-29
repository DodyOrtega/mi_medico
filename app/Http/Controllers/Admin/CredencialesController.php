<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CredencialesController extends Controller
{
    public function index()
    {
        $usuarios = DB::table('PERSONAS')
            ->where(function ($q) {
                $q->where('correo', 'like', '%@laboratorio%')
                  ->orWhere('correo', 'like', '%@farmacia%');
            })
            ->select('cedula', 'nombres', 'apellido1', 'correo', 'estado')
            ->orderBy('correo')
            ->get()
            ->map(function ($u) {
                $u->rol = str_contains($u->correo, '@laboratorio') ? 'laboratorio' : 'farmacia';
                return $u;
            });

        return view('admin.credenciales.index', compact('usuarios'));
    }

    public function generar(Request $request)
    {
        $request->validate([
            'rol'    => 'required|in:laboratorio,farmacia',
            'nombre' => 'nullable|string|max:40',
        ]);

        $rol = $request->input('rol');

        $cedula   = $this->generarCedula($rol);
        $password = $this->generarPassword();
        $nombre   = trim($request->input('nombre') ?: ($rol === 'laboratorio' ? 'Laboratorio' : 'Farmacia'));
        $num      = substr($cedula, 3); // los 7 dígitos finales
        $correo   = ($rol === 'laboratorio' ? 'lab' : 'farm') . ltrim($num, '0') . '@' . $rol . '.local';

        if (DB::table('PERSONAS')->where('cedula', $cedula)->exists()) {
            return response()->json(['ok' => false, 'msg' => 'Error generando usuario único, intente de nuevo.']);
        }

        DB::table('PERSONAS')->insert([
            'cedula'       => $cedula,
            'nombres'      => $nombre,
            'apellido1'    => ucfirst($rol),
            'apellido2'    => '',
            'tipo_cedula'  => 'cedula',
            'fecha_nac'    => '1990-01-01',
            'estado_civil' => 'S',
            'correo'       => $correo,
            'contrasena'   => Hash::make($password),
            'telefono'     => '',
            'genero'       => 'M',
            'estado'       => 'activo',
        ]);

        return response()->json([
            'ok'       => true,
            'cedula'   => $cedula,
            'password' => $password,
            'correo'   => $correo,
            'nombre'   => $nombre,
            'rol'      => $rol,
        ]);
    }

    public function revocar(string $cedula)
    {
        $user = DB::table('PERSONAS')
            ->where('cedula', $cedula)
            ->where(function ($q) {
                $q->where('correo', 'like', '%@laboratorio%')
                  ->orWhere('correo', 'like', '%@farmacia%');
            })
            ->first();

        if (! $user) {
            return response()->json(['ok' => false, 'msg' => 'Usuario no encontrado.']);
        }

        $nuevoEstado = $user->estado === 'activo' ? 'inactivo' : 'activo';

        DB::table('PERSONAS')->where('cedula', $cedula)->update(['estado' => $nuevoEstado]);

        return response()->json(['ok' => true, 'estado' => $nuevoEstado]);
    }

    public function resetPassword(string $cedula)
    {
        $user = DB::table('PERSONAS')
            ->where('cedula', $cedula)
            ->where(function ($q) {
                $q->where('correo', 'like', '%@laboratorio%')
                  ->orWhere('correo', 'like', '%@farmacia%');
            })
            ->first();

        if (! $user) {
            return response()->json(['ok' => false, 'msg' => 'Usuario no encontrado.']);
        }

        $password = $this->generarPassword();

        DB::table('PERSONAS')->where('cedula', $cedula)->update([
            'contrasena' => Hash::make($password),
        ]);

        return response()->json(['ok' => true, 'password' => $password]);
    }

    private function generarCedula(string $rol): string
    {
        $prefix       = $rol === 'laboratorio' ? 'LAB' : 'FAR';
        $emailPattern = $rol === 'laboratorio' ? '%@laboratorio%' : '%@farmacia%';

        $count = DB::table('PERSONAS')
            ->where('correo', 'like', $emailPattern)
            ->count();

        return $prefix . str_pad($count + 1, 7, '0', STR_PAD_LEFT);
    }

    private function generarPassword(): string
    {
        $upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower  = 'abcdefghjkmnpqrstuvwxyz';
        $digits = '23456789';
        $all    = $upper . $lower . $digits;

        // Al menos 1 de cada tipo
        $pwd = $upper[random_int(0, strlen($upper) - 1)]
             . $lower[random_int(0, strlen($lower) - 1)]
             . $digits[random_int(0, strlen($digits) - 1)]
             . $digits[random_int(0, strlen($digits) - 1)];

        for ($i = 4; $i < 10; $i++) {
            $pwd .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($pwd);
    }
}
