<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamenService
{
    private const BASE   = 'examenes';
    private const INDEX  = '_index.json';
    private const MAX_MB = 10;
    private const TIPOS  = ['pdf','jpg','jpeg','png','webp'];

    // ── Guardar archivo y actualizar índice ──────────────────────────
    public static function guardar(string $cedula, $file, string $descripcion = ''): array
    {
        $ext      = strtolower($file->getClientOriginalExtension());
        $id       = Str::uuid()->toString();
        $nombre   = $id . '.' . $ext;
        $ruta     = self::BASE . '/' . $cedula . '/' . $nombre;

        Storage::put($ruta, file_get_contents($file->getRealPath()));

        $entrada = [
            'id'              => $id,
            'nombre_original' => $file->getClientOriginalName(),
            'nombre_guardado' => $nombre,
            'descripcion'     => trim($descripcion) ?: $file->getClientOriginalName(),
            'extension'       => $ext,
            'mime'            => $file->getMimeType(),
            'tamanio'         => $file->getSize(),
            'fecha'           => now()->format('Y-m-d H:i:s'),
        ];

        $indice   = self::leerIndice($cedula);
        $indice[] = $entrada;
        self::guardarIndice($cedula, $indice);

        return $entrada;
    }

    // ── Listar archivos de un paciente ───────────────────────────────
    public static function listar(string $cedula): array
    {
        return array_reverse(self::leerIndice($cedula));
    }

    // ── Obtener un archivo por ID ────────────────────────────────────
    public static function obtener(string $cedula, string $id): ?array
    {
        $indice = self::leerIndice($cedula);
        foreach ($indice as $entry) {
            if ($entry['id'] === $id) return $entry;
        }
        return null;
    }

    // ── Ruta de Storage para descargar ───────────────────────────────
    public static function rutaStorage(string $cedula, string $nombreGuardado): string
    {
        return self::BASE . '/' . $cedula . '/' . $nombreGuardado;
    }

    // ── Eliminar archivo ─────────────────────────────────────────────
    public static function eliminar(string $cedula, string $id): bool
    {
        $indice = self::leerIndice($cedula);
        $nuevo  = [];
        $borrado = false;

        foreach ($indice as $entry) {
            if ($entry['id'] === $id) {
                Storage::delete(self::rutaStorage($cedula, $entry['nombre_guardado']));
                $borrado = true;
            } else {
                $nuevo[] = $entry;
            }
        }

        self::guardarIndice($cedula, $nuevo);
        return $borrado;
    }

    // ── Resumen general para el admin ────────────────────────────────
    public static function resumenAdmin(): array
    {
        $dirs     = Storage::directories(self::BASE);
        $pacientes = [];
        $totalArchivos = 0;
        $totalBytes    = 0;
        $recientes     = [];

        foreach ($dirs as $dir) {
            $cedula  = basename($dir);
            $indice  = self::leerIndice($cedula);
            if (empty($indice)) continue;

            $bytes = array_sum(array_column($indice, 'tamanio'));
            $totalArchivos += count($indice);
            $totalBytes    += $bytes;

            $pacientes[] = [
                'cedula'    => $cedula,
                'archivos'  => count($indice),
                'tamanio'   => $bytes,
                'ultimo'    => $indice[count($indice) - 1]['fecha'],
            ];

            foreach ($indice as $e) {
                $recientes[] = array_merge($e, ['cedula' => $cedula]);
            }
        }

        usort($recientes, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

        return [
            'total_archivos'  => $totalArchivos,
            'total_bytes'     => $totalBytes,
            'total_pacientes' => count($pacientes),
            'pacientes'       => $pacientes,
            'recientes'       => array_slice($recientes, 0, 20),
        ];
    }

    public static function maxMb(): int { return self::MAX_MB; }
    public static function tiposPermitidos(): array { return self::TIPOS; }

    // ── Helpers privados ─────────────────────────────────────────────
    private static function rutaIndice(string $cedula): string
    {
        return self::BASE . '/' . $cedula . '/' . self::INDEX;
    }

    private static function leerIndice(string $cedula): array
    {
        $ruta = self::rutaIndice($cedula);
        if (!Storage::exists($ruta)) return [];
        return json_decode(Storage::get($ruta), true) ?? [];
    }

    private static function guardarIndice(string $cedula, array $indice): void
    {
        Storage::put(self::rutaIndice($cedula), json_encode($indice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
