<?php

use Illuminate\Support\Facades\Schedule;

// Recordatorio 5 minutos antes de la cita (corre cada minuto)
Schedule::command('recordatorio:cinco-minutos')->everyMinute();

// Recordatorio del día (según config tiempo_recordatorio)
Schedule::command('sms:recordatorios')->hourly();
