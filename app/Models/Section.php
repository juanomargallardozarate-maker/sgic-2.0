<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Section extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // ✅ AGREGADO
        'code',
        'name',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }
}