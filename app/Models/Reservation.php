<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

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
}