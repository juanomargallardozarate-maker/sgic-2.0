<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para procesar reservas expiradas
 * US-3.3: Limpieza automática de reservas expiradas
 */
class ProcessExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando proceso de reservas expiradas...');

        // Obtener reservas activas que han expirado
        $expiredReservations = Reservation::active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredReservations as $reservation) {
            try {
                $reservation->markAsExpired();
                $count++;
                
                Log::info("Reserva #{$reservation->id} marcada como expirada. Cripta {$reservation->crypt->code} liberada.");

                // Aquí se podría enviar una notificación al cliente
                // Notification::send($reservation->customer, new ReservationExpiredNotification($reservation));

            } catch (\Exception $e) {
                Log::error("Error al procesar reserva expirada #{$reservation->id}: " . $e->getMessage());
            }
        }

        Log::info("Proceso completado. {$count} reservas expiradas procesadas.");
    }
}
