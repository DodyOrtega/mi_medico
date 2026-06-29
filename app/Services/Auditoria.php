<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Auditoria
{
    public static function registrar(string $accion, string $detalle = ''): void
    {
        try {
            $hoy  = now()->format('Y-m-d');
            $ruta = "auditoria/{$hoy}.json";

            $entradas = [];
            if (Storage::exists($ruta)) {
                $entradas = json_decode(Storage::get($ruta), true) ?? [];
            }

            $entradas[] = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'cedula'    => Session::get('usuario_cedula', 'sistema'),
                'nombre'    => Session::get('usuario_nombre', 'Sistema'),
                'rol'       => Session::get('usuario_rol', 'sistema'),
                'accion'    => $accion,
                'detalle'   => $detalle,
                'ip'        => request()->ip(),
            ];

            Storage::put($ruta, json_encode($entradas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable) {
            // El fallo de auditoría no debe interrumpir la operación principal
        }
    }

    public static function leerDia(string $fecha): array
    {
        try {
            $ruta = "auditoria/{$fecha}.json";
            if (!Storage::exists($ruta)) return [];
            return array_reverse(json_decode(Storage::get($ruta), true) ?? []);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function diasDisponibles(): array
    {
        try {
            $archivos = Storage::files('auditoria');
            $dias = [];
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo, '.json');
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $nombre)) {
                    $dias[] = $nombre;
                }
            }
            rsort($dias);
            return $dias;
        } catch (\Throwable) {
            return [];
        }
    }
}
