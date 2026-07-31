<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

// Imports necesarios para Stancl Tenancy
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

use App\Models\SubscriptionHistory;
use App\Models\User;
use App\Models\Cemetery;
use App\Models\Domain;

/**
 * Tenant - Modelo principal para gestión de clientes en el sistema SaaS SGIC
 * 
 * Cada tenant representa un cliente independiente con su propia configuración,
 * cementerio(s), usuarios y suscripción.
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    use HasFactory, SoftDeletes;

    /**
     * Indicar que la clave primaria es un UUID (string) y no auto-incremental.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Los atributos que son asignables masivamente.
     * 
     * NOTA: Solo incluir campos que existen REALMENTE en la base de datos.
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
     * Obtener el nombre de la columna de datos personalizados.
     * Sobrescrito para evitar que Stancl use 'data' por defecto.
     * Retornamos string vacío para indicar que NO usamos columna JSON genérica.
     * 
     * @return string
     */
    public static function getDataColumn(): string
    {
        return '';
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
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
     * Relación con los usuarios del tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con el historial de suscripciones del tenant.
     */
    public function subscriptionHistory(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }
}
