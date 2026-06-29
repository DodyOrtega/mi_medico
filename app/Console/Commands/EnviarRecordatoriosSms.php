<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ConfiguracionController;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EnviarRecordatoriosSms extends Command
{
    protected $signature   = 'sms:recordatorios';
    protected $description = 'Envía SMS de recordatorio a pacientes con cita próxima';

    public function handle(): void
    {
        if (!SmsService::habilitado()) {
            $this->info('SMS deshabilitado en configuración. Nada enviado.');
            return;
        }

        $cfg   = ConfiguracionController::leerConfig();
        $horas    = max(1, (int) ($cfg['citas']['tiempo_recordatorio'] ?? 24));
        $horasMin = $horas - 1; // ventana de 1 h que coincide con el scheduler hourly

        // Solo citas que caen EXACTAMENTE en la ventana [horas-1, horas] desde ahora.
        // Así cada cita es capturada una única vez por la ejecución horaria.
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
            LEFT JOIN AGENDA a ON a.id_agenda = c.id_agenda
            WHERE c.fecha_hora >  NOW() + INTERVAL {$horasMin} HOUR
              AND c.fecha_hora <= NOW() + INTERVAL {$horas} HOUR
              AND pp.telefono IS NOT NULL
              AND pp.telefono != ''
              AND (c.id_agenda IS NULL OR a.estado = 'ocupado')
        ");

        $enviados = 0;
        $omitidos = 0;
        foreach ($citas as $cita) {
            // Caché como segunda línea de defensa (evita doble envío si el scheduler
            // se ejecuta dos veces en la misma hora o si el trigger manual coincide)
            $cacheKey = 'sms_recordatorio_' . $cita->id_cita;
            if (Cache::has($cacheKey)) {
                $omitidos++;
                continue;
            }

            $fechaFmt  = \Carbon\Carbon::parse($cita->fecha_hora)->isoFormat('D [de] MMMM [de] YYYY');
            $horaFmt   = \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i');
            $nombreMed = strtoupper(trim("{$cita->med_nombres} {$cita->med_apellido}"));
            $nombrePac = trim("{$cita->pac_nombres} {$cita->pac_apellido}");
            $mensaje   = SmsService::formatearCita(
                'le recuerda su cita:',
                $cita->especialidad,
                $nombreMed,
                $fechaFmt,
                $horaFmt,
                (int) $cita->id_cita,
                $nombrePac
            );

            if (SmsService::enviar($cita->pac_tel, $mensaje)) {
                $enviados++;
                // Marcar como enviado hasta 2h después de la cita para no reenviar
                Cache::put($cacheKey, true, \Carbon\Carbon::parse($cita->fecha_hora)->addHours(2));
            }
        }

        $this->info("Recordatorios enviados: {$enviados}, omitidos (ya enviados): {$omitidos}");
    }
}
