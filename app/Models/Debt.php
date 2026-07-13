<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class Debt extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'customer_id',
        'crypt_id',
        'debt_type',
        'original_amount',
        'interest_amount',
        'total_amount',
        'paid_amount',
        'pending_amount',
        'due_date',
        'grace_period_ends_at',
        'paid_at',
        'status',
        'blocked_at',
        'days_overdue',
        'notes',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'due_date' => 'date',
        'grace_period_ends_at' => 'date',
        'paid_at' => 'datetime',
        'blocked_at' => 'datetime',
        'days_overdue' => 'integer',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function crypt(): BelongsTo
    {
        return $this->belongsTo(Crypt::class);
    }
}