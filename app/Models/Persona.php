<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Persona extends Authenticatable
{
    protected $table      = 'PERSONAS';
    protected $primaryKey = 'cedula';
    public    $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'cedula', 'nombres', 'apellido1', 'apellido2',
        'correo', 'contrasena', 'telefono', 'estado',
    ];

    protected $hidden = ['contrasena'];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getRol(): string
    {
        if (\DB::table('ADMIN')->where('cedula', $this->cedula)->exists()) {
            return 'admin';
        }

        $pm = \DB::table('PERSONAL_MEDICO')->where('cedula', $this->cedula)->first();
        if ($pm) {
            return $pm->tipo === 'Medico' ? 'medico' : 'paramedico';
        }

        if (\DB::table('PACIENTE')->where('cedula', $this->cedula)->exists()) {
            return 'paciente';
        }

        // Fase 8 — detección por dominio de correo (sin alterar esquema del docente)
        if (str_contains($this->correo ?? '', '@laboratorio')) {
            return 'laboratorio';
        }

        if (str_contains($this->correo ?? '', '@farmacia')) {
            return 'farmacia';
        }

        return 'desconocido';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido1} {$this->apellido2}");
    }
}