<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function enviar(string $telefono, string $mensaje): bool
    {
        $numero = self::normalizar($telefono);
        if (!$numero) {
            Log::warning("WhatsApp: número inválido ({$telefono})");
            return false;
        }

        try {
            $resp = Http::timeout(10)
                ->withHeader('X-Api-Key', 'mimedico_waha_2024')
                ->post('http://waha:3000/api/sendText', [
                    'chatId'  => $numero . '@c.us',
                    'text'    => $mensaje,
                    'session' => 'default',
                ]);

            if (!$resp->successful()) {
                Log::warning('WhatsApp: error WAHA — ' . $resp->body());
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp: excepción — ' . $e->getMessage());
            return false;
        }
    }

    public static function formatearCita(
        string $tipoCabecera,
        string $especialidad,
        string $medico,
        string $fecha,
        string $hora,
        int    $idCita = 0,
        string $paciente = ''
    ): string {
        $empresa = 'Mi Médico';

        return implode("\n", [
            "👤 " . strtoupper($paciente),
            "{$empresa}",
            " {$tipoCabecera}",
            "💉 Especialidad: {$especialidad}",
            "👨‍⚕️ Médico: DR {$medico}",
            "🗓️ Fecha: {$fecha}",
            "⏱️ Hora: {$hora}",
            " ",
            "Conectarse 5 min antes",
            "",
            "No respondas a este número",
        ]);
    }

    // Normaliza a formato internacional Ecuador (sin +)
    private static function normalizar(string $tel): ?string
    {
        $d = preg_replace('/\D/', '', $tel);

        if (strlen($d) === 10 && str_starts_with($d, '0')) {
            return '593' . substr($d, 1);
        }
        if (strlen($d) === 9) {
            return '593' . $d;
        }
        if (strlen($d) >= 11 && str_starts_with($d, '593')) {
            return $d;
        }
        return null;
    }

    public static function habilitado(): bool
    {
        $metodo = \App\Http\Controllers\Admin\ConfiguracionController::leerConfig()['notificaciones']['metodo_notificacion'] ?? 'email';
        return in_array($metodo, ['sms', 'ambos']);
    }
}
