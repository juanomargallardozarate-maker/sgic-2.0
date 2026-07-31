<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

// Imports necesarios para Stancl Tenancy
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Models\User;
use App\Models\Crypt;
use App\Models\Cemetery;
use App\Models\Domain;

/**
 * Tenant - Modelo principal para gestión de clientes en el sistema SaaS SGIC
 * 
 * Cada tenant representa un cliente independiente con su propia configuración,
 * cementerio(s), usuarios y suscripción.
 * 
 * ============================================================================
 * ⚠️ ADVERTENCIA CRÍTICA SOBRE CAMPOS VIRTUALES
 * ============================================================================
 * El campo 'subscription_months' es un campo VIRTUAL (dummy) que SOLO se usa
 * en formularios para calcular la fecha de fin de suscripción (subscription_ends_at).
 * 
 * ESTE CAMPO NO DEBE ESTAR EN EL ARRAY $fillable BAJO NINGUNA CIRCUNSTANCIA.
 * Si se agrega accidentalmente, causará errores de base de datos ya que no
 * existe como columna real en la tabla 'tenants'.
 * ============================================================================
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
     * Campos virtuales como 'subscription_months' NO deben estar aquí.
     */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'subscription_plan_id',
        'status',
        'grace_period_years',
        'debt_months_to_block',
        'moratorium_interest_rate',
        'reservation_days',
        'reservation_deposit_percent',
        'maintenance_grace_days',
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
            'slug',
            'subscription_plan_id',
            'status',
            'grace_period_years',
            'debt_months_to_block',
            'moratorium_interest_rate',
            'reservation_days',
            'reservation_deposit_percent',
            'maintenance_grace_days',
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
     * MÉTODO CRÍTICO: Requerido por stancl/tenancy para identificar la clave del tenant.
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
     * Relación con los usuarios del tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con las criptas del tenant.
     */
    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class, 'tenant_id');
    }

    /**
     * Relación con el plan de suscripción actual del tenant.
     * La columna 'subscription_plan_id' referencia al ID del plan.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Relación con el historial de suscripciones del tenant.
     */
    public function subscriptionHistory(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    /**
     * Scope para obtener solo los tenants activos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para obtener tenants por estado específico.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Verificar si el tenant tiene una suscripción activa.
     * 
     * @return bool
     */
    public function isSubscribed(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Si no hay fecha de fin, considerar como activo indefinidamente
        if (!$this->hasAttribute('subscription_ends_at') || $this->subscription_ends_at === null) {
            return true;
        }

        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Obtener días restantes hasta el vencimiento de la suscripción.
     * 
     * @return int|null Retorna null si no hay fecha de vencimiento
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->subscription_ends_at) {
            return null;
        }

        return now()->diffInDays($this->subscription_ends_at, false);
    }

    /**
     * Verificar si la suscripción está por vencer (próximos 30 días).
     * 
     * @return bool
     */
    public function isExpiringSoon(): bool
    {
        $days = $this->getDaysUntilExpiry();
        
        if ($days === null) {
            return false;
        }

        return $days <= 30 && $days > 0;
    }

    /**
     * Obtener la tasa de interés moratorio formateada como porcentaje.
     * 
     * @return string
     */
    public function getMoratoriumInterestRatePercentAttribute(): string
    {
        return number_format($this->moratorium_interest_rate * 100, 2) . '%';
    }

    /**
     * Acceso defensivo para obtener el nombre del plan.
     * Evita errores si la relación no está cargada o es null.
     * 
     * @return string
     */
    public function getPlanNameAttribute(): string
    {
        return $this->subscriptionPlan?->name ?? 'Sin plan asignado';
    }

    /**
     * Acceso defensivo para obtener el precio mensual del plan.
     * 
     * @return float
     */
    public function getMonthlyPriceAttribute(): float
    {
        return $this->subscriptionPlan?->monthly_price ?? 0.00;
    }
}