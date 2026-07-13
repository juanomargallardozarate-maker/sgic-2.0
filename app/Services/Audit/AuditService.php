<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Registra un evento de auditoría (RN-07)
     */
    public function log(
        string $action,
        Model $model,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null
    ): AuditLog {
        return AuditLog::create([
            'tenant_id' => $model->tenant_id ?? auth()->user()?->tenant_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'description' => $description,
            'tags' => $tags,
            'created_at' => now(),
        ]);
    }
}