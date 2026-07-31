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
     * Retornamos string vacío para deshabilitar la columna 'data'.
     * La firma debe coincidir EXACTAMENTE con el padre (string, no ?string).
     */
    public static function getDataColumn(): string
    {
        return '';
    }

    /**
     * Opcional: Si necesitas casts específicos para tus columnas personalizadas.
     * Solo definimos los casts, NO $fillable ni otras propiedades que causan conflicto.
     */
    protected function castAttribute($key, $value)
    {
        return match ($key) {
            'subscription_ends_at' => $this->asDateTime($value),
            'grace_period_years', 'moratorium_interest_rate', 'reservation_deposit_percent' => $this->fromFloat($value),
            'debt_months_to_block', 'reservation_days', 'maintenance_grace_days' => $this->fromInt($value),
            'is_active' => $this->asBoolean($value),
            default => parent::castAttribute($key, $value),
        };
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
