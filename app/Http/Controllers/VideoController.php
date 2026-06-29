<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class VideoController extends Controller
{
    private function cedula(): ?string
    {
        return Session::get('usuario_cedula');
    }

    // Lista de pacientes con cita hoy (para médico/paramédico)
    public function index()
    {
        $cedula = $this->cedula();
        $rol    = Session::get('usuario_rol');
        if (!$cedula) return redirect()->route('login');

        if ($rol === 'paramedico') {
            $pacientes = DB::table('PERSONAS as p')
                ->join('PACIENTE as pac', 'p.cedula', '=', 'pac.cedula')
                ->where('p.estado', 'activo')
                ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.telefono')
                ->orderBy('p.apellido1')
                ->orderBy('p.nombres')
                ->get();

            return view('paramedico.video', compact('pacientes'));
        }

        // Médico: solo citas virtuales de hoy que aún no han pasado
        $pacientes = DB::table('CITAS as c')
            ->join('PERSONAS as p', 'p.cedula', '=', 'c.cedula_paciente')
            ->where('c.cedula_medico', $cedula)
            ->whereDate('c.fecha_hora', today())
            ->where('c.fecha_hora', '>=', now())
            ->where('c.tipo_cita', 'virtual')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'c.fecha_hora', 'c.id_cita')
            ->orderBy('c.fecha_hora')
            ->get();

        return view('medico.video', compact('pacientes'));
    }

    // Médico/paramédico inicia llamada
    public function iniciar(Request $request)
    {
        $cedula = $this->cedula();
        if (!$cedula) return response()->json(['error' => 'No autenticado'], 401);

        $cedulaPaciente = $request->cedula_paciente;
        $roomId = 'vc_' . substr(md5($cedula . $cedulaPaciente . time()), 0, 10);

        Cache::put("vc_{$roomId}", [
            'status'     => 'calling',
            'caller'     => $cedula,
            'callee'     => $cedulaPaciente,
            'offer'      => null,
            'answer'     => null,
            'ice_caller' => [],
            'ice_callee' => [],
            'chat'       => [],
        ], now()->addHours(2));

        Cache::put("vc_incoming_{$cedulaPaciente}", $roomId, now()->addMinutes(30));

        return response()->json(['room' => $roomId]);
    }

    // Intercambio SDP (offer/answer)
    public function signal(Request $request)
    {
        $cedula = $this->cedula();
        if (!$cedula) return response()->json(['error' => 'No autenticado'], 401);

        $data = Cache::get("vc_{$request->room}");
        if (!$data) return response()->json(['error' => 'Sala no encontrada'], 404);

        $data[$request->tipo] = $request->sdp;
        if ($request->tipo === 'answer') {
            $data['status'] = 'connected';
            Cache::forget("vc_incoming_{$data['callee']}");
        }

        Cache::put("vc_{$request->room}", $data, now()->addHours(2));
        return response()->json(['ok' => true]);
    }

    // Intercambio ICE candidates
    public function candidate(Request $request)
    {
        $cedula = $this->cedula();
        if (!$cedula) return response()->json(['error' => 'No autenticado'], 401);

        $data = Cache::get("vc_{$request->room}");
        if (!$data) return response()->json(['error' => 'Sala no encontrada'], 404);

        $key = 'ice_' . $request->rol; // 'ice_caller' | 'ice_callee'
        $data[$key][] = $request->candidate;
        Cache::put("vc_{$request->room}", $data, now()->addHours(2));

        return response()->json(['ok' => true]);
    }

    // Poll del estado de la sala (ambas partes)
    public function poll(Request $request)
    {
        $data = Cache::get("vc_{$request->room}");
        if (!$data) return response()->json(['status' => 'ended']);

        $rol     = $request->rol; // 'caller' | 'callee'
        $iceKey  = $rol === 'caller' ? 'ice_callee' : 'ice_caller';
        $iceOff  = (int) $request->ice_offset;
        $chatOff = (int) $request->chat_offset;

        return response()->json([
            'status'     => $data['status'],
            'offer'      => $data['offer'],
            'answer'     => $data['answer'],
            'candidates' => array_values(array_slice($data[$iceKey] ?? [], $iceOff)),
            'chat'       => array_values(array_slice($data['chat'] ?? [], $chatOff)),
            'ice_total'  => count($data[$iceKey] ?? []),
            'chat_total' => count($data['chat'] ?? []),
        ]);
    }

    // Paciente: sondea si hay llamada entrante
    public function entrante()
    {
        $cedula = $this->cedula();
        if (!$cedula) return response()->json(['room' => null]);

        $roomId = Cache::get("vc_incoming_{$cedula}");
        if (!$roomId) return response()->json(['room' => null]);

        $data = Cache::get("vc_{$roomId}");
        if (!$data || $data['status'] === 'ended') {
            Cache::forget("vc_incoming_{$cedula}");
            return response()->json(['room' => null]);
        }

        $caller = DB::table('PERSONAS')->where('cedula', $data['caller'])->first();
        $nombre = $caller ? trim("{$caller->nombres} {$caller->apellido1}") : 'Tu médico';

        return response()->json(['room' => $roomId, 'caller' => $nombre]);
    }

    // Enviar mensaje de chat
    public function mensaje(Request $request)
    {
        $cedula = $this->cedula();
        if (!$cedula) return response()->json(['error' => 'No autenticado'], 401);

        $data = Cache::get("vc_{$request->room}");
        if (!$data) return response()->json(['error' => 'Sala no encontrada'], 404);

        $persona = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $data['chat'][] = [
            'cedula' => $cedula,
            'nombre' => $persona ? $persona->nombres : 'Usuario',
            'texto'  => substr(strip_tags($request->texto), 0, 500),
            'hora'   => now()->format('H:i'),
        ];

        Cache::put("vc_{$request->room}", $data, now()->addHours(2));
        return response()->json(['ok' => true]);
    }

    // Terminar llamada
    public function terminar(Request $request)
    {
        $data = Cache::get("vc_{$request->room}", []);
        $data['status'] = 'ended';
        Cache::put("vc_{$request->room}", $data, now()->addMinutes(10));
        if (isset($data['callee'])) {
            Cache::forget("vc_incoming_{$data['callee']}");
        }
        return response()->json(['ok' => true]);
    }

    // Sala de videollamada (ambas partes)
    public function sala(string $roomId)
    {
        $cedula = $this->cedula();
        $rol    = Session::get('usuario_rol');
        if (!$cedula) return redirect()->route('login');

        $data     = Cache::get("vc_{$roomId}");
        $esCaller = $data && $data['caller'] === $cedula;

        // Nombre propio para el chat
        $persona  = DB::table('PERSONAS')->where('cedula', $cedula)->first();
        $miNombre = $persona ? $persona->nombres : 'Yo';

        // Nombre del otro participante
        $otraCedula = $esCaller ? ($data['callee'] ?? null) : ($data['caller'] ?? null);
        $otro = $otraCedula ? DB::table('PERSONAS')->where('cedula', $otraCedula)->first() : null;
        $otroNombre = $otro ? trim("{$otro->nombres} {$otro->apellido1}") : 'Participante';

        return view('video.sala', [
            'roomId'       => $roomId,
            'esCaller'     => $esCaller,
            'miCedula'     => $cedula,
            'miNombre'     => $miNombre,
            'otroNombre'   => $otroNombre,
            'rolSistema'   => $rol,
        ]);
    }
}
