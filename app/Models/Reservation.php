<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Carbon\Carbon;

class Reservation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'crypt_id',
        'customer_id',
        'deposit_amount',
        'reserved_at',
        'expires_at',
        'status',
        'contract_id',
        'notes',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Constants for status
    const STATUS_ACTIVE = 'active';
    const STATUS_CONVERTED = 'converted';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public function crypt()
    {
        return $this->belongsTo(Crypt::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Check if reservation is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if reservation has expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_ACTIVE);
    }

    /**
     * Check if reservation can be converted to contract
     */
    public function canBeConverted(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->isActive() && 
               $this->crypt && 
               $this->crypt->isAvailableForSale;
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expires_at) return null;
        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Mark reservation as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
        
        // Liberar la cripta si estaba reservada
        if ($this->crypt) {
            $this->crypt->changeStatus('available');
        }
    }

    /**
     * Cancel reservation
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason ? ($this->notes ?? '') . "\nCancelado: " . $reason : $this->notes,
        ]);

        // Liberar la cripta
        if ($this->crypt) {
            $this->crypt->changeStatus('available');
        }
    }

    /**
     * Convert reservation to contract
     * RN-01: Solo criptas disponibles pueden convertirse
     */
    public function convertToContract(array $contractData): Contract
    {
        if (!$this->canBeConverted()) {
            throw new \Exception('La reserva no puede ser convertida a contrato.');
        }

        // Validar que la cripta siga disponible
        if (!$this->crypt->isAvailableForSale) {
            throw new \Exception('La cripta ya no está disponible.');
        }

        return \DB::transaction(function () use ($contractData) {
            // Crear contrato
            $contract = Contract::create([
                'customer_id' => $this->customer_id,
                'crypt_id' => $this->crypt_id,
                'contract_type_id' => $contractData['contract_type_id'],
                'contract_number' => $this->generateContractNumber(),
                'start_date' => $contractData['start_date'] ?? now(),
                'end_date' => $contractData['end_date'] ?? null,
                'price' => $contractData['price'],
                'annual_maintenance_fee' => $contractData['annual_maintenance_fee'] ?? 0,
                'payment_type' => $contractData['payment_type'] ?? 'cash',
                'installments_count' => $contractData['installments_count'] ?? null,
                'status' => 'draft',
                'notes' => $this->notes ? "Convertido desde reserva ID: {$this->id}. " . $this->notes : "Convertido desde reserva ID: {$this->id}",
                'created_by_user_id' => auth()->id(),
            ]);

            // Actualizar reserva
            $this->update([
                'status' => self::STATUS_CONVERTED,
                'contract_id' => $contract->id,
            ]);

            // Marcar cripta como ocupada temporalmente (en proceso de contrato)
            $this->crypt->changeStatus('occupied');

            return $contract;
        });
    }

    /**
     * Extend reservation expiration
     */
    public function extendExpiration(int $days): void
    {
        if (!$this->isActive()) {
            throw new \Exception('Solo las reservas activas pueden extenderse.');
        }

        $newExpiresAt = $this->expires_at 
            ? $this->expires_at->addDays($days)
            : now()->addDays($days);

        $this->update(['expires_at' => $newExpiresAt]);
    }

    /**
     * Generate contract number
     */
    protected function generateContractNumber(): string
    {
        $year = now()->format('Y');
        $last = Contract::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereYear('created_at', $year)
                    ->max('id') ?? 0;
        return sprintf('CTR-%s-%05d', $year, $last + 1);
    }

    /**
     * Scope: Active reservations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Expiring soon (default: 7 days)
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->active()
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: Expired reservations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function ($q) {
                        $q->where('status', self::STATUS_ACTIVE)
                          ->whereNotNull('expires_at')
                          ->where('expires_at', '<', now());
                    });
    }

    /**
     * Scope: Converted reservations
     */
    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }
}