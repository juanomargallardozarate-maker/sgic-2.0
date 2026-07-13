<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'requires_sanitary_validation',
        'requires_death_certificate',
        'requires_family_signature',
        'min_photos',
        'max_photos',
        'description',
    ];

    protected $casts = [
        'requires_sanitary_validation' => 'boolean',
        'requires_death_certificate' => 'boolean',
        'requires_family_signature' => 'boolean',
        'min_photos' => 'integer',
        'max_photos' => 'integer',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}