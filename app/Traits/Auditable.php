<?php

namespace App\Traits;

use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Auditable
 * 
 * Proporciona funcionalidad de auditoría automática para modelos Eloquent
 * en un entorno multi-tenant con stancl/tenancy.
 * 
 * Uso:
 * 1. Agregar el trait al modelo: use Auditable;
 * 2. Definir propiedades auditables en el modelo:
 *    - protected $auditEnabled = true;
 *    - protected $auditOnly = ['field1', 'field2']; // Solo auditar estos campos
 *    - protected $auditExcept = ['updated_at']; // Excluir estos campos
 *    - protected $auditCriticalFields = ['price', 'status']; // Campos críticos que requieren reason
 */
trait Auditable
{
    /**
     * Boot del trait para registrar eventos del modelo
     */
    public static function bootAuditable(): void
    {
        // Created event
        static::created(function (Model $model) {
            if ($model->isAuditEnabled()) {
                $auditService = app(AuditService::class);
                $auditService->log(
                    action: 'created',
                    model: $model,
                    description: self::generateDescription($model, 'created'),
                    newValues: $model->getAuditAttributes(),
                    tags: self::generateTags($model, 'created')
                );
            }
        });

        // Updated event
        static::updated(function (Model $model) {
            if ($model->isAuditEnabled()) {
                $changes = $model->getDirty();
                
                // Filtrar campos según configuración
                $changes = $model->filterAuditChanges($changes);
                
                if (!empty($changes)) {
                    $auditService = app(AuditService::class);
                    
                    // Verificar si hay campos críticos modificados
                    $criticalFields = $model->getAuditCriticalFields();
                    $hasCriticalChanges = !empty(array_intersect_key($changes, array_flip($criticalFields)));
                    
                    if ($hasCriticalChanges) {
                        // Para cambios críticos, se requiere razón (debe venir del contexto)
                        $reason = request()?->input('audit_reason') ?? request()?->header('X-Audit-Reason') ?? 'No especificada';
                        
                        $auditService->logCritical(
                            action: 'updated',
                            model: $model,
                            reason: $reason,
                            description: self::generateDescription($model, 'updated', $changes),
                            oldValues: $model->getOriginalValues($changes),
                            newValues: $changes,
                            tags: self::generateTags($model, 'updated', true)
                        );
                    } else {
                        $auditService->log(
                            action: 'updated',
                            model: $model,
                            description: self::generateDescription($model, 'updated', $changes),
                            oldValues: $model->getOriginalValues($changes),
                            newValues: $changes,
                            tags: self::generateTags($model, 'updated')
                        );
                    }
                }
            }
        });

        // Deleted event
        static::deleted(function (Model $model) {
            if ($model->isAuditEnabled()) {
                $auditService = app(AuditService::class);
                $auditService->log(
                    action: 'deleted',
                    model: $model,
                    description: self::generateDescription($model, 'deleted'),
                    oldValues: $model->toArray(),
                    tags: self::generateTags($model, 'deleted')
                );
            }
        });

        // Restored event (si usa SoftDeletes)
        if (method_exists(static::class, 'restore')) {
            static::restored(function (Model $model) {
                if ($model->isAuditEnabled()) {
                    $auditService = app(AuditService::class);
                    $auditService->log(
                        action: 'restored',
                        model: $model,
                        description: self::generateDescription($model, 'restored'),
                        tags: self::generateTags($model, 'restored')
                    );
                }
            });
        }
    }

    /**
     * Verifica si la auditoría está habilitada para este modelo
     */
    public function isAuditEnabled(): bool
    {
        return property_exists($this, 'auditEnabled') ? $this->auditEnabled : true;
    }

    /**
     * Obtiene los campos a auditar (filtrados según configuración)
     */
    public function getAuditAttributes(): array
    {
        $attributes = $this->toArray();
        
        // Si hay lista blanca (auditOnly), usar solo esos campos
        if (property_exists($this, 'auditOnly') && !empty($this->auditOnly)) {
            $attributes = array_intersect_key($attributes, array_flip($this->auditOnly));
        }
        
        // Excluir campos de auditExcept
        if (property_exists($this, 'auditExcept') && !empty($this->auditExcept)) {
            $attributes = array_diff_key($attributes, array_flip($this->auditExcept));
        }
        
        return $attributes;
    }

    /**
     * Filtra los cambios según la configuración de auditoría
     */
    protected function filterAuditChanges(array $changes): array
    {
        // Si hay lista blanca (auditOnly), usar solo esos campos
        if (property_exists($this, 'auditOnly') && !empty($this->auditOnly)) {
            $changes = array_intersect_key($changes, array_flip($this->auditOnly));
        }
        
        // Excluir campos de auditExcept
        if (property_exists($this, 'auditExcept') && !empty($this->auditExcept)) {
            $changes = array_diff_key($changes, array_flip($this->auditExcept));
        }
        
        return $changes;
    }

    /**
     * Obtiene los valores originales para los campos cambiados
     */
    protected function getOriginalValues(array $changes): array
    {
        $original = $this->getOriginal();
        return array_intersect_key($original, $changes);
    }

    /**
     * Obtiene los campos críticos que requieren justificación
     */
    protected function getAuditCriticalFields(): array
    {
        return property_exists($this, 'auditCriticalFields') ? $this->auditCriticalFields : [];
    }

    /**
     * Genera una descripción legible del evento de auditoría
     */
    protected static function generateDescription(Model $model, string $action, ?array $changes = null): string
    {
        $modelName = class_basename($model);
        $modelId = $model->getKey();
        
        switch ($action) {
            case 'created':
                return "Se creó {$modelName} #{$modelId}";
            
            case 'updated':
                if ($changes) {
                    $fields = implode(', ', array_keys($changes));
                    return "Se actualizó {$modelName} #{$modelId}: {$fields}";
                }
                return "Se actualizó {$modelName} #{$modelId}";
            
            case 'deleted':
                return "Se eliminó {$modelName} #{$modelId}";
            
            case 'restored':
                return "Se restauró {$modelName} #{$modelId}";
            
            default:
                return "{$action} en {$modelName} #{$modelId}";
        }
    }

    /**
     * Genera tags para el registro de auditoría
     */
    protected static function generateTags(Model $model, string $action, bool $isCritical = false): array
    {
        $tags = [
            strtolower(class_basename($model)),
            $action
        ];
        
        if ($isCritical) {
            $tags[] = 'critical';
        }
        
        return $tags;
    }

    /**
     * Método helper para registrar auditoría manual desde el modelo
     */
    public function auditLog(
        string $action,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null,
        ?string $reason = null
    ) {
        $auditService = app(AuditService::class);
        
        if ($reason || $this->isCriticalAction($action)) {
            return $auditService->logCritical(
                action: $action,
                model: $this,
                reason: $reason ?? 'Acción crítica manual',
                description: $description,
                oldValues: $oldValues,
                newValues: $newValues,
                tags: $tags
            );
        }
        
        return $auditService->log(
            action: $action,
            model: $this,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues,
            tags: $tags,
            reason: $reason
        );
    }

    /**
     * Determina si una acción es crítica por defecto
     */
    protected function isCriticalAction(string $action): bool
    {
        $criticalActions = ['deleted', 'restored', 'forceDeleted'];
        return in_array($action, $criticalActions);
    }
}
