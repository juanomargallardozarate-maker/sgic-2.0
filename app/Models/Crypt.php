<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Crypt extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'level_id',
        'crypt_type_id',
        'crypt_status_id',
        'code',
        'capacity',
        'current_occupancy',
        'price',
        'dimensions',
        'door_type',
        'notes',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'blocked_by_user_id',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'price' => 'decimal:2',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    // Relaciones
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function cryptType(): BelongsTo
    {
        return $this->belongsTo(CryptType::class, 'crypt_type_id');
    }

    public function cryptStatus(): BelongsTo
    {
        return $this->belongsTo(CryptStatus::class, 'crypt_status_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContract()
    {
        return $this->hasMany(Contract::class)->where('status', 'active');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // Accessors
    public function getFullCodeAttribute(): string
    {
        return "{$this->level->block->section->code}-{$this->level->block->code}-{$this->level->code}-{$this->code}";
    }

    public function getIsAvailableForSaleAttribute(): bool
    {
        return $this->cryptStatus->is_available_for_sale && !$this->is_blocked;
    }

    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }

    // Scopes (RN-01, RN-04)
    public function scopeAvailable($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
                     ->where('is_blocked', false);
    }

    public function scopeOccupied($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code', 'occupied'));
    }

    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    public function scopeDecaying($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code', 'decaying'));
    }

    // Métodos de negocio
    public function canBeOccupied(): bool
    {
        return $this->is_available_for_sale &&
               $this->available_capacity > 0 &&
               !$this->is_blocked;
    }

    public function block(string $reason, ?int $userId = null): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
            'blocked_by_user_id' => $userId,
        ]);
    }

    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
            'blocked_by_user_id' => null,
        ]);
    }

    public function incrementOccupancy(): void
    {
        if ($this->current_occupancy >= $this->capacity) {
            throw new \DomainException('La cripta ha alcanzado su capacidad máxima.');
        }
        $this->increment('current_occupancy');
        
        if ($this->current_occupancy === $this->capacity) {
            $this->changeStatus('occupied');
        }
    }

    public function decrementOccupancy(): void
    {
        if ($this->current_occupancy <= 0) {
            throw new \DomainException('La cripta ya está vacía.');
        }
        $this->decrement('current_occupancy');
        
        if ($this->current_occupancy === 0) {
            $this->changeStatus('available');
        }
    }

    public function changeStatus(string $statusCode): void
    {
        $status = CryptStatus::where('code', $statusCode)->firstOrFail();
        $this->update(['crypt_status_id' => $status->id]);
    }
}