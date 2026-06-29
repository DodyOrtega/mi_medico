<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $fecha    = $request->get('fecha', now()->format('Y-m-d'));
        $entradas = Auditoria::leerDia($fecha);
        $dias     = Auditoria::diasDisponibles();

        return view('admin.auditoria.index', compact('entradas', 'fecha', 'dias'));
    }
}
