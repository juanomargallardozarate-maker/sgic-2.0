<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Beneficiary extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'contract_id',
        'customer_id',
        'beneficiary_customer_id',
        'relationship',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Mutator para permitir usar customer_id en lugar de beneficiary_customer_id
     */
    public function setCustomerIdAttribute($value)
    {
        $this->attributes['beneficiary_customer_id'] = $value;
    }

    /**
     * Accessor para permitir usar customer_id en lugar de beneficiary_customer_id
     */
    public function getCustomerIdAttribute()
    {
        return $this->attributes['beneficiary_customer_id'] ?? null;
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'beneficiary_customer_id');
    }
}