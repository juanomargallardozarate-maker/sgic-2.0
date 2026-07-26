<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Facades\Tenancy;

class AuditService
{
    /**
     * Registra un evento de auditoría (RN-07)
     * 
     * @param string $action Tipo de acción (created, updated, deleted, etc.)
     * @param Model|null $model Modelo relacionado (puede ser null para acciones generales)
     * @param string|null $description Descripción legible del evento
     * @param array|null $oldValues Valores anteriores (para updates)
     * @param array|null $newValues Valores nuevos
     * @param array|null $tags Etiquetas para clasificación
     * @param string|null $reason Motivo del cambio (para operaciones críticas)
     * @return AuditLog
     */
    public function log(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null,
        ?string $reason = null
    ): AuditLog {
        // Obtener tenant_id del contexto de tenancy (prioritario) o fallback al modelo/user
        $tenantId = Tenancy::getTenantId() 
            ?? ($model?->tenant_id ?? auth()->user()?->tenant_id);

        if (!$tenantId) {
            throw new \RuntimeException('No se pudo determinar el tenant_id para el registro de auditoría');
        }

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'description' => $description,
            'reason' => $reason,
            'tags' => $tags,
            'created_at' => now(),
        ]);
    }

    /**
     * Registra una acción crítica que requiere justificación
     */
    public function logCritical(
        string $action,
        ?Model $model = null,
        string $reason,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null
    ): AuditLog {
        $tags = array_merge($tags ?? [], ['critical']);
        
        return $this->log(
            action: $action,
            model: $model,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues,
            tags: $tags,
            reason: $reason
        );
    }

    /**
     * Registra cambios en relaciones pivot
     */
    public function logPivotChange(
        string $action,
        Model $parentModel,
        string $relationName,
        array $pivotData,
        ?string $description = null
    ): AuditLog {
        return $this->log(
            action: $action,
            model: $parentModel,
            description: $description ?? "Cambio en relación {$relationName}",
            newValues: $pivotData,
            tags: ['pivot', $relationName]
        );
    }
}