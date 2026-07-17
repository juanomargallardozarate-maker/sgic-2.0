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

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function beneficiaryCustomer()
    {
        return $this->belongsTo(Customer::class, 'beneficiary_customer_id');
    }
}