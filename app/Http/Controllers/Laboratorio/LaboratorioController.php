<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LaboratorioController extends Controller
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
        return redirect()->route('laboratorio.buscar');
    }

    // ── Buscar + mostrar exámenes ────────────────────────────
    public function buscar(Request $request)
    {
        $cedula   = $request->query('cedula');
        $paciente = null;
        $examen   = null;
        $error    = null;

        if ($cedula) {
            $request->validate(['cedula' => 'required|digits:10']);

            $paciente = DB::table('PERSONAS as p')
                ->leftJoin('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
                ->where('p.cedula', $cedula)
                ->select('p.cedula','p.nombres','p.apellido1','p.apellido2',
                         'p.fecha_nac','p.genero','p.telefono','pac.tipo_sangre')
                ->first();

            if (! $paciente) {
                $error = "No se encontró ningún paciente con la cédula: {$cedula}";
            } else {
                // Última orden de exámenes escrita por el médico en consulta
                $examen = DB::table('CITAS as c')
                    ->join('PERSONAS as m', 'c.cedula_medico', '=', 'm.cedula')
                    ->leftJoin('MEDICO_ESPECIALIDAD as me', 'c.cedula_medico', '=', 'me.cedula_medico')
                    ->leftJoin('ESPECIALIDAD as e', 'me.id_especialidad', '=', 'e.id_especialidad')
                    ->where('c.cedula_paciente', $cedula)
                    ->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->whereNotNull('c.examen_laboratorio')->where('c.examen_laboratorio', '!=', '');
                        })->orWhere(function ($q2) {
                            $q2->whereNotNull('c.examen_imagenes')->where('c.examen_imagenes', '!=', '');
                        });
                    })
                    ->orderByDesc('c.fecha_hora')
                    ->select(
                        'c.id_cita','c.fecha_hora','c.examen_laboratorio','c.examen_imagenes',
                        'c.diagnostico','c.motivo_consulta',
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

        return view('laboratorio.buscar', compact(
            'paciente', 'examen', 'error', 'cedula',
            'persona', 'nombre', 'iniciales'
        ));
    }
}