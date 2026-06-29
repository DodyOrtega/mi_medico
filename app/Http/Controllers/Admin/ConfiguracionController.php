<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ConfiguracionController
 *
 * Configuración general del sistema. NO crea tablas nuevas en la base
 * de datos: los valores viven en config/sistema.php y se actualizan
 * sobrescribiendo ese archivo. Esto respeta la restricción de no tocar
 * el esquema de base de datos mientras se confirma con el docente si
 * tablas técnicas (config/auditoría/backups) están permitidas.
 *
 * Si más adelante se confirma que sí se pueden agregar tablas propias,
 * este controller es el punto de partida para migrar a SISTEMA_CONFIG.
 */
class ConfiguracionController extends Controller
{
    private const GRUPOS_VALIDOS = ['general', 'citas', 'notificaciones', 'seguridad'];

    public function index()
    {
        $config = $this->leerConfig();

        $medicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Medico')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.estado')
            ->orderBy('p.apellido1')->get();

        $paramedicos = DB::table('PERSONAS as p')
            ->join('PERSONAL_MEDICO as pm', 'p.cedula', '=', 'pm.cedula')
            ->where('pm.tipo', 'Paramedico')
            ->select('p.cedula', 'p.nombres', 'p.apellido1', 'p.estado')
            ->orderBy('p.apellido1')->get();

        return view('admin.configuracion.index', compact('config', 'medicos', 'paramedicos'));
    }

    // ── Guardar un grupo de configuración ─────────────────────────
    public function update(Request $request, string $grupo)
    {
        if (!in_array($grupo, self::GRUPOS_VALIDOS)) {
            return back()->with('error', 'Grupo de configuración no reconocido.');
        }

        $valores = $request->except(['_token', '_method']);

        $camposBooleanos = [
            'recordatorio_cita', 'notif_nueva_cita', 'notif_cita_cancelada',
            'notif_recordatorio', 'notif_urgencia', 'sesion_unica',
        ];
        foreach ($camposBooleanos as $campo) {
            $configGrupo = $this->leerConfig()[$grupo] ?? [];
            if (array_key_exists($campo, $configGrupo)) {
                $valores[$campo] = $request->has($campo);
            }
        }

        $this->guardarGrupo($grupo, $valores);

        Auditoria::registrar('config_cambio', "Configuración del grupo '{$grupo}' actualizada");

        return back()->with('exito', 'Configuración guardada correctamente.');
    }

    // ── Leer config: JSON guardado tiene prioridad, fallback a sistema.php ──
    public static function leerConfig(): array
    {
        if (Storage::exists('sistema.json')) {
            $datos = json_decode(Storage::get('sistema.json'), true);
            if (is_array($datos)) {
                return $datos;
            }
        }
        return config('sistema', []);
    }

    // ── Persistir cambios en storage/app/sistema.json ─────────────
    private function guardarGrupo(string $grupo, array $valores): void
    {
        $configActual = self::leerConfig();

        foreach ($valores as $clave => $valor) {
            if (array_key_exists($clave, $configActual[$grupo] ?? [])) {
                $configActual[$grupo][$clave] = $valor;
            }
        }

        Storage::put('sistema.json', json_encode($configActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ── Limpiar caché del sistema ──────────────────────────────────
    public function limpiarCache()
    {
        Artisan::call('optimize:clear');

        return back()->with('exito', 'Caché del sistema limpiada correctamente.');
    }

    // ── WhatsApp WAHA ──────────────────────────────────────────────
    private const WAHA_URL = 'http://waha:3000';
    private const WAHA_KEY = 'mimedico_waha_2024';

    public function wahaStatus()
    {
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeader('X-Api-Key', self::WAHA_KEY)
                ->get(self::WAHA_URL . '/api/sessions/default');

            if ($resp->successful()) {
                $data = $resp->json();
                return response()->json([
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'nombre' => $data['me']['pushname'] ?? null,
                    'numero' => $data['me']['id'] ?? null,
                ]);
            }
        } catch (\Throwable) {}

        return response()->json(['status' => 'OFFLINE']);
    }

    public function wahaQr()
    {
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeader('X-Api-Key', self::WAHA_KEY)
                ->get(self::WAHA_URL . '/api/default/auth/qr?format=image');

            if ($resp->successful()) {
                return response($resp->body(), 200)->header('Content-Type', 'image/png');
            }
        } catch (\Throwable) {}

        return response('', 503);
    }

    public function wahaLogout()
    {
        try {
            $http = \Illuminate\Support\Facades\Http::withHeader('X-Api-Key', self::WAHA_KEY);
            $http->timeout(5)->post(self::WAHA_URL . '/api/sessions/default/logout');
            sleep(1);
            $http->timeout(5)->post(self::WAHA_URL . '/api/sessions/default/start');
        } catch (\Throwable) {}

        return response()->json(['ok' => true]);
    }

    public function enviarRecordatorios()
    {
        $cfg   = self::leerConfig();
        $horas = max(1, (int) ($cfg['citas']['tiempo_recordatorio'] ?? 24));

        $citas = DB::select("
            SELECT c.id_cita, c.fecha_hora, c.tipo_cita,
                   pp.nombres AS pac_nombres, pp.apellido1 AS pac_apellido, pp.telefono AS pac_tel,
                   pm.nombres AS med_nombres, pm.apellido1 AS med_apellido,
                   COALESCE(e.nombre, 'General') AS especialidad
            FROM CITAS c
            JOIN PERSONAS pp ON pp.cedula = c.cedula_paciente
            JOIN PERSONAS pm ON pm.cedula = c.cedula_medico
            LEFT JOIN MEDICO_ESPECIALIDAD me ON me.cedula_medico = c.cedula_medico
            LEFT JOIN ESPECIALIDAD e ON e.id_especialidad = me.id_especialidad
            WHERE c.fecha_hora > NOW()
              AND c.fecha_hora <= NOW() + INTERVAL {$horas} HOUR
              AND pp.telefono IS NOT NULL AND pp.telefono != ''
        ");

        $enviados = 0;
        $fallidos = 0;
        $omitidos = 0;
        foreach ($citas as $cita) {
            $cacheKey = 'sms_recordatorio_' . $cita->id_cita;

            // No reenviar si ya fue notificada (evita duplicados por cron + trigger manual)
            if (Cache::has($cacheKey)) {
                $omitidos++;
                continue;
            }

            $fechaFmt  = \Carbon\Carbon::parse($cita->fecha_hora)->isoFormat('D [de] MMMM [de] YYYY');
            $horaFmt   = \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i');
            $nombreMed = strtoupper(trim("{$cita->med_nombres} {$cita->med_apellido}"));
            $nombrePac = trim("{$cita->pac_nombres} {$cita->pac_apellido}");
            $mensaje   = \App\Services\SmsService::formatearCita(
                'le recuerda su cita:',
                $cita->especialidad,
                $nombreMed,
                $fechaFmt,
                $horaFmt,
                (int) $cita->id_cita,
                $nombrePac
            );

            if (\App\Services\SmsService::enviar($cita->pac_tel, $mensaje)) {
                $enviados++;
                Cache::put($cacheKey, true, \Carbon\Carbon::parse($cita->fecha_hora)->addHours(2));
            } else {
                $fallidos++;
            }
        }

        $total = count($citas);
        Auditoria::registrar('recordatorios_whatsapp', "Recordatorios enviados: {$enviados}/{$total}, omitidos: {$omitidos}");

        return response()->json([
            'total'    => $total,
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            'omitidos' => $omitidos,
        ]);
    }
}