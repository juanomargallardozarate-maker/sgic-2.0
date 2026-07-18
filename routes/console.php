<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// Scheduled Tasks - SGIC 2.0
// ==========================================

// US-3.3: Ejecutar diariamente a las 2:00 AM para procesar reservas expiradas
Schedule::command('reservations:process-expired --sync')
    ->dailyAt('02:00')
    ->description('Procesa reservas expiradas y libera criptas');

// Futuro: Se pueden agregar más tareas programadas aquí
// Schedule::command('contracts:check-expiring')->daily();
// Schedule::command('notifications:send-reminders')->hourly();
