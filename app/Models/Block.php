<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Block extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // ✅ AGREGADO
        'section_id',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }
}