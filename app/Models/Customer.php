<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class Customer extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'type',
        'rfc',
        'curp',
        'legal_name',
        'commercial_name',
        'email',
        'phone',
        'mobile',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'country',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
     * Relación con beneficiarios (a través de contratos)
     */
    public function beneficiaries(): HasManyThrough
    {
        return $this->hasManyThrough(Beneficiary::class, Contract::class);
    }

    /**
     * Scope para buscar por RFC
     */
    public function scopeByRfc($query, string $rfc)
    {
        return $query->where('rfc', $rfc);
    }

    /**
     * Scope para buscar por email
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope para búsqueda global
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('legal_name', 'like', "%{$search}%")
              ->orWhere('commercial_name', 'like', "%{$search}%")
              ->orWhere('rfc', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Obtener nombre completo formateado
     */
    public function getFullNameAttribute(): string
    {
        return $this->commercial_name ?? $this->legal_name;
    }

    /**
     * Obtener dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        $address = "{$this->street} #{$this->exterior_number}";
        
        if ($this->interior_number) {
            $address .= " Int. {$this->interior_number}";
        }
        
        $address .= ", {$this->neighborhood}, {$this->city}, {$this->state} C.P. {$this->zip_code}";
        
        return $address;
    }
}