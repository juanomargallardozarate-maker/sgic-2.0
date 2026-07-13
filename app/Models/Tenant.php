<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'rfc',
        'subdomain',
        'plan',
        'grace_period_years',
        'debt_months_to_block',
        'moratorium_interest_rate',
        'reservation_days',
        'reservation_deposit_percent',
        'maintenance_grace_days',
        'is_active',
        'subscription_ends_at',
    ];

    protected $casts = [
        'grace_period_years' => 'integer',
        'debt_months_to_block' => 'integer',
        'moratorium_interest_rate' => 'decimal:4',
        'reservation_days' => 'integer',
        'reservation_deposit_percent' => 'decimal:2',
        'maintenance_grace_days' => 'integer',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    public function cemetery(): HasOne
    {
        return $this->hasOne(Cemetery::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan', 'code');
    }

    public function subscriptionHistory(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}