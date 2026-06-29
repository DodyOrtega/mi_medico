<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Services\ExamenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ExamenController extends Controller
{
    private function cedula(): string
    {
        return Session::get('usuario_cedula');
    }

    // ── Subir archivo ────────────────────────────────────────────────
    public function subir(Request $request)
    {
        $maxMb  = ExamenService::maxMb();
        $tipos  = implode(',', ExamenService::tiposPermitidos());

        $request->validate([
            'archivo'     => "required|file|max:" . ($maxMb * 1024) . "|mimes:{$tipos}",
            'descripcion' => 'nullable|string|max:120',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.max'      => "El archivo no puede superar {$maxMb} MB.",
            'archivo.mimes'    => 'Solo se permiten archivos: PDF, JPG, PNG, WEBP.',
        ]);

        ExamenService::guardar($this->cedula(), $request->file('archivo'), $request->descripcion ?? '');

        return back()->with('exito', 'Archivo subido correctamente.');
    }

    // ── Descargar / visualizar archivo ───────────────────────────────
    public function descargar(string $id)
    {
        $cedula = $this->cedula();
        $entry  = ExamenService::obtener($cedula, $id);

        if (!$entry) abort(404);

        $ruta = ExamenService::rutaStorage($cedula, $entry['nombre_guardado']);
        if (!Storage::exists($ruta)) abort(404);

        return Storage::download($ruta, $entry['nombre_original']);
    }

    // ── Eliminar archivo ─────────────────────────────────────────────
    public function eliminar(string $id)
    {
        ExamenService::eliminar($this->cedula(), $id);
        return back()->with('exito', 'Archivo eliminado.');
    }
}
