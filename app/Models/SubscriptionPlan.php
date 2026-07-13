<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'max_users',
        'max_crypts',
        'max_contracts',
        'has_pwa',
        'has_bi_reports',
        'has_api_access',
        'has_priority_support',
        'has_custom_branding',
        'is_active',
        'order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'max_users' => 'integer',
        'max_crypts' => 'integer',
        'max_contracts' => 'integer',
        'has_pwa' => 'boolean',
        'has_bi_reports' => 'boolean',
        'has_api_access' => 'boolean',
        'has_priority_support' => 'boolean',
        'has_custom_branding' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan', 'code');
    }

    public function history(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}