<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\ExamenService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ExamenMedicoController extends Controller
{
    // ── Ver archivos de un paciente ──────────────────────────────────
    public function index(string $cedula)
    {
        $paciente = Persona::find($cedula);
        if (!$paciente) abort(404);

        $archivos = ExamenService::listar($cedula);

        return view('medico.examenes-paciente', compact('paciente', 'archivos'));
    }

    // ── Descargar archivo del paciente ───────────────────────────────
    public function descargar(string $cedula, string $id)
    {
        $entry = ExamenService::obtener($cedula, $id);
        if (!$entry) abort(404);

        $ruta = ExamenService::rutaStorage($cedula, $entry['nombre_guardado']);
        if (!Storage::exists($ruta)) abort(404);

        return Storage::download($ruta, $entry['nombre_original']);
    }
}
