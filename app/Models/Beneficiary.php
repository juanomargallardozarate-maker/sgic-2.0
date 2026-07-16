<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class Beneficiary extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'contract_id',
        'full_name',
        'rfc',
        'curp',
        'email',
        'phone',
        'relationship',
        'priority',
        'inheritance_percentage',
        'id_document_path',
        'is_verified',
    ];

    protected $casts = [
        'priority' => 'integer',
        'inheritance_percentage' => 'decimal:2',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Scope para ordenar por prioridad
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope para beneficiarios verificados
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Obtener nombre completo formateado
     */
    public function getFullNameFormattedAttribute(): string
    {
        return strtoupper($this->full_name);
    }
}