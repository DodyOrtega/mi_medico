<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FarmaciaController extends Controller
{
    private function cedula(): string
    {
        return Session::get('usuario_cedula');
    }

    private function datosLayout(): array
    {
        $cedula  = $this->cedula();
        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $nombre  = trim($persona->nombres . ' ' . $persona->apellido1);
        $iniciales = strtoupper(substr($persona->nombres, 0, 1) . substr($persona->apellido1, 0, 1));
        return [$persona, $nombre, $iniciales];
    }

    // ── Dashboard → redirige directo al buscador ─────────────
    public function dashboard()
    {
        return redirect()->route('farmacia.buscar');
    }

    // ── Buscar + mostrar última receta ───────────────────────
    public function buscar(Request $request)
    {
        $cedula   = $request->query('cedula');
        $paciente = null;
        $receta   = null;
        $error    = null;

        if ($cedula) {
            $request->validate(['cedula' => 'required|digits:10']);

            $paciente = DB::table('PERSONAS as p')
                ->where('p.cedula', $cedula)
                ->select('p.cedula','p.nombres','p.apellido1','p.apellido2','p.fecha_nac')
                ->first();

            if (! $paciente) {
                $error = "No se encontró ningún paciente con la cédula: {$cedula}";
            } else {
                $receta = DB::table('CITAS as c')
                    ->join('PERSONAS as m', 'c.cedula_medico', '=', 'm.cedula')
                    ->leftJoin('MEDICO_ESPECIALIDAD as me', 'c.cedula_medico', '=', 'me.cedula_medico')
                    ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
                    ->where('c.cedula_paciente', $cedula)
                    ->whereNotNull('c.receta')
                    ->where('c.receta', '!=', '')
                    ->orderByDesc('c.fecha_hora')
                    ->select(
                        'c.id_cita','c.fecha_hora','c.receta','c.diagnostico',
                        'm.nombres as medico_nombres','m.apellido1 as medico_apellido1',
                        'e.nombre as especialidad'
                    )
                    ->first();

                if ($paciente->fecha_nac) {
                    $paciente->edad = \Carbon\Carbon::parse($paciente->fecha_nac)->age;
                }
            }
        }

        [$persona, $nombre, $iniciales] = $this->datosLayout();

        return view('farmacia.buscar', compact(
            'paciente', 'receta', 'error', 'cedula',
            'persona', 'nombre', 'iniciales'
        ));
    }
}