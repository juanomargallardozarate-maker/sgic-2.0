<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class WorkOrderEvidence extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'work_order_id',
        'type',
        'file_url',
        'file_hash',
        'file_size',
        'mime_type',
        'metadata',
        'gps_latitude',
        'gps_longitude',
        'taken_at',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'taken_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}