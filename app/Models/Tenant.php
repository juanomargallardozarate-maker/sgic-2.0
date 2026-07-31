<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Columnas reales de la tabla tenants (sin columna 'data').
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
     * Casts para las columnas personalizadas.
     */
    protected $casts = [
        'grace_period_years' => 'float',
        'debt_months_to_block' => 'integer',
        'moratorium_interest_rate' => 'float',
        'reservation_days' => 'integer',
        'reservation_deposit_percent' => 'float',
        'maintenance_grace_days' => 'integer',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Retornamos string vacío para deshabilitar la columna 'data'.
     * La firma debe coincidir EXACTAMENTE con el padre (string, no ?string).
     * Esto evita que VirtualColumn intente acceder a una columna inexistente.
     */
    public static function getDataColumn(): string
    {
        return '';
    }

    /**
     * Relación con usuarios del tenant.
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
