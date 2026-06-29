<?php

namespace App\Http\Controllers\Paramedico;

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotificacionController extends Controller
{
    // GET /paramedico/notif — devuelve JSON con notificaciones no leídas
    public function index()
    {
        $cedula  = Session::get('usuario_cedula');
        $leidas  = Cache::get("notif_leidas_{$cedula}", []);
        $config    = ConfiguracionController::leerConfig();
        $cfgNotif  = $config['notificaciones'] ?? [];
        $cfgCitas  = $config['citas'] ?? [];

        $notifNuevaCita     = $cfgNotif['notif_nueva_cita']     ?? true;
        $notifRecordatorio  = $cfgNotif['notif_recordatorio']   ?? true;
        $notifUrgencia      = $cfgNotif['notif_urgencia']       ?? true;
        $notifCancelada     = $cfgNotif['notif_cita_cancelada'] ?? true;
        $recordatorioActivo = $cfgCitas['recordatorio_cita']    ?? true;
        $horasRecordatorio  = max(1, (int) ($cfgCitas['tiempo_recordatorio'] ?? 24));

        $notificaciones = [];

        // 1. Próximas citas — controlado por notif_nueva_cita, notif_recordatorio y tiempo_recordatorio
        if (($notifNuevaCita || $notifRecordatorio) && $recordatorioActivo) {
            $citas = DB::select("
                SELECT c.id_cita, c.fecha_hora, c.tipo_cita, p.nombres, p.apellido1
                FROM CITAS c
                JOIN PERSONAS p ON p.cedula = c.cedula_paciente
                WHERE c.cedula_medico = ?
                  AND c.fecha_hora > NOW()
                  AND c.fecha_hora <= NOW() + INTERVAL {$horasRecordatorio} HOUR
                ORDER BY c.fecha_hora ASC
                LIMIT 10
            ", [$cedula]);

            foreach ($citas as $c) {
                $key = 'cita_' . $c->id_cita;
                if (in_array($key, $leidas)) continue;
                $notificaciones[] = [
                    'key'     => $key,
                    'titulo'  => $c->nombres . ' ' . $c->apellido1,
                    'mensaje' => 'Cita ' . $c->tipo_cita . ' — ' . \Carbon\Carbon::parse($c->fecha_hora)->format('d/m H:i'),
                    'icono'   => $c->tipo_cita === 'virtual' ? 'video' : 'hospital',
                    'color'   => 'blue',
                    'ruta'    => route('paramedico.citas.agendar'),
                    'tiempo'  => \Carbon\Carbon::parse($c->fecha_hora)->diffForHumans(),
                ];
            }
        }

        // 2. Citas/slots cancelados hoy — controlado por notif_cita_cancelada
        if ($notifCancelada) {
            $canceladas = DB::select("
                SELECT a.id_agenda, a.hora, p.nombres, p.apellido1
                FROM AGENDA a
                JOIN PERSONAS p ON p.cedula = a.cedula_paciente
                WHERE a.cedula_medico = ?
                  AND a.estado = 'cancelado'
                  AND a.fecha = CURDATE()
                ORDER BY a.hora DESC
                LIMIT 5
            ", [$cedula]);

            foreach ($canceladas as $ca) {
                $key = 'cancel_' . $ca->id_agenda;
                if (in_array($key, $leidas)) continue;
                $notificaciones[] = [
                    'key'     => $key,
                    'titulo'  => $ca->nombres . ' ' . $ca->apellido1,
                    'mensaje' => 'Canceló su cita de las ' . substr($ca->hora, 0, 5),
                    'icono'   => 'calendar-xmark',
                    'color'   => 'orange',
                    'ruta'    => route('paramedico.citas.agendar'),
                    'tiempo'  => 'Hoy',
                ];
            }
        }

        // 3. Emergencias activas del día — controlado por notif_urgencia
        if ($notifUrgencia) {
            $emergencias = DB::select("
                SELECT a.id_agenda, a.motivo_emergencia, a.hora,
                       p.nombres, p.apellido1
                FROM AGENDA a
                JOIN PERSONAS p ON p.cedula = a.cedula_paciente
                WHERE a.es_emergencia = 1
                  AND a.fecha = CURDATE()
                  AND a.estado = 'disponible'
                ORDER BY a.hora DESC
            ");

            foreach ($emergencias as $e) {
                $key = 'emerg_' . $e->id_agenda;
                if (in_array($key, $leidas)) continue;
                $notificaciones[] = [
                    'key'       => $key,
                    'id_agenda' => $e->id_agenda,
                    'titulo'    => $e->nombres . ' ' . $e->apellido1,
                    'mensaje'   => $e->motivo_emergencia ?? 'Sin descripción',
                    'icono'     => 'triangle-exclamation',
                    'color'     => 'red',
                    'es_emerg'  => true,
                    'ruta'      => route('paramedico.emergencias.index'),
                    'tiempo'    => $e->hora,
                ];
            }
        }

        return response()->json([
            'total'          => count($notificaciones),
            'notificaciones' => $notificaciones,
        ]);
    }

    // POST /*/emergencia/atender — acepta la emergencia e inicia videollamada
    public function aceptarEmergencia()
    {
        $idAgenda = request()->input('id_agenda');
        $cedula   = Session::get('usuario_cedula');

        if (!$idAgenda) {
            return response()->json(['ok' => false, 'msg' => 'ID inválido'], 422);
        }

        $slot = DB::table('AGENDA')
            ->where('id_agenda', $idAgenda)
            ->where('es_emergencia', 1)
            ->where('estado', 'disponible')
            ->first();

        if (!$slot) {
            return response()->json(['ok' => false, 'msg' => 'Esta emergencia ya fue atendida.']);
        }

        DB::table('AGENDA')->where('id_agenda', $idAgenda)->update([
            'estado'        => 'ocupado',
            'cedula_medico' => $cedula,
        ]);

        $cedulaPaciente = $slot->cedula_paciente;
        $roomId = 'vc_emerg_' . substr(md5($cedula . $cedulaPaciente . time()), 0, 10);

        \Illuminate\Support\Facades\Cache::put("vc_{$roomId}", [
            'status'     => 'calling',
            'caller'     => $cedula,
            'callee'     => $cedulaPaciente,
            'offer'      => null,
            'answer'     => null,
            'ice_caller' => [],
            'ice_callee' => [],
            'chat'       => [],
        ], now()->addHours(2));

        \Illuminate\Support\Facades\Cache::put("vc_incoming_{$cedulaPaciente}", $roomId, now()->addMinutes(30));

        return response()->json(['ok' => true, 'room' => $roomId]);
    }

    // POST /*/notif/leida
    public function marcarLeida()
    {
        $cedula = Session::get('usuario_cedula');
        $key    = request()->input('key');
        $leidas = Cache::get("notif_leidas_{$cedula}", []);
        if ($key && !in_array($key, $leidas)) {
            $leidas[] = $key;
            Cache::put("notif_leidas_{$cedula}", $leidas, now()->addDays(30));
        }
        return response()->json(['ok' => true]);
    }

    // POST /*/notif/todas
    public function marcarTodas()
    {
        $cedula = Session::get('usuario_cedula');
        $ids    = [];

        $horas = max(1, (int) (ConfiguracionController::leerConfig()['citas']['tiempo_recordatorio'] ?? 24));

        $citas = DB::select("
            SELECT id_cita FROM CITAS
            WHERE cedula_medico = ?
              AND fecha_hora > NOW()
              AND fecha_hora <= NOW() + INTERVAL {$horas} HOUR
        ", [$cedula]);
        foreach ($citas as $c) $ids[] = 'cita_' . $c->id_cita;

        $ems = DB::select("
            SELECT id_agenda FROM AGENDA
            WHERE es_emergencia = 1 AND fecha = CURDATE() AND estado = 'disponible'
        ");
        foreach ($ems as $e) $ids[] = 'emerg_' . $e->id_agenda;

        Cache::put("notif_leidas_{$cedula}", $ids, now()->addDays(30));
        return response()->json(['ok' => true]);
    }
}