<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RecuperarContrasenaController extends Controller
{
    // ── Paso 1: mostrar formulario ────────────────────────────────────
    public function showForm()
    {
        if (session('usuario_cedula')) {
            return redirect()->route('login');
        }
        return view('auth.recuperar');
    }

    // ── Paso 1 POST: buscar usuario y enviar OTP por WhatsApp ────────
    public function enviarOtp(Request $request)
    {
        $request->validate(['identificador' => 'required|string|max:100']);

        $input   = trim($request->identificador);
        $persona = Persona::where('correo', $input)->orWhere('cedula', $input)->first();

        if (!$persona || !$persona->telefono) {
            return back()
                ->withInput()
                ->with('error', 'No encontramos una cuenta con teléfono registrado para ese dato.');
        }

        $otp   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(16));

        Cache::put("recuperar_{$token}", [
            'cedula'     => $persona->cedula,
            'otp'        => $otp,
            'verificado' => false,
            'intentos'   => 0,
        ], 600); // 10 minutos

        $nombre  = strtoupper(trim("{$persona->nombres} {$persona->apellido1}"));
        $mensaje = implode("\n", [
            "🔐 {$nombre}",
            "Solicitaste recuperar tu contraseña en *Mi Médico*.",
            "",
            "Tu código de verificación es:",
            "",
            "  *{$otp}*",
            "",
            "⏱️ Válido durante 10 minutos.",
            "Si no solicitaste esto, ignora este mensaje.",
            "",
            "No respondas a este número.",
        ]);

        if (!SmsService::enviar($persona->telefono, $mensaje)) {
            return back()
                ->withInput()
                ->with('error', 'No se pudo enviar el código por WhatsApp. Intenta más tarde o contacta al administrador.');
        }

        session(['recuperar_token' => $token]);

        $tel = preg_replace('/\D/', '', $persona->telefono);
        $telMasked = '****' . substr($tel, -4);

        return redirect()->route('recuperar.verificar')
                         ->with('tel_masked', $telMasked);
    }

    // ── Paso 2: mostrar formulario de OTP ────────────────────────────
    public function showVerificar()
    {
        if (!session('recuperar_token')) {
            return redirect()->route('recuperar.form');
        }
        return view('auth.verificar-otp');
    }

    // ── Paso 2 POST: verificar OTP ────────────────────────────────────
    public function verificarOtp(Request $request)
    {
        $request->validate(['codigo' => 'required|digits:6']);

        $token = session('recuperar_token');
        if (!$token) {
            return redirect()->route('recuperar.form')
                             ->with('error', 'Sesión expirada. Solicita un código nuevo.');
        }

        $datos = Cache::get("recuperar_{$token}");
        if (!$datos) {
            session()->forget('recuperar_token');
            return redirect()->route('recuperar.form')
                             ->with('error', 'El código ha expirado. Solicita uno nuevo.');
        }

        if ($datos['intentos'] >= 3) {
            Cache::forget("recuperar_{$token}");
            session()->forget('recuperar_token');
            return redirect()->route('recuperar.form')
                             ->with('error', 'Demasiados intentos fallidos. Solicita un código nuevo.');
        }

        if ($request->codigo !== $datos['otp']) {
            $datos['intentos']++;
            Cache::put("recuperar_{$token}", $datos, 600);
            $restantes = 3 - $datos['intentos'];
            return back()->with('error', "Código incorrecto. Te quedan {$restantes} intento(s).");
        }

        $datos['verificado'] = true;
        Cache::put("recuperar_{$token}", $datos, 600);

        return redirect()->route('recuperar.nueva');
    }

    // ── Paso 3: mostrar formulario de nueva contraseña ───────────────
    public function showNueva()
    {
        $token = session('recuperar_token');
        if (!$token) {
            return redirect()->route('recuperar.form');
        }

        $datos = Cache::get("recuperar_{$token}");
        if (!$datos || !$datos['verificado']) {
            return redirect()->route('recuperar.verificar');
        }

        return view('auth.nueva-contrasena');
    }

    // ── Paso 3 POST: guardar nueva contraseña ────────────────────────
    public function guardarNueva(Request $request)
    {
        $request->validate([
            'contrasena'  => 'required|min:8',
            'confirmacion'=> 'required|same:contrasena',
        ], [
            'contrasena.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'confirmacion.same'   => 'Las contraseñas no coinciden.',
        ]);

        $token = session('recuperar_token');
        if (!$token) {
            return redirect()->route('recuperar.form');
        }

        $datos = Cache::get("recuperar_{$token}");
        if (!$datos || !$datos['verificado']) {
            return redirect()->route('recuperar.verificar');
        }

        $persona = Persona::find($datos['cedula']);
        if (!$persona) {
            return redirect()->route('recuperar.form')
                             ->with('error', 'Usuario no encontrado.');
        }

        $persona->update(['contrasena' => Hash::make($request->contrasena)]);

        Cache::forget("recuperar_{$token}");
        session()->forget('recuperar_token');

        return redirect()->route('login')
                         ->with('mensaje', '✅ Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}
