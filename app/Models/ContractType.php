<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class ContractType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'modality',
        'duration_years',
        'is_renewable',
        'base_price',
        'maintenance_fee_annual',
        'grace_period_days',
        'terms_and_conditions',
        'is_active',
    ];

    protected $casts = [
        'duration_years' => 'integer',
        'is_renewable' => 'boolean',
        'base_price' => 'decimal:2',
        'maintenance_fee_annual' => 'decimal:2',
        'grace_period_days' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con contratos
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Scope para tipos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para tipos perpetuos
     */
    public function scopePerpetual($query)
    {
        return $query->where('modality', 'perpetual');
    }

    /**
     * Scope para tipos temporales
     */
    public function scopeTemporary($query)
    {
        return $query->where('modality', 'temporary');
    }

    /**
     * Obtener modalidad formateada
     */
    public function getModalityFormattedAttribute(): string
    {
        $modalities = [
            'perpetual' => 'Perpetuo',
            'temporary' => 'Temporal',
        ];
        
        return $modalities[$this->modality] ?? $this->modality;
    }

    /**
     * Verificar si es temporal
     */
    public function getIsTemporaryAttribute(): bool
    {
        return $this->modality === 'temporary';
    }

    /**
     * Verificar si es perpetuo
     */
    public function getIsPerpetualAttribute(): bool
    {
        return $this->modality === 'perpetual';
    }
}