<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'default_capacity',
        'max_capacity',
    ];

    protected $casts = [
        'default_capacity' => 'integer',
        'max_capacity' => 'integer',
    ];

    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class);
    }
}