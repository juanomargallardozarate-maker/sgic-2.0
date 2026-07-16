<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Heir extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'contract_id',
        'customer_id',
        'is_designated',
        'inheritance_percent',
    ];

    protected $casts = [
        'is_designated' => 'boolean',
        'inheritance_percent' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}