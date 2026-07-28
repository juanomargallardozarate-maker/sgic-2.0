# Módulo de Auditoría - SGIC 2.0

## Resumen de la Implementación

El módulo de auditoría del sistema SGIC 2.0 ha sido implementado siguiendo las mejores prácticas para entornos multi-tenant con `stancl/tenancy`. Este documento describe los componentes, configuración y uso del sistema.

---

## Componentes Principales

### 1. Modelo `AuditLog` (app/Models/AuditLog.php)

**Propósito:** Representa la entidad de registro de auditoría en la base de datos.

**Características:**
- Tabla inmutable (protegida por triggers de MySQL)
- Sin soft deletes (los logs nunca se eliminan)
- Campos JSON para valores antiguos/nuevos
- Soporte para tags y reason (motivo de operaciones críticas)

**Campos principales:**
- tenant_id: ID del tenant (multi-tenancia)
- user_id: Usuario que realizó la acción
- action: Tipo de acción (created, updated, deleted, etc.)
- model_type: Clase del modelo afectado
- model_id: ID del modelo afectado
- old_values: Valores antes del cambio (JSON)
- new_values: Valores después del cambio (JSON)
- ip_address: IP del usuario
- user_agent: Navegador/dispositivo
- url: URL donde se realizó la acción
- description: Descripción legible
- reason: Motivo (para operaciones críticas)
- tags: Etiquetas para clasificación (JSON)
- created_at: Fecha del evento

---

### 2. Service `AuditService` (app/Services/Audit/AuditService.php)

**Propósito:** Servicio centralizado para registrar eventos de auditoría.

**Métodos públicos:**

#### log()
Registra un evento de auditoría estándar.

#### logCritical()
Registra una acción crítica que requiere justificación.

#### logPivotChange()
Registra cambios en relaciones pivot.

**Características clave:**
- Obtiene automáticamente el tenant_id del contexto de tenancy
- Fallback a model->tenant_id o auth()->user()->tenant_id
- Valida que siempre haya un tenant_id disponible
- Manejo seguro de request() cuando no hay contexto HTTP

---

### 3. Trait `Auditable` (app/Traits/Auditable.php)

**Propósito:** Proporciona auditoría automática para modelos Eloquent.

**Configuración:**
- auditEnabled: bool - Habilita/deshabilita auditoría para el modelo
- auditOnly: array - Lista blanca de campos a auditar
- auditExcept: array - Lista negra de campos a excluir
- auditCriticalFields: array - Campos que requieren justificación (reason)

**Eventos automáticos:**
- created: Registra creación del modelo
- updated: Registra cambios (solo campos modificados)
- deleted: Registra eliminación
- restored: Registra restauración (si usa SoftDeletes)

---

### 4. Seeder `AuditLogSeeder` (database/seeders/AuditLogSeeder.php)

**Propósito:** Generar datos de ejemplo para testing/demostración.

**NOTA IMPORTANTE:** En producción, los registros se generan automáticamente. Este seeder es solo para entornos de desarrollo, testing o demostraciones.

**Uso:** php artisan db:seed --class=AuditLogSeeder

---

## Migraciones

Las siguientes migraciones definen la estructura completa:

1. 2026_07_10_000009_create_audit_logs_table.php - Creación inicial con triggers de inmutabilidad
2. 2026_07_12_202828_add_missing_columns_to_audit_logs_table.php - Columnas adicionales
3. 2026_07_26_000001_add_reason_to_audit_logs_table.php - Campo reason para operaciones críticas

**Triggers MySQL:**
- prevent_audit_update: Previene UPDATE en audit_logs
- prevent_audit_delete: Previene DELETE en audit_logs

Ambos triggers lanzan error SQLSTATE 45000 si se intenta modificar/eliminar registros.

---

## Ejemplos de Uso

### 1. Auditoría Automática (Recomendado)

Agregar el trait Auditable a tus modelos para auditoría automática.

### 2. Auditoría Manual con AuditService

Inyectar AuditService en controladores y usar los métodos log() o logCritical().

### 3. Auditoría desde el Modelo (Helper)

Usar el método auditLog() dentro de métodos del modelo para registros manuales.

---

## Consultas Comunes

- Logs de un tenant específico: AuditLog::where('tenant_id', 1)->latest()->get()
- Logs de un usuario: AuditLog::where('user_id', auth()->id())->get()
- Logs de un modelo específico: AuditLog::where('model_type', 'App\Models\Contract')->where('model_id', 123)->get()
- Acciones críticas: AuditLog::whereJsonContains('tags', 'critical')->get()

---

## Consideraciones de Seguridad

1. **Inmutabilidad:** Los logs no pueden modificarse ni eliminarse (triggers MySQL + protección en el modelo)
2. **Multi-tenancia:** Cada tenant solo ve sus propios logs (aislamiento garantizado)
3. **Trazabilidad completa:** IP, user agent, URL y timestamp de cada acción
4. **Campos críticos:** Requieren justificación explícita

---

## Próximas Mejoras (Roadmap)

- Dashboard de auditoría en el panel de administración
- Exportación de logs a PDF/Excel
- Alertas por acciones críticas
- Retención configurable de logs (archivado)
- Integración con sistemas de SIEM externos

---

## Referencias

- RN-07: Requisito de trazabilidad y auditoría del sistema
- Documentación de stancl/tenancy: https://tenancyforlaravel.com/
