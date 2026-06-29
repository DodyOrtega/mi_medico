<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Session::has('usuario_cedula')) {
            return $this->redirigirPorRol(Session::get('usuario_rol'));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|string',
            'contrasena'     => 'required|string',
        ]);

        $input = trim($request->nombre_usuario);

        $persona = Persona::where('correo', $input)
                          ->orWhere('cedula', $input)
                          ->first();

        if (!$persona) {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->with('error', 'Credenciales incorrectas. Verifica tu usuario y contraseña.')
                ->with('error_tipo', 'invalido');
        }

        if ($persona->estado === 'inactivo') {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.')
                ->with('error_tipo', 'inactivo');
        }

        if (!Hash::check($request->contrasena, $persona->contrasena)) {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->with('error', 'Credenciales incorrectas. Verifica tu usuario y contraseña.')
                ->with('error_tipo', 'invalido');
        }

        $rol = $persona->getRol();

        Session::put('usuario_cedula',  $persona->cedula);
        Session::put('usuario_nombre',  $persona->nombre_completo);
        Session::put('usuario_rol',     $rol);
        Session::put('usuario_correo',  $persona->correo);
        Session::put('login_success',   true);

        Auditoria::registrar('login', "Inicio de sesión como {$rol}");

        return $this->redirigirPorRol($rol);
    }

    public function logout()
    {
        Auditoria::registrar('logout', 'Cierre de sesión');
        Session::flush();
        return redirect()->route('login')
                         ->with('mensaje', 'Sesión cerrada correctamente.');
    }

    private function redirigirPorRol(string $rol)
    {
        return match($rol) {
            'admin'       => redirect()->route('admin.dashboard'),
            'medico'      => redirect()->route('medico.dashboard'),
            'paramedico'  => redirect()->route('paramedico.dashboard'),
            'paciente'    => redirect()->route('paciente.dashboard'),
            'laboratorio' => redirect()->route('laboratorio.dashboard'),
            'farmacia'    => redirect()->route('farmacia.dashboard'),
            default       => redirect()->route('inicio')
                                       ->with('error', 'Rol no reconocido.'),
        };
    }
}