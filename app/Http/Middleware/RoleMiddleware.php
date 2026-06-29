<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 *
 * Protege rutas según el rol del usuario autenticado (vía sesión).
 * Uso en rutas: ->middleware('role:admin') o ->middleware('role:medico,paramedico')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$rolesPermitidos): Response
    {
        // 1. ¿Hay sesión activa?
        if (!Session::has('usuario_cedula')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder a esta página.')
                ->with('error_tipo', 'invalido');
        }

        $rolUsuario = Session::get('usuario_rol');

        // 2. ¿El rol del usuario está en la lista permitida para esta ruta?
        if (!in_array($rolUsuario, $rolesPermitidos)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}