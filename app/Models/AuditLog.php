<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // ⚠️ NO usar SoftDeletes (tabla inmutable)
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'description',
        'tags',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }

    // ⚠️ Previene UPDATE (doble seguridad, además del trigger MySQL)
    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new \DomainException('Audit logs are immutable. UPDATE is not allowed.');
        });

        static::deleting(function () {
            throw new \DomainException('Audit logs are immutable. DELETE is not allowed.');
        });
    }
}