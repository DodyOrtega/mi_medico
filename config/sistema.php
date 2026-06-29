<?php

/**
 * config/sistema.php
 *
 * Configuración general del sistema MiMedico. No usa base de datos
 * (decisión deliberada: el esquema clínico actual no debe alterarse
 * con tablas adicionales hasta confirmar con el docente). Los valores
 * se guardan aquí mismo, vía ConfiguracionController.
 *
 * Para que los cambios desde el panel admin persistan entre despliegues,
 * este archivo se sobrescribe directamente (ver ConfiguracionController::update).
 */

return [

    'general' => [
        'nombre_sistema'    => env('SISTEMA_NOMBRE', 'Mi Médico'),
        'version'           => env('SISTEMA_VERSION', '2.1.0'),
        'empresa'           => env('SISTEMA_EMPRESA', 'Fundación Mi Médico'),
        'telefono_contacto' => env('SISTEMA_TELEFONO', '+593 97 932 8112'),
        'email_contacto'    => env('SISTEMA_EMAIL', 'globalhealthsas@gmail.com'),
        'direccion'         => env('SISTEMA_DIRECCION', 'Calle César Chávez y 5ta transversal'),
        'horario_atencion'  => env('SISTEMA_HORARIO', 'Lunes a Viernes: 08:00 - 18:00'),
    ],

    'citas' => [
        'duracion_cita_default' => env('CITAS_DURACION', 30),
        'max_citas_dia'          => env('CITAS_MAX_DIA', 20),
        'recordatorio_cita'      => env('CITAS_RECORDATORIO', true),
        'tiempo_recordatorio'    => env('CITAS_TIEMPO_RECORDATORIO', 24),
    ],

    'notificaciones' => [
        'notif_nueva_cita'      => env('NOTIF_NUEVA_CITA', true),
        'notif_cita_cancelada'  => env('NOTIF_CITA_CANCELADA', true),
        'notif_recordatorio'    => env('NOTIF_RECORDATORIO', true),
        'notif_urgencia'        => env('NOTIF_URGENCIA', true),
        'metodo_notificacion'   => env('NOTIF_METODO', 'email'),
    ],

    'seguridad' => [
        'pass_min_length'  => env('SEGURIDAD_PASS_MIN', 8),
        'session_timeout'  => env('SEGURIDAD_SESSION_TIMEOUT', 30),
        'sesion_unica'      => env('SEGURIDAD_SESION_UNICA', true),
    ],

];