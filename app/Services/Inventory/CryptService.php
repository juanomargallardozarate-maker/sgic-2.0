<?php

namespace App\Services\Inventory;

use App\Models\Crypt;
use App\Models\CryptStatus;
use App\Models\Reservation;
use App\Exceptions\CryptNotAvailableException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CryptService
{
    /**
     * Validar si una cripta puede ser vendida/concedida (RN-01)
     * 
     * @throws CryptNotAvailableException
     */
    public function validateForSale(Crypt $crypt): void
    {
        // Validar estado disponible
        if ($crypt->cryptStatus->code !== 'available') {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} no está disponible para venta. " .
                "Estado actual: {$crypt->cryptStatus->name}"
            );
        }

        // Validar que no esté bloqueada (RN-04)
        if ($crypt->is_blocked) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} está bloqueada: {$crypt->blocked_reason}"
            );
        }

        // Validar capacidad disponible (RN-01)
        if ($crypt->current_occupancy > 0) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} ya tiene ocupación " .
                "({$crypt->current_occupancy}/{$crypt->capacity})"
            );
        }

        // Validar que esté operativa
        if (!$crypt->cryptStatus->is_operational) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} no está operativa para venta"
            );
        }
    }

    /**
     * Reservar una cripta (cambia estado a reserved)
     * 
     * @throws CryptNotAvailableException
     */
    public function reserve(Crypt $crypt, int $customerId, int $days, float $deposit): Reservation
    {
        $this->validateForSale($crypt);

        return DB::transaction(function () use ($crypt, $customerId, $days, $deposit) {
            // Cambiar estado a "reserved"
            $status = CryptStatus::where('code', 'reserved')->firstOrFail();
            $crypt->update(['crypt_status_id' => $status->id]);

            // Crear reserva
            $reservation = Reservation::create([
                'tenant_id' => auth()->user()->tenant_id,
                'crypt_id' => $crypt->id,
                'customer_id' => $customerId,
                'deposit_amount' => $deposit,
                'reserved_at' => now(),
                'expires_at' => now()->addDays($days),
                'status' => 'active',
            ]);

            Log::info("Cripta {$crypt->code} reservada por {$days} días", [
                'crypt_id' => $crypt->id,
                'customer_id' => $customerId,
                'deposit' => $deposit,
            ]);

            return $reservation;
        });
    }

    /**
     * Ocupar cripta (al firmar contrato)
     * 
     * @throws CryptNotAvailableException
     */
    public function occupy(Crypt $crypt): void
    {
        if ($crypt->cryptStatus->code === 'occupied') {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} ya está ocupada"
            );
        }

        DB::transaction(function () use ($crypt) {
            $status = CryptStatus::where('code', 'occupied')->firstOrFail();
            $crypt->update(['crypt_status_id' => $status->id]);

            Log::info("Cripta {$crypt->code} marcada como ocupada", [
                'crypt_id' => $crypt->id,
            ]);
        });
    }

    /**
     * Bloquear cripta por morosidad (RN-04)
     */
    public function blockForDebt(Crypt $crypt, int $monthsOverdue): void
    {
        if ($crypt->is_blocked) {
            return; // Ya está bloqueada
        }

        DB::transaction(function () use ($crypt, $monthsOverdue) {
            $crypt->update([
                'is_blocked' => true,
                'blocked_reason' => "Morosidad superior a {$monthsOverdue} meses",
                'blocked_at' => now(),
                'blocked_by_user_id' => null, // Sistema
            ]);

            // Cambiar estado a "blocked_debt"
            $status = CryptStatus::where('code', 'blocked_debt')->firstOrFail();
            $crypt->update(['crypt_status_id' => $status->id]);

            Log::warning("Cripta {$crypt->code} bloqueada por morosidad ({$monthsOverdue} meses)", [
                'crypt_id' => $crypt->id,
                'months_overdue' => $monthsOverdue,
            ]);
        });
    }

    /**
     * Desbloquear cripta al liquidar adeudo (RN-04)
     */
    public function unblockAfterPayment(Crypt $crypt): void
    {
        if (!$crypt->is_blocked) {
            return;
        }

        DB::transaction(function () use ($crypt) {
            // Determinar estado correcto según contrato activo
            $hasActiveContract = $crypt->contracts()
                ->where('status', 'active')
                ->exists();

            $newStatusCode = $hasActiveContract ? 'occupied' : 'available';
            $status = CryptStatus::where('code', $newStatusCode)->firstOrFail();

            $crypt->update([
                'is_blocked' => false,
                'blocked_reason' => null,
                'blocked_at' => null,
                'blocked_by_user_id' => null,
                'crypt_status_id' => $status->id,
            ]);

            Log::info("Cripta {$crypt->code} desbloqueada tras pago. Nuevo estado: {$newStatusCode}", [
                'crypt_id' => $crypt->id,
                'new_status' => $newStatusCode,
            ]);
        });
    }

    /**
     * Liberar cripta tras proceso de decadencia (RN-03)
     */
    public function releaseAfterDecay(Crypt $crypt): void
    {
        DB::transaction(function () use ($crypt) {
            $status = CryptStatus::where('code', 'available')->firstOrFail();

            $crypt->update([
                'current_occupancy' => 0,
                'is_blocked' => false,
                'blocked_reason' => null,
                'blocked_at' => null,
                'crypt_status_id' => $status->id,
            ]);

            Log::info("Cripta {$crypt->code} liberada tras proceso de decadencia", [
                'crypt_id' => $crypt->id,
            ]);
        });
    }

    /**
     * Validar si una cripta puede ser inhumada (RN-01 + RN-04 + RN-06)
     * 
     * @throws CryptNotAvailableException
     */
    public function canBeInhumed(Crypt $crypt): bool
    {
        // RN-01: Debe tener capacidad disponible
        if ($crypt->available_capacity < 1) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} no tiene capacidad disponible " .
                "({$crypt->current_occupancy}/{$crypt->capacity})"
            );
        }

        // RN-04: No puede estar bloqueada
        if ($crypt->is_blocked) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} está bloqueada: {$crypt->blocked_reason}"
            );
        }

        // RN-01: Debe tener contrato activo (estado occupied o reserved)
        $validStatuses = ['occupied', 'reserved'];
        if (!in_array($crypt->cryptStatus->code, $validStatuses)) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} no tiene contrato activo. " .
                "Estado actual: {$crypt->cryptStatus->name}"
            );
        }

        return true;
    }

    /**
     * Validar si una cripta puede ser exhumada (RN-04)
     * 
     * @throws CryptNotAvailableException
     */
    public function canBeExhumed(Crypt $crypt, bool $hasJudicialOrder = false): bool
    {
        // RN-01: Debe tener ocupación
        if ($crypt->current_occupancy < 1) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} está vacía, no se puede exhumar"
            );
        }

        // RN-04: No se puede exhumar si está bloqueada (salvo orden judicial)
        if ($crypt->is_blocked && !$hasJudicialOrder) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} está bloqueada. " .
                "Se requiere orden judicial para exhumar."
            );
        }

        return true;
    }

    /**
     * Incrementar ocupación de la cripta (RN-01)
     * 
     * @throws \DomainException
     */
    public function incrementOccupancy(Crypt $crypt): void
    {
        if ($crypt->current_occupancy >= $crypt->capacity) {
            throw new \DomainException(
                "La cripta {$crypt->code} ha alcanzado su capacidad máxima " .
                "({$crypt->capacity})"
            );
        }

        $crypt->increment('current_occupancy');

        // Si se llenó, cambiar estado a "occupied" completo
        if ($crypt->current_occupancy === $crypt->capacity) {
            $status = CryptStatus::where('code', 'occupied')->firstOrFail();
            $crypt->update(['crypt_status_id' => $status->id]);
        }

        Log::info("Ocupación incrementada en cripta {$crypt->code}", [
            'crypt_id' => $crypt->id,
            'new_occupancy' => $crypt->current_occupancy,
            'capacity' => $crypt->capacity,
        ]);
    }

    /**
     * Decrementar ocupación de la cripta
     * 
     * @throws \DomainException
     */
    public function decrementOccupancy(Crypt $crypt): void
    {
        if ($crypt->current_occupancy <= 0) {
            throw new \DomainException(
                "La cripta {$crypt->code} ya está vacía"
            );
        }

        $crypt->decrement('current_occupancy');

        // Si se vació completamente, cambiar a "available"
        if ($crypt->current_occupancy === 0) {
            $status = CryptStatus::where('code', 'available')->firstOrFail();
            $crypt->update(['crypt_status_id' => $status->id]);
        }

        Log::info("Ocupación decrementada en cripta {$crypt->code}", [
            'crypt_id' => $crypt->id,
            'new_occupancy' => $crypt->current_occupancy,
        ]);
    }

    /**
     * Cambiar estado de la cripta
     * 
     * @throws \InvalidArgumentException
     */
    public function changeStatus(Crypt $crypt, string $statusCode): void
    {
        $status = CryptStatus::where('code', $statusCode)->first();

        if (!$status) {
            throw new \InvalidArgumentException(
                "Estado de cripta inválido: {$statusCode}"
            );
        }

        $crypt->update(['crypt_status_id' => $status->id]);

        Log::info("Estado de cripta {$crypt->code} cambiado a {$statusCode}", [
            'crypt_id' => $crypt->id,
            'old_status' => $crypt->cryptStatus->code,
            'new_status' => $statusCode,
        ]);
    }

    /**
     * Obtener estadísticas de inventario para dashboard
     */
    public function getInventoryStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $totalCrypts = Crypt::where('tenant_id', $tenantId)->count();
        $availableCrypts = Crypt::where('tenant_id', $tenantId)
            ->whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->count();
        $occupiedCrypts = Crypt::where('tenant_id', $tenantId)
            ->whereHas('cryptStatus', fn($q) => $q->where('code', 'occupied'))
            ->count();
        $blockedCrypts = Crypt::where('tenant_id', $tenantId)
            ->where('is_blocked', true)
            ->count();

        $occupancyRate = $totalCrypts > 0 
            ? round(($occupiedCrypts / $totalCrypts) * 100, 1) 
            : 0;

        return [
            'total' => $totalCrypts,
            'available' => $availableCrypts,
            'occupied' => $occupiedCrypts,
            'blocked' => $blockedCrypts,
            'occupancy_rate' => $occupancyRate,
        ];
    }
}