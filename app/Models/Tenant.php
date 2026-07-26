<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

// Imports necesarios para Stancl Tenancy
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
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
        'settings',
        'is_active',
        'subscription_ends_at',
    ];

    /**
     * Indicar a Stancl Tenancy qué columnas son explícitas en la base de datos
     * para que NO se guarden en la columna JSON 'data'.
     */
    public static function getCustomColumns(): array
    {
        return [
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
            'settings',
            'is_active',
            'subscription_ends_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'grace_period_years' => 'integer',
        'debt_months_to_block' => 'integer',
        'moratorium_interest_rate' => 'decimal:4',
        'reservation_days' => 'integer',
        'reservation_deposit_percent' => 'decimal:2',
        'maintenance_grace_days' => 'integer',
        'settings' => 'array',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * MÉTODO CRÍTICO AGREGADO: Requerido por stancl/tenancy para identificar la clave del tenant.
     * Esto soluciona el error: Call to undefined method App\Models\Tenant::getTenantKey()
     */
    public function getTenantKey(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Relación con los dominios del tenant.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Relación con el cementerio principal del tenant.
     */
    public function cemetery(): HasOne
    {
        return $this->hasOne(Cemetery::class);
    }

    /**
     * Scope para obtener solo los tenants activos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Verificar si el tenant tiene una suscripción activa.
     */
    public function isSubscribed(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->subscription_ends_at === null) {
            return true; // Sin fecha de fin significa activo indefinidamente o plan vitalicio
        }

        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Obtener la tasa de interés moratorio formateada o como decimal.
     * (Asumiendo que existía un accessor o método similar en tu lógica original)
     */
    public function getMoratoriumInterestRateAttribute($value)
    {
        return $value;
    }
    
    /**
     * Ejemplo de método helper para obtener días de gracia (si existía en tu lógica).
     */
    public function getGracePeriodDaysAttribute(): int
    {
        return $this->grace_period_years * 365;
    }
}