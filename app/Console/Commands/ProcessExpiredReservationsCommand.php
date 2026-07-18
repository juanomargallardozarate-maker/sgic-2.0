<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExpiredReservations;
use Illuminate\Console\Command;

/**
 * Comando para procesar reservas expiradas manualmente o por schedule
 * US-3.3: Limpieza automática de reservas expiradas
 */
class ProcessExpiredReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:process-expired {--sync : Procesar en modo síncrono (sin cola)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa las reservas expiradas y libera las criptas asociadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando procesamiento de reservas expiradas...');

        if ($this->option('sync')) {
            // Ejecutar sincrónicamente
            $job = new ProcessExpiredReservations();
            $job->handle();
            $this->info('Proceso completado exitosamente.');
        } else {
            // Enviar a la cola
            ProcessExpiredReservations::dispatch();
            $this->info('Job enviado a la cola para procesamiento asíncrono.');
        }

        return 0;
    }
}
