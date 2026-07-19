<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class InterestRate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'months', 'percentage', 'description', 'is_active'];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener la tasa de interés para un número de meses específico del tenant actual
     */
    public static function getRateForMonths(int $months)
    {
        return self::where('months', $months)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtener todas las tasas activas del tenant actual
     */
    public static function getActiveRates()
    {
        return self::where('is_active', true)
            ->orderBy('months')
            ->get();
    }
}
