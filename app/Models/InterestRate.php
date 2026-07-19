<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class InterestRate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['cemetery_id', 'min_months', 'max_months', 'interest_rate', 'is_active'];

    protected $casts = [
        'interest_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'min_months' => 'integer',
        'max_months' => 'integer',
    ];

    /**
     * Obtener la tasa de interés para un número de meses específico de un cementerio
     * Busca el rango donde min_months <= months <= max_months
     */
    public static function getRateForMonths(int $months, int $cemeteryId)
    {
        return self::where('cemetery_id', $cemeteryId)
            ->where('is_active', true)
            ->where('min_months', '<=', $months)
            ->where(function ($query) use ($months) {
                $query->where('max_months', '>=', $months)
                      ->orWhereNull('max_months');
            })
            ->first();
    }

    /**
     * Obtener la tasa de interés para un número de meses específico del tenant actual (legacy)
     */
    public static function getRateForMonthsLegacy(int $months)
    {
        // Legacy: buscar por columna months exacta si existe
        if (static::hasColumn('months')) {
            return self::where('months', $months)
                ->where('is_active', true)
                ->first();
        }
        
        // Nuevo formato con rangos
        return self::where('is_active', true)
            ->where('min_months', '<=', $months)
            ->where(function ($query) use ($months) {
                $query->where('max_months', '>=', $months)
                      ->orWhereNull('max_months');
            })
            ->first();
    }

    /**
     * Obtener todas las tasas activas de un cementerio
     */
    public static function getActiveRates(int $cemeteryId = null)
    {
        $query = self::where('is_active', true);
        
        if ($cemeteryId) {
            $query->where('cemetery_id', $cemeteryId);
        }
        
        return $query->orderBy('min_months')->get();
    }
    
    /**
     * Verificar si una columna existe en la tabla
     */
    private static function hasColumn(string $column): bool
    {
        $model = new static();
        return in_array($column, $model->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($model->getTable()));
    }
}
