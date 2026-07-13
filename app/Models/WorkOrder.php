<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class WorkOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_type_id',
        'crypt_id',
        'customer_id',
        'contract_id',
        'crew_id',
        'assigned_to_user_id',
        'order_number',
        'scheduled_at',
        'started_at',
        'completed_at',
        'sanitary_validated',
        'death_certificate_url',
        'body_type',
        'coffin_type',
        'coffin_seal_number',
        'judicial_exception',
        'judicial_order_url',
        'judicial_notes',
        'signature_url',
        'signature_hash',
        'signature_ip',
        'signature_at',
        'observations',
        'status',
        'sync_status',
        'offline_id',
        'conflict_notes',
        'created_by_user_id',
        'completed_by_user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sanitary_validated' => 'boolean',
        'judicial_exception' => 'boolean',
        'signature_at' => 'datetime',
    ];

    public function workOrderType(): BelongsTo
    {
        return $this->belongsTo(WorkOrderType::class);
    }

    public function crypt(): BelongsTo
    {
        return $this->belongsTo(Crypt::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(WorkOrderEvidence::class);
    }
}