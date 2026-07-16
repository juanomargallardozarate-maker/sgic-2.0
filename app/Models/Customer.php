<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Customer extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'type',
        'rfc_encrypted',
        'rfc_hash',
        'curp_encrypted',
        'name',
        'email',
        'phone',
        'mobile',
        'address',
        'ine_url',
        'proof_of_address_url',
        'is_deceased',
        'deceased_at',
        'death_certificate_url',
        'heir_declaration_url',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_deceased' => 'boolean',
        'deceased_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function heirs(): HasMany
    {
        return $this->hasMany(Heir::class);
    }
}