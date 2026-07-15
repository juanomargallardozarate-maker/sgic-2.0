<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Crypt extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // ✅ AGREGADO
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

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function cryptType(): BelongsTo
    {
        return $this->belongsTo(CryptType::class);
    }

    public function cryptStatus(): BelongsTo
    {
        return $this->belongsTo(CryptStatus::class);
    }

    public function getFullCodeAttribute(): string
    {
        // Manejo seguro por si las relaciones no están cargadas
        $sectionCode = $this->level?->block?->section?->code ?? 'SEC';
        $blockCode = $this->level?->block?->code ?? 'BLK';
        $levelCode = $this->level?->code ?? 'LVL';
        
        return "{$sectionCode}-{$blockCode}-{$levelCode}-{$this->code}";
    }

    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }

    public function getIsAvailableForSaleAttribute(): bool
    {
        return ($this->cryptStatus?->is_available_for_sale ?? false) && !$this->is_blocked;
    }
}