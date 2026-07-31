<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Los únicos atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'id',
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

    /**
     * Las conversiones de atributos.
     */
    protected $casts = [
        'grace_period_years' => 'integer',
        'debt_months_to_block' => 'integer',
        'moratorium_interest_rate' => 'float',
        'reservation_days' => 'integer',
        'reservation_deposit_percent' => 'float',
        'maintenance_grace_days' => 'integer',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Retorna string vacío para deshabilitar la columna 'data' JSON.
     * Debe coincidir exactamente con la firma del padre.
     */
    public static function getDataColumn(): string
    {
        return '';
    }

    /**
     * Relación con los usuarios del tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }

    /**
     * Relación con el historial de suscripciones.
     */
    public function subscriptionHistory(): HasMany
    {
        return $this->hasMany(\App\Models\SubscriptionHistory::class);
    }

    /**
     * Relación con el cementerio (configuración principal).
     */
    public function cemetery(): HasOne
    {
        return $this->hasOne(\App\Models\Cemetery::class);
    }
}
