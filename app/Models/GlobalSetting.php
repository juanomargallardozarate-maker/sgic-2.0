<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class GlobalSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'key', 'value', 'description'];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    /**
     * Obtener un valor por su clave para el tenant actual
     */
    public static function getValue(string $key, $default = 0)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Guardar o actualizar un valor para el tenant actual
     */
    public static function setValue(string $key, $value, string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}
