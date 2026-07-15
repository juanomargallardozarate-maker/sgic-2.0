<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Level extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // ✅ AGREGADO
        'block_id',
        'code',
        'name',
        'height_order',
        'is_active',
    ];

    protected $casts = [
        'height_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class);
    }
}