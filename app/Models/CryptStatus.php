<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'color',
        'is_available_for_sale',
        'is_operational',
        'order',
    ];

    protected $casts = [
        'is_available_for_sale' => 'boolean',
        'is_operational' => 'boolean',
        'order' => 'integer',
    ];

    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class);
    }
}