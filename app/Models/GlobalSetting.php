<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class GlobalSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['cemetery_id', 'key', 'value', 'description', 'is_active'];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener un valor por su clave para un cementerio específico
     */
    public static function getValue(string $key, int $cemeteryId, $default = 0)
    {
        $setting = self::where('key', $key)
            ->where('cemetery_id', $cemeteryId)
            ->where('is_active', true)
            ->first();
        return $setting ? (float)$setting->value : $default;
    }

    /**
     * Obtener un valor por su clave para el tenant actual (legacy)
     */
    public static function getValueForTenant(string $key, $default = 0)
    {
        $setting = self::where('key', $key)
            ->where('is_active', true)
            ->first();
        return $setting ? (float)$setting->value : $default;
    }

    /**
     * Guardar o actualizar un valor para un cementerio específico
     */
    public static function setValue(string $key, $value, string $description = null, int $cemeteryId = null)
    {
        $data = [
            'key' => $key,
            'value' => $value,
            'description' => $description,
        ];
        
        if ($cemeteryId) {
            $data['cemetery_id'] = $cemeteryId;
        }
        
        return self::updateOrCreate(
            ['key' => $key, 'cemetery_id' => $cemeteryId],
            $data
        );
    }
}
