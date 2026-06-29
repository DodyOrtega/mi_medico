<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecordatorioCincoMinutos extends Command
{
    protected $signature   = 'recordatorio:cinco-minutos';
    protected $description = 'Envía WhatsApp a pacientes con cita en 30 y 15 minutos';

    public function handle(): void
    {
        if (!SmsService::habilitado()) {
            return;
        }

        $this->enviarVentana(29, 30, 'sms_30min_', '30 minutos');
        $this->enviarVentana(14, 15, 'sms_15min_', '15 minutos');
    }

    private function enviarVentana(int $desde, int $hasta, string $prefijo, string $etiqueta): void
    {
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
            WHERE c.fecha_hora > NOW() + INTERVAL {$desde} MINUTE
              AND c.fecha_hora <= NOW() + INTERVAL {$hasta} MINUTE
              AND pp.telefono IS NOT NULL AND pp.telefono != ''
              AND (c.id_agenda IS NULL OR a.estado = 'ocupado')
        ");

        foreach ($citas as $cita) {
            $cacheKey = $prefijo . $cita->id_cita;
            if (Cache::has($cacheKey)) {
                continue;
            }

            $hora      = \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i');
            $nombreMed = strtoupper(trim("{$cita->med_nombres} {$cita->med_apellido}"));
            $nombrePac = trim("{$cita->pac_nombres} {$cita->pac_apellido}");

            $mensaje = implode("\n", [
                "⏰ " . strtoupper($nombrePac),
                "Su cita es en {$etiqueta}",
                "",
                "👨‍⚕️ Médico: DR {$nombreMed}",
                "💉 Especialidad: {$cita->especialidad}",
                "⏱️ Hora: {$hora}",
                "",
                $cita->tipo_cita === 'virtual'
                    ? "Por favor prepare su conexión con anticipación."
                    : "Por favor diríjase al consultorio con anticipación.",
                "",
                "No respondas a este número",
            ]);

            if (SmsService::enviar($cita->pac_tel, $mensaje)) {
                Cache::put($cacheKey, true, 3600);
            }
        }
    }
}
