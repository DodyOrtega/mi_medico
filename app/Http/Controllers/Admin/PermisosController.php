<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auditoria;
use App\Services\Permisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    public function index()
    {
        $medicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Medico')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.estado')
            ->orderBy('p.apellido1')
            ->get();

        $paramedicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Paramedico')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.estado')
            ->orderBy('p.apellido1')
            ->get();

        return view('admin.permisos.index', compact('medicos', 'paramedicos'));
    }

    public function update(Request $request, string $cedula)
    {
        $rol     = $request->input('rol');
        $claves  = $rol === 'medico' ? array_keys(Permisos::MEDICO_MODULOS) : array_keys(Permisos::PARA_MODULOS);
        $modulos = [];

        foreach ($claves as $clave) {
            $modulos[$clave] = $request->boolean("perm_{$clave}");
        }

        Permisos::guardar($cedula, $modulos);
        Auditoria::registrar('permisos_actualizar', "Permisos actualizados para cédula {$cedula} ({$rol})");

        return back()->with('exito', 'Permisos actualizados correctamente.');
    }
}
