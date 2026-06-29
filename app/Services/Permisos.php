<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class Permisos
{
    const MEDICO_MODULOS = [
        'pacientes'  => 'Mis Pacientes',
        'agenda'     => 'Agenda',
        'historial'  => 'Historial Clínico',
        'citas'      => 'Mis Citas',
        'video'      => 'Videollamadas',
    ];

    const PARA_MODULOS = [
        'pacientes'   => 'Mis Pacientes',
        'citas'       => 'Agendar Cita',
        'atencion'    => 'Primera Atención',
        'emergencias' => 'Emergencias',
        'video'       => 'Videollamadas',
    ];

    public static function tienePermiso(string $cedula, string $modulo): bool
    {
        try {
            if (!Storage::exists('permisos.json')) return true;
            $permisos = json_decode(Storage::get('permisos.json'), true) ?? [];
            if (!isset($permisos[$cedula])) return true;
            return (bool) ($permisos[$cedula][$modulo] ?? true);
        } catch (\Throwable) {
            return true;
        }
    }

    public static function obtenerPermisos(string $cedula): array
    {
        try {
            if (!Storage::exists('permisos.json')) return [];
            $permisos = json_decode(Storage::get('permisos.json'), true) ?? [];
            return $permisos[$cedula] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public static function guardar(string $cedula, array $modulos): void
    {
        try {
            $todos = [];
            if (Storage::exists('permisos.json')) {
                $todos = json_decode(Storage::get('permisos.json'), true) ?? [];
            }
            $todos[$cedula] = $modulos;
            Storage::put('permisos.json', json_encode($todos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable) {}
    }
}
