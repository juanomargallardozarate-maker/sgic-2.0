```
SDD — SISTEMA DE GESTIÓN INTEGRAL DE CRIPTAS (SGIC) 2.0
```

```
**SOFTWARE DESIGN DOCUMENT**
```

```
**Versión:** 1.0 | **Fecha:** 09 de Julio, 2026 | **Autor:** Arquitecto de
Software
**Stack:** PHP Laravel 11 + MySQL 8 + Blade + Livewire 3 + Tailwind CSS
**Arquitectura:** SaaS Multi-tenant (Single DB + `tenant_id` + Global Scopes)
**Referencia:** PRD v1.0 (Fase 3)
```

```
---
```

```
## 📑 ÍNDICE
```

`1. Introducción` 

`2. Arquitectura C4 Model` 

`3. Stack Tecnológico Detallado` 

`4. Diseño de Base de Datos (ER Diagram + Migraciones)` 

`5. Modelos Eloquent` 

`6. Servicios (Lógica de Negocio)` 

`7. Controladores y Rutas` 

`8. APIs Principales` 

`9. Estrategia de Multi-tenancy` 

`10. Seguridad` 

`11. Estrategia de Testing` 

`12. Despliegue y Operación` 

`13. Apéndices` 

```
---
```

```
## 1. 📑 INTRODUCCIÓN
```

```
### 1.1 Propósito
```

```
Este documento **Software Design Document (SDD)** define la arquitectura
técnica, el diseño de base de datos, los componentes de software y las
decisiones de implementación para el **Sistema de Gestión Integral de Criptas
(SGIC) 2.0**. Sirve como blueprint definitivo para la Fase 5 (Desarrollo) y
garantiza que el equipo de 2 desarrolladores tenga una guía clara, consistente y
ejecutable.
```

```
### 1.2 Alcance
```

```
El SDD cubre:
```

- `📑 Arquitectura de alto nivel (C4 Model: Context, Container, Component)` 

- `📑 Stack tecnológico con versiones y justificaciones` 

- `📑 Diseño de base de datos (ER Diagram orientado a las 7 Reglas de Negocio)` 

- `📑 Modelos Eloquent con relaciones, scopes y casts` 

- `📑 Migraciones MySQL con estructura completa` 

- `📑 Servicios con lógica de negocio encapsulada` 

- `📑 APIs REST + endpoints de sincronización PWA` 

- `📑 Estrategia de multi-tenancy (Single DB + Global Scopes)` 

- `📑 Seguridad (autenticación, autorización, cifrado, auditoría)` 

- `📑 Estrategia de testing (Unit, Feature, E2E)` 

- `📑 Despliegue y operación (Docker + Forge + monitoreo)` 

```
### 1.3 Referencias
```

```
| Documento | Versión | Propósito |
```

- `|-----------|---------|-----------|` 

- `| PRD SGIC 2.0 | 1.0 | Requisitos funcionales y no funcionales |` 

- `| Documento Base SGIC | 2.0 | Descripción original del sistema |` 

- `| Laravel Documentation | 11.x | Framework base |` 

- `| NOM-013-SSA2-1994 | Vigente | Disposición de cadáveres (México) |` 

- `| NOM-133-SSA1-2012 | Vigente | Servicios funerarios |` 

```
| SAT CFDI 4.0 | Vigente | Facturación electrónica México |
```

```
| LFPDPPP | Vigente | Protección de datos personales |
```

```
| Spatie Laravel Permission | 6.x | RBAC |
```

```
---
```

## `## 2. ARQUITECTURA C4 MODEL` 🏗️� 

```
### 2.1 Nivel 1: System Context (Contexto del Sistema)
```

```
```
```

**==> picture [433 x 294] intentionally omitted <==**

**----- Start of picture text -----**<br>
                    ┌─────────────────────────────────────────┐<br>                    │      USUARIOS DEL SISTEMA               │<br>                    ├─────────────────────────────────────────┤<br>                    │ • SuperAdmin SaaS (proveedor)           │<br>                    │ • Admin Cementerio (dueño)              │<br>                    │ • Administrativo (ventas/cobranza)      │<br>                    │ • Operativo Campo (sepulturero)         │<br>                    │ • Familia / Titular (portal V1.0)       │<br>                    └──────────────┬──────────────────────────┘<br>                                   │ usa<br>                                   ▼<br>┌──────────────────────────────────────────────────────────────────────┐<br>│                    SGIC 2.0 (SaaS Platform)                          │<br>│    Plataforma SaaS para gestión integral de cementerios en México    │<br>│    Digitaliza inventario, contratos, finanzas, operaciones y BI      │<br>└──────────┬────────────┬────────────┬────────────┬────────────────────┘<br>           │            │            │            │<br>           ▼            ▼            ▼            ▼<br>    ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐<br>    │   SAT    │  │Pasarelas │  │  SendGrid│  │  Twilio  │<br>    │ (CFDI)   │  │  Pago    │  │ (Email)  │  │(WhatsApp)│<br>    │México    │  │MP/Stripe │  │          │  │          │<br>    └──────────┘  └──────────┘  └──────────┘  └──────────┘<br>         ▲              ▲             ▲             ▲<br>         │              │             │             │<br>    Timbra CFDI    Procesa pagos  Envía emails  Envía WhatsApp<br>```<br>**----- End of picture text -----**<br>


```
**Actores externos:**
```

```
- **Usuarios internos**: 5 roles (SuperAdmin, AdminCementerio, Administrativo,
Operativo, Consulta)
```

```
- **Familias**: Portal de autogestión (V1.0)
```

```
- **SAT México**: Timbrado de CFDI 4.0 vía PAC
```

```
- **Pasarelas de pago**: MercadoPago, Stripe, PayPal, Culqi
```

```
- **SendGrid**: Envío de emails transaccionales
```

```
- **Twilio/Meta API**: Envío de WhatsApp
```

```
- **AWS S3 / Cloudflare R2**: Almacenamiento de archivos
```

```
### 2.2 Nivel 2: Container Diagram (Contenedores)
```

```
```
```

**==> picture [427 x 133] intentionally omitted <==**

**----- Start of picture text -----**<br>
┌─────────────────────────────────────────────────────────────────────┐<br>│                     SGIC 2.0 - ARQUITECTURA                         │<br>├─────────────────────────────────────────────────────────────────────┤<br>│                                                                     │<br>│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐ │<br>│  │  Web App Admin  │    │   PWA Campo     │    │  Portal Familias│ │<br>│  │  (Blade+Livewire│    │  (PWA Offline)  │    │  (Blade+Alpine) │ │<br>│  │  + Tailwind)    │    │  + IndexedDB    │    │   [V1.0]        │ │<br>│  └────────┬────────┘    └────────┬────────┘    └────────┬────────┘ │<br>│           │ HTTPS                │ HTTPS                │ HTTPS    │<br>│           ▼                      ▼                      ▼          │<br>│  ┌─────────────────────────────────────────────────────────────┐   │<br>**----- End of picture text -----**<br>


```
│  │              LARAVEL 11 APPLICATION (PHP 8.2+)              │   │
│  │  ┌──────────────────────────────────────────────────────┐  │   │
│  │  │  Controllers + Services + Jobs + Middleware          │  │   │
│  │  │  Multi-tenant Global Scopes + RBAC (Spatie)          │  │   │
│  │  └──────────────────────────────────────────────────────┘  │   │
│  └──────────┬────────────────┬────────────────┬───────────────┘   │
│             │                │                │                    │
│             ▼                ▼                ▼                    │
│  ┌─────────────────┐  ┌─────────────┐  ┌──────────────────┐       │
│  │   MySQL 8.x     │  │   Redis     │  │  Object Storage  │       │
│  │  (Single DB +   │  │  (Cache +   │  │  (S3/R2/MinIO)   │       │
│  │   tenant_id)    │  │   Queues +  │  │  Fotos, Contratos│       │
│  │                 │  │   Sessions) │  │  Actas, CFDI     │       │
│  └─────────────────┘  └─────────────┘  └──────────────────┘       │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                    │              │              │
                    ▼              ▼              ▼
            ┌──────────┐   ┌──────────┐   ┌──────────┐
            │  SAT PAC │   │SendGrid/ │   │MercadoPago│
            │(Facturama│   │ Twilio   │   │ / Stripe  │
            └──────────┘   └──────────┘   └──────────┘
```

```
```
```

```
**Contenedores:**
```

```
| Contenedor | Tecnología | Responsabilidad |
```

```
|------------|-----------|-----------------|
```

```
| **Web App Admin** | Blade + Livewire 3 + Tailwind | UI para administrativos y
supervisores |
```

```
| **PWA Campo** | PWA + Service Workers + IndexedDB | UI offline-first para
operativos |
```

```
| **Portal Familias** | Blade + Alpine.js | Autogestión para titulares (V1.0) |
| **Laravel App** | PHP 8.2 + Laravel 11 | Lógica de negocio, API, multi-tenancy
|
| **MySQL 8.x** | Single DB + `tenant_id` | Persistencia de datos con
aislamiento por tenant |
```

```
| **Redis** | Cache + Queues + Sessions | Caché, colas de trabajo, sesiones |
```

```
| **Object Storage** | S3 / R2 / MinIO | Fotos, contratos, actas, CFDI |
```

```
### 2.3 Nivel 3: Component Diagram (Componentes Internos)
```

```
```
```

```
┌─────────────────────────────────────────────────────────────────────┐
│              LARAVEL APPLICATION - COMPONENTES INTERNOS             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              HTTP LAYER (Controllers + Middleware)           │   │
│  │  • TenantMiddleware (identifica tenant por subdominio)      │   │
│  │  • RoleMiddleware (RBAC con Spatie)                         │   │
│  │  • AuditMiddleware (registra acciones críticas)             │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                              │                                      │
│                              ▼                                      │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              SERVICE LAYER (Lógica de Negocio)               │   │
│  │  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌──────────┐ │   │
│  │  │ Inventory  │ │ Commercial │ │ Financial  │ │Operations│ │   │
│  │  │ Service    │ │ Service    │ │ Service    │ │ Service  │ │   │
│  │  └────────────┘ └────────────┘ └────────────┘ └──────────┘ │   │
│  │  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌──────────┐ │   │
│  │  │  Decay     │ │  Debt      │ │  Sanitary  │ │  Audit   │ │   │
│  │  │  Service   │ │  Blocking  │ │ Validation │ │  Service │ │   │
```

**==> picture [427 x 265] intentionally omitted <==**

**----- Start of picture text -----**<br>
│  │  │  (RN-03)   │ │  (RN-04)   │ │  (RN-06)   │ │  (RN-07) │ │   │<br>│  │  └────────────┘ └────────────┘ └────────────┘ └──────────┘ │   │<br>│  └─────────────────────────────────────────────────────────────┘   │<br>│                              │                                      │<br>│                              ▼                                      │<br>│  ┌─────────────────────────────────────────────────────────────┐   │<br>│  │              DATA LAYER (Eloquent + Global Scopes)           │   │<br>│  │  • TenantScope (filtra automáticamente por tenant_id)       │   │<br>│  │  • CryptScope (valida estados según RN-01)                  │   │<br>│  │  • Observers (trigger audit_logs automáticamente)           │   │<br>│  └─────────────────────────────────────────────────────────────┘   │<br>│                              │                                      │<br>│                              ▼                                      │<br>│  ┌─────────────────────────────────────────────────────────────┐   │<br>│  │              BACKGROUND JOBS (Laravel Queues)                │   │<br>│  │  • StampCfdiJob (timbrado SAT asíncrono)                    │   │<br>│  │  • ProcessDecayCheckJob (RN-03 diario)                      │   │<br>│  │  • CalculateMoratoriumInterestJob (RN-04 diario)            │   │<br>│  │  • SyncOfflineDataJob (PWA sync)                            │   │<br>│  │  • SendNotificationJob (multi-canal)                        │   │<br>│  └─────────────────────────────────────────────────────────────┘   │<br>│                                                                     │<br>└─────────────────────────────────────────────────────────────────────┘<br>```<br>**----- End of picture text -----**<br>


```
### 2.4 Nivel 4: Code Diagram (Ejemplo: Servicio de Decadencia RN-03)
```

```
```
```

```
┌─────────────────────────────────────────────────────────────────────┐
│  DecayService.php - Implementación de Regla de Negocio RN-03        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  processExpiredContracts()                                          │
│  ├── 1. Query: contratos temporales vencidos                        │
│  ├── 2. For each contract:                                          │
│  │   ├── 2.1 ¿Está en periodo de gracia?                            │
│  │   │   └── SÍ → notificar titular, esperar                        │
│  │   │   └── NO → pasar a siguiente paso                            │
│  │   ├── 2.2 ¿Pasó tiempo legal de decadencia?                      │
│  │   │   └── SÍ → liberar cripta + generar OT traslado              │
│  │   │   └── NO → marcar como "decaying"                            │
│  │   └── 2.3 Registrar en audit_logs (RN-07)                        │
│  └── 3. Commit transaction                                          │
│                                                                     │
│  Dependencies: ContractRepository, CryptRepository, AuditService    │
│  Trigger: ProcessDecayCheckJob (diario, 3:00 AM)                    │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```
```

```
---
```

## `## 3.` 🛠️� `STACK TECNOLÓGICO DETALLADO` 

```
### 3.1 Tabla de Tecnologías
```

```
| Capa | Tecnología | Versión | Justificación | Alternativa considerada |
```

```
|------|-----------|---------|---------------|------------------------|
```

```
| **Lenguaje** | PHP | 8.2+ | Soporte nativo de enums, readonly properties,
fibers | — |
```

```
| **Framework** | Laravel | 11.x | Eloquent, queues, events, ecosistema maduro |
Symfony (más complejo) |
```

```
| **Base de datos** | MySQL | 8.x | JSON nativo, window functions, rendimiento
probado | PostgreSQL (overkill) |
```

```
| **Frontend Admin** | Blade + Livewire 3 | 3.x | Reactividad sin SPA, una sola
base de código | Vue/React (complejidad innecesaria) |
```

```
| **CSS** | Tailwind CSS | 3.4+ | Utility-first, responsive, componentes
reutilizables | Bootstrap (menos flexible) |
```

```
| **JS Interactivo** | Alpine.js | 3.x | Lightweight, complementa Livewire |
jQuery (obsoleto) |
| **PWA** | Service Workers + IndexedDB | — | Offline-first, sin stores | App
nativa (costo alto) |
| **Autenticación** | Laravel Breeze + Sanctum | — | Breeze para web, Sanctum
para API/PWA | Jetstream (overkill) |
```

```
| **RBAC** | Spatie Laravel Permission | 6.x | Granular, probado, bien
documentado | Rol propio (reinventar rueda) |
| **Pasarelas Pago** | MercadoPago + Stripe + PayPal | — | Cobertura LATAM,
multi-moneda | Solo una (limitante) |
```

```
| **Facturación SAT** | Facturama (PAC) | API v3 | Cumple CFDI 4.0, SDK PHP,
soporte | SW sapien (similar) |
| **Email** | SendGrid | API v3 | Deliverability, templates, analytics | SES
(más complejo) |
```

```
| **WhatsApp** | Twilio / Meta API | — | Mensajería oficial, plantillas
aprobadas | — |
| **Object Storage** | AWS S3 / Cloudflare R2 | — | URLs firmadas, CDN,
escalable | MinIO self-hosted (backup) |
```

```
| **Caché/Colas** | Redis | 7.x | Sesiones, caché, queues, pub/sub | File driver
(lento) |
| **Testing** | Pest PHP | 2.x | Más expresivo que PHPUnit, sintaxis moderna |
PHPUnit (más verboso) |
```

```
| **E2E Testing** | Laravel Dusk | 7.x | Browser testing nativo de Laravel |
Cypress (externo) |
```

```
| **Despliegue** | Docker + Laravel Forge | — | Automatizado, zero-downtime |
Manual (riesgoso) |
| **Hosting** | DigitalOcean / AWS | — | Escalable, económico para MVP | Hetzner
(Europa) |
| **Monitoreo** | Sentry + Telescope | — | Errores en tiempo real, debugging |
Bugsnag (similar) |
```

```
| **Logs** | Laravel Log + LogView | — | Centralizado, visualización |
Papertrail (costo) |
```

```
### 3.2 Justificación de Decisiones Críticas
```

```
#### ¿Por qué Single DB + `tenant_id` (no Database per Tenant)?
```

```
| Criterio | Single DB + tenant_id | Database per Tenant |
|----------|----------------------|---------------------|
| **Complejidad operativa** | 📑 Baja (1 BD) | 📑 Alta (100+ BDs) |
| **Costo infraestructura** | 📑 Bajo | 📑 Alto |
```

`| **Migraciones** | 📑 1 script | 📑 100+ scripts | | **Backups** | 📑 1 backup | 📑 100+ backups | | **Consultas cross-tenant** (SuperAdmin) | 📑 Fácil | 📑 Complejo | | **Aislamiento** |` ⚠️� `Medio (Global Scopes) |  Alto | | **Escalabilidad hasta 100 tenants** | 📑 Suficiente | 📑 Overkill |` 

```
**Decisión:** Single DB + `tenant_id` con Global Scopes de Eloquent. Si
superamos 500 tenants o necesitamos aislamiento regulatorio estricto, migraremos
a Database per Tenant.
```

```
#### ¿Por qué Livewire 3 (no Vue/React SPA)?
```

- `📑 **Una sola base de código** (2 devs, tiempo limitado)` 

- `📑 **Reactividad server-side** sin complejidad de SPA` 

- `📑 **SEO nativo** (Blade renderiza HTML)` 

- `📑 **Curva de aprendizaje baja** (solo PHP + Blade)` 

- `📑 **Integración nativa con Laravel** (auth, routes, validation)` 

- `📑 **No es ideal para apps muy interactivas** (pero SGIC no lo requiere)` 

```
#### ¿Por qué PWA (no app nativa)?
```

- `📑 **Offline-first** con Service Workers + IndexedDB` 

- `📑 **Sin publicación en stores** (actualización instantánea)` 

- `📑 **Misma base de código** (web + móvil)` 

- `📑 **Funciona en Android y iOS** (con limitaciones en iOS)` 

- `📑 **Acceso limitado a hardware** (cámara OK, bluetooth NO)` 

```
---
```

## `## 4.  DISEÑO DE BASE DE DATOS` 🗄️� 

```
### 4.1 Diagrama Entidad-Relación (Enfocado en Reglas de Negocio)
```

```
```
```

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           ENTIDADES PRINCIPALES                              │
└─────────────────────────────────────────────────────────────────────────────┘
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│   tenants    │       │    users     │       │  cemeteries  │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ PK id        │──┐    │ PK id        │       │ PK id        │
│ name         │  │    │ FK tenant_id │──┐    │ FK tenant_id │
│ rfc          │  │    │ name         │  │    │ name         │
│ subdomain    │  │    │ email        │  │    │ address      │
│ plan         │  │    │ password     │  │    │ municipality │
│ grace_period │  │    │ FK cemetery_id│─┘    │ FK tenant_id │
│ debt_months  │  │    │ is_active    │  │    └──────────────┘
│ interest_rate│  │    └──────────────┘  │
│ is_active    │  │                      │
│ settings(JSON│  │    ┌──────────────┐  │
│ timestamps   │  │    │   roles      │  │
└──────────────┘  │    ├──────────────┤  │
                  │    │ PK id        │  │
                  │    │ tenant_id    │  │
                  │    │ name         │  │
                  │    └──────┬───────┘  │
                  │           │          │
                  │           ▼          │
                  │    ┌──────────────┐  │
                  │    │ model_has_   │  │
                  │    │ roles        │  │
                  │    └──────────────┘  │
                  │                      │
                  └──────────────────────┘
                         (tenant_id en todas las tablas)
┌─────────────────────────────────────────────────────────────────────────────┐
│                    JERARQUÍA DE INFRAESTRUCTURA (RN-01)                      │
└─────────────────────────────────────────────────────────────────────────────┘
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│  sections    │       │   blocks     │       │   levels     │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ PK id        │──┐    │ PK id        │──┐    │ PK id        │
│ tenant_id    │  │    │ tenant_id    │  │    │ tenant_id    │
│ code         │  │    │ section_id   │──┘    │ block_id     │
│ name         │  │    │ code         │       │ code         │
│ description  │  │    │ name         │       │ name         │
│ FK tenant_id │  │    └──────┬───────┘       └──────┬───────┘
└──────────────┘  │           │                      │
                  │           ▼                      ▼
                  │    ┌──────────────────────────────────────┐
```

```
                  │    │              crypts                   │
                  │    ├──────────────────────────────────────┤
                  │    │ PK id                                │
                  │    │ tenant_id                            │
                  │    │ level_id                             │
                  │    │ crypt_type_id (FK)                   │
                  │    │ crypt_status_id (FK)                 │
                  │    │ code (único por tenant)              │
                  │    │ capacity (1-6)                       │
                  │    │ current_occupancy (0-capacity)       │
                  │    │ price                                │
                  │    │ dimensions                           │
                  │    │ door_type                            │
                  │    │ is_blocked (RN-04)                   │
                  │    │ blocked_reason                       │
                  │    │ timestamps                           │
                  │    └──────────────┬───────────────────────┘
                  │                   │
                  │                   │ 1:N
                  │                   ▼
                  │    ┌──────────────────────────────────────┐
                  │    │          crypt_statuses              │
                  │    ├──────────────────────────────────────┤
                  │    │ PK id                                │
                  │    │ code (available/occupied/reserved/   │
                  │    │      maintenance/decaying/blocked)   │
                  │    │ name                                 │
                  │    │ color                                │
                  │    └──────────────────────────────────────┘
                  │
                  │    ┌──────────────────────────────────────┐
                  │    │           crypt_types                │
                  │    ├──────────────────────────────────────┤
                  │    │ PK id                                │
                  │    │ code (crypt/niche/mausoleum/ossuary) │
                  │    │ name                                 │
                  │    └──────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                   GESTIÓN COMERCIAL (RN-02, RN-05)                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│  customers   │       │  contracts   │       │contract_types│
├──────────────┤       ├──────────────┤       ├──────────────┤
│ PK id        │──┐    │ PK id        │──┐    │ PK id        │
│ tenant_id    │  │    │ tenant_id    │  │    │ code         │
│ type (PF/PM) │  │    │ customer_id  │──┘    │ (perpetual/  │
│ rfc (cifrado)│  │    │ crypt_id     │──┐    │  temp_10/25/ │
│ curp (cifrado│  │    │ contract_type│──┘    │  50)         │
│ name         │  │    │ start_date   │  │    │ name         │
│ email        │  │    │ end_date     │  │    │ years        │
│ phone        │  │    │ price        │  │    │ is_temporary │
│ address      │  │    │ payment_type │  │    │ grace_period │
│ ine_url      │  │    │ status       │  │    └──────────────┘
│ is_deceased  │  │    │ signed_at    │  │
│ death_cert   │  │    │ heir_doc_url │  │
│ timestamps   │  │    │ timestamps   │  │
└──────────────┘  │    └──────┬───────┘  │
                  │           │          │
                  │           │ 1:N      │
                  │           ▼          │
                  │    ┌──────────────┐  │
                  │    │ beneficiaries│  │
```

**==> picture [253 x 330] intentionally omitted <==**

**----- Start of picture text -----**<br>
                  │    ├──────────────┤  │<br>                  │    │ PK id        │  │<br>                  │    │ tenant_id    │  │<br>                  │    │ contract_id  │  │<br>                  │    │ customer_id  │  │<br>                  │    │ relationship │  │<br>                  │    └──────────────┘  │<br>                  │                      │<br>                  │    ┌──────────────┐  │<br>                  │    │    heirs     │  │<br>                  │    ├──────────────┤  │<br>                  │    │ PK id        │  │<br>                  │    │ tenant_id    │  │<br>                  │    │ contract_id  │  │<br>                  │    │ customer_id  │  │<br>                  │    │ is_designated│  │<br>                  │    └──────────────┘  │<br>                  │                      │<br>                  │    ┌──────────────┐  │<br>                  │    │ reservations │  │<br>                  │    ├──────────────┤  │<br>                  │    │ PK id        │  │<br>                  │    │ tenant_id    │  │<br>                  │    │ crypt_id     │  │<br>                  │    │ customer_id  │  │<br>                  │    │ deposit      │  │<br>                  │    │ expires_at   │  │<br>                  │    │ status       │  │<br>                  │    └──────────────┘  │<br>**----- End of picture text -----**<br>


```
┌─────────────────────────────────────────────────────────────────────────────┐
│                   GESTIÓN FINANCIERA (RN-04)                                 │
└─────────────────────────────────────────────────────────────────────────────┘
```

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│   payments   │       │  invoices    │       │    debts     │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ PK id        │       │ PK id        │       │ PK id        │
│ tenant_id    │       │ tenant_id    │       │ tenant_id    │
│ contract_id  │       │ contract_id  │       │ contract_id  │
│ customer_id  │       │ customer_id  │       │ customer_id  │
│ amount       │       │ cfdi_uuid    │       │ amount       │
│ payment_type │       │ rfc          │       │ due_date     │
│ method       │       │ sat_usage    │       │ paid_at      │
│ reference    │       │ xml_url      │       │ interest     │
│ receipt_num  │       │ pdf_url      │       │ status       │
│ paid_at      │       │ status       │       │ (pending/    │
│ user_id      │       │ stamped_at   │       │  paid/overdue│
│ timestamps   │       │ cancelled_at │       │ )            │
└──────────────┘       │ timestamps   │       │ timestamps   │
                       └──────────────┘       └──────────────┘
```

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                   OPERACIONES DE CAMPO (RN-06)                               │
└─────────────────────────────────────────────────────────────────────────────┘
```

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│work_order_   │       │ work_orders  │       │work_order_   │
│  types       │       ├──────────────┤       │  evidences   │
├──────────────┤       │ PK id        │       ├──────────────┤
│ PK id        │       │ tenant_id    │       │ PK id        │
│ code         │◄──────│ type_id      │       │ work_order_id│
│ name         │       │ crypt_id     │       │ type (photo/ │
│ requires_    │       │ customer_id  │       │  signature)  │
```

```
│  sanitary    │       │ crew_id      │       │ file_url     │
│ validation   │       │ scheduled_at │       │ metadata     │
│ (RN-06)      │       │ started_at   │       │ (JSON)       │
└──────────────┘       │ completed_at │       │ taken_at     │
                       │ status       │       │ gps_coords   │
                       │ sanitary_ok  │       └──────────────┘
                       │ observations │
                       │ signature_url│
                       │ judicial_flag│
                       │ timestamps   │
                       └──────┬───────┘
                              │
                              │ N:1
                              ▼
                       ┌──────────────┐       ┌──────────────┐
                       │    crews     │       │crew_members  │
                       ├──────────────┤       ├──────────────┤
                       │ PK id        │       │ PK id        │
                       │ tenant_id    │       │ crew_id      │
                       │ name         │       │ user_id      │
                       │ vehicle      │       │ role         │
                       │ is_active    │       └──────────────┘
                       └──────────────┘
```

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                   AUDITORÍA Y CONFIGURACIÓN (RN-07)                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

```
┌──────────────────────────────────────┐
│           audit_logs                 │
│  (INMUTABLE - Sin UPDATE/DELETE)     │
├──────────────────────────────────────┤
│ PK id                                │
│ tenant_id                            │
│ user_id                              │
│ action (create/update/delete/restore)│
│ model_type                           │
│ model_id                             │
│ old_values (JSON)                    │
│ new_values (JSON)                    │
│ ip_address                           │
│ user_agent                           │
│ url                                  │
│ created_at (único timestamp)         │
└──────────────────────────────────────┘
```

⚠️� `TRIGGER: Prevent UPDATE/DELETE` 

```
┌──────────────────────────────────────┐
│            settings                  │
├──────────────────────────────────────┤
│ PK id                                │
│ tenant_id                            │
│ key (unique per tenant)              │
│ value (JSON)                         │
│ timestamps                           │
└──────────────────────────────────────┘
```

```
```
```

```
### 4.2 Mapeo de Reglas de Negocio a Estructura de BD
```

```
| Regla | Tablas/Columnas Clave | Mecanismo de Implementación |
```

```
|-------|----------------------|----------------------------|
```

```
| **RN-01** (Unicidad y Capacidad) | `crypts.crypt_status_id`,
`crypts.capacity`, `crypts.current_occupancy` | Validación en
```

```
`CryptService::canBeOccupied()` + trigger de estado |
| **RN-02** (Perpetuidad vs Temporalidad) | `contracts.contract_type_id`,
`contracts.start_date`, `contracts.end_date` | `contract_types.is_temporary`
determina lógica de cobro |
| **RN-03** (Decadencia) | `contracts.end_date`, `tenants.grace_period`,
`crypts.crypt_status_id = decaying` | `ProcessDecayCheckJob` diario +
`DecayService` |
| **RN-04** (Bloqueo por Morosidad) | `debts.status`, `crypts.is_blocked`,
`crypts.blocked_reason` | `DebtService` + `BlockingService` + `CryptScope` |
| **RN-05** (Sucesión) | `customers.is_deceased`, `contracts.heir_doc_url` |
Validación en `HeirService::processSuccession()` |
| **RN-06** (Sanidad) | `work_orders.sanitary_ok`, `work_order_evidences`,
`work_order_types.requires_sanitary_validation` | `SanitaryValidationService` +
validación en `WorkOrderService::complete()` |
| **RN-07** (Auditoría Inmutable) | `audit_logs` (tabla inmutable) | Observer
pattern + trigger MySQL (previene UPDATE/DELETE) |
```

```
### 4.3 Migraciones MySQL (Estructura Completa)
```

```
#### 4.3.1 `database/migrations/2026_07_10_000001_create_tenants_table.php`
```

```
```php
<?php
```

```
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
```

```
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('rfc', 13)->unique(); // RFC México
            $table->string('subdomain', 63)->unique(); // {tenant}.sgic.mx
            $table->enum('plan', ['basic', 'professional', 'enterprise'])-
>default('basic');
            $table->integer('grace_period_years')->default(3); // RN-03
            $table->integer('debt_months_to_block')->default(3); // RN-04
            $table->decimal('moratorium_interest_rate', 5, 4)->default(0.02); //
2% mensual
```

```
            $table->integer('reservation_days')->default(15);
            $table->decimal('reservation_deposit_percent', 5, 2)-
>default(20.00);
            $table->integer('maintenance_grace_days')->default(30);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
```

```
            $table->index('subdomain');
            $table->index('is_active');
```

```
        });
```

```
        // Trigger para prevenir eliminación física de tenants (solo soft
delete)
```

```
        DB::unprepared('
            CREATE TRIGGER prevent_tenant_delete
            BEFORE DELETE ON tenants
            FOR EACH ROW
```

```
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Tenants cannot be physically deleted. Use
soft delete.";
            END
        ');
    }
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_tenant_delete');
        Schema::dropIfExists('tenants');
    }
};
```
```

```
#### 4.3.2 `database/migrations/2026_07_10_000002_create_cemeteries_table.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cemeteries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name', 150);
            $table->string('address', 255);
            $table->string('municipality', 100);
            $table->string('state', 50);
            $table->string('postal_code', 5);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('legal_representative', 150);
            $table->string('legal_representative_rfc', 13);
            $table->time('opening_time')->default('08:00');
            $table->time('closing_time')->default('18:00');
            $table->json('schedule')->nullable(); // Horarios especiales
            $table->timestamps();
            $table->index(['tenant_id', 'municipality']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cemeteries');
    }
};
```
#### 4.3.3
`database/migrations/2026_07_10_000003_create_crypt_statuses_and_types_tables.ph
p`
```

```
```php
<?php
```

```
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

```
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypt_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // available, occupied,
reserved, maintenance, decaying, blocked_debt
            $table->string('name', 50);
            $table->string('color', 7); // Hex color (#00FF00)
            $table->string('icon', 30)->nullable();
            $table->boolean('is_available_for_sale')->default(false);
            $table->boolean('is_operational')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        Schema::create('crypt_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // crypt, niche, mausoleum,
ossuary
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->integer('default_capacity')->default(1);
            $table->integer('max_capacity')->default(6);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('crypt_types');
        Schema::dropIfExists('crypt_statuses');
    }
};
```
```

```
#### 4.3.4
```

```
`database/migrations/2026_07_10_000004_create_infrastructure_hierarchy_tables.ph
p`
```

```
```php
<?php
```

```
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

```
return new class extends Migration
{
    public function up(): void
    {
        // Secciones / Manzanas
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "A", "SAN_PEDRO"
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
```

```
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });
        // Bloques
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "1", "B1"
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'section_id', 'code']);
            $table->index(['tenant_id', 'section_id']);
        });
        // Niveles
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "1", "N1"
            $table->string('name', 100);
            $table->integer('height_order')->default(0); // Para visualización
(1 = abajo)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
```

```
            $table->unique(['tenant_id', 'block_id', 'code']);
        });
        // Criptas (entidad core)
        Schema::create('crypts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->foreignId('crypt_type_id')->constrained()-
>onDelete('restrict');
            $table->foreignId('crypt_status_id')->constrained()-
>onDelete('restrict');
```

```
            $table->string('code', 30); // Código único por tenant (Ej: "A-1-3-
05")
            $table->integer('capacity')->default(1); // Máximo de urnas/ataúdes
(RN-01)
            $table->integer('current_occupancy')->default(0); // Inhumaciones
actuales
```

```
            $table->decimal('price', 12, 2)->default(0);
            $table->string('dimensions', 50)->nullable(); // Ej: "2.0x1.0x1.5m"
            $table->enum('door_type', ['marble', 'bronze', 'glass', 'stone',
'other'])->nullable();
            $table->text('notes')->nullable();
```

```
            // RN-04: Bloqueo por morosidad
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason', 100)->nullable();
            $table->timestamp('blocked_at')->nullable();
```

```
            $table->foreignId('blocked_by_user_id')->nullable()-
>constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'crypt_status_id']);
            $table->index(['tenant_id', 'is_blocked']);
            $table->index('level_id');
            // Validación RN-01: current_occupancy <= capacity
            $table->check('`current_occupancy` <= `capacity`');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('crypts');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('sections');
    }
};
```
```

```
#### 4.3.5 `database/migrations/2026_07_10_000005_create_customers_table.php`
```

```
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['persona_fisica', 'persona_moral']);
            $table->string('rfc_encrypted', 500); // RFC cifrado (AES-256)
            $table->string('rfc_hash', 64); // Hash para búsqueda (SHA-256)
            $table->string('curp_encrypted', 500)->nullable(); // CURP cifrado
(solo PF)
            $table->string('name', 200); // Nombre o razón social
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('ine_url', 500)->nullable(); // URL en S3
            $table->string('proof_of_address_url', 500)->nullable();
            // RN-05: Sucesión
            $table->boolean('is_deceased')->default(false);
            $table->date('deceased_at')->nullable();
            $table->string('death_certificate_url', 500)->nullable();
            $table->string('heir_declaration_url', 500)->nullable(); //
Declaratoria de herederos
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
```

```
            $table->timestamps();
            $table->softDeletes();
```

```
            $table->index(['tenant_id', 'rfc_hash']); // Búsqueda por RFC
cifrado
```

```
            $table->index(['tenant_id', 'is_deceased']);
            $table->index(['tenant_id', 'type']);
            $table->fullText('name'); // Búsqueda full-text
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
```
```

```
#### 4.3.6
`database/migrations/2026_07_10_000006_create_contracts_and_related_tables.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Tipos de contrato
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // perpetual, temporary_10,
temporary_25, temporary_50
            $table->string('name', 100);
            $table->integer('years')->nullable(); // NULL para perpetuidad
            $table->boolean('is_temporary')->default(false); // RN-02
            $table->boolean('requires_renewal')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
        // Contratos (RN-02, RN-03, RN-05)
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()-
>onDelete('restrict');
            $table->foreignId('crypt_id')->constrained()->onDelete('restrict');
            $table->foreignId('contract_type_id')->constrained()-
>onDelete('restrict');
```

```
            $table->string('contract_number', 50)->unique(); // Folio único por
tenant
```

```
            $table->date('start_date');
            $table->date('end_date')->nullable(); // NULL para perpetuidad
            $table->decimal('price', 12, 2);
            $table->decimal('annual_maintenance_fee', 10, 2); // Cuota anual de
mantenimiento
            $table->enum('payment_type', ['cash', 'installments', 'mixed']);
            $table->integer('installments_count')->nullable();
```

```
            // RN-05: Sucesión
            $table->boolean('is_succession_pending')->default(false);
            $table->string('heir_document_url', 500)->nullable();
            $table->date('succession_completed_at')->nullable();
```

```
            // Firma digital
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_hash', 64)->nullable(); // SHA-256 de la
firma
```

```
            $table->string('signature_ip', 45)->nullable();
            $table->string('signed_document_url', 500)->nullable();
```

```
            // Estado del contrato
            $table->enum('status', ['draft', 'active', 'expired',
'grace_period', 'decaying', 'terminated', 'renewed'])->default('draft');
```

```
            // RN-03: Decadencia
            $table->date('grace_period_ends_at')->nullable();
            $table->date('decay_process_started_at')->nullable();
```

```
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')-
>nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
```

```
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'crypt_id']);
            $table->index(['tenant_id', 'end_date']); // Para RN-03
            $table->index(['tenant_id', 'contract_number']);
        });
```

```
        // Beneficiarios autorizados
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()-
>onDelete('cascade');
            $table->foreignId('customer_id')->constrained()-
>onDelete('restrict');
            $table->string('relationship', 50); // Esposo/a, hijo/a, etc.
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
```

```
            $table->unique(['contract_id', 'customer_id']);
        });
        // Herederos designados (RN-05)
        Schema::create('heirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()-
>onDelete('cascade');
            $table->foreignId('customer_id')->constrained()-
>onDelete('restrict');
            $table->boolean('is_designated')->default(false);
            $table->decimal('inheritance_percent', 5, 2)->default(100.00);
            $table->timestamps();
```

```
            $table->unique(['contract_id', 'customer_id']);
        });
```

```
        // Reservas
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('crypt_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()-
>onDelete('restrict');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'converted', 'expired',
'cancelled'])->default('active');
            $table->foreignId('contract_id')->nullable()->constrained()-
>nullOnDelete(); // Si se convierte
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('heirs');
        Schema::dropIfExists('beneficiaries');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');
    }
};
```
```

```
#### 4.3.7 `database/migrations/2026_07_10_000007_create_financial_tables.php`
```

```
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

```
return new class extends Migration
{
    public function up(): void
    {
        // Pagos
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('debt_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()-
>nullOnDelete();
            $table->enum('payment_type', ['crypt_sale', 'maintenance',
'service', 'reservation_deposit', 'moratorium_interest']);
            $table->enum('method', ['cash', 'transfer', 'check', 'card',
'online']);
            $table->decimal('amount', 12, 2);
```

```
            $table->string('reference', 100)->nullable(); // Referencia bancaria
            $table->string('receipt_number', 50); // Folio interno
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('registered_by_user_id')->constrained('users')-
>nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'paid_at']);
            $table->index(['tenant_id', 'customer_id']);
            $table->unique(['tenant_id', 'receipt_number']);
        });
        // Facturas CFDI 4.0
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('customer_id')->constrained()-
>onDelete('restrict');
```

```
            $table->string('cfdi_uuid', 36)->unique()->nullable(); // UUID del
SAT
            $table->string('invoice_number', 50); // Folio interno
            $table->string('sat_series', 10); // Serie CFDI
            $table->string('sat_folio', 20); // Folio CFDI
            $table->string('customer_rfc_hash', 64); // Para búsqueda
            $table->enum('sat_usage', ['G01', 'G03', 'G04', 'P01', 'S01']); //
Catálogo SAT
            $table->enum('payment_method_sat', ['PUE', 'PPD']); // Pago en una
sola exhibición / parcial
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('xml_url', 500)->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->string('cancel_reason', 10)->nullable(); // 01-04 catálogo
SAT
            $table->string('replacement_uuid', 36)->nullable();
```

```
            $table->enum('status', ['draft', 'pending_stamp', 'stamped',
'cancelled', 'error'])->default('draft');
            $table->timestamp('stamped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('sat_response')->nullable(); // Respuesta completa del
PAC
            $table->timestamps();
```

```
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index('stamped_at');
        });
        // Adeudos (RN-04)
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()-
>onDelete('cascade');
            $table->foreignId('customer_id')->constrained()-
```

```
>onDelete('cascade');
            $table->foreignId('crypt_id')->constrained()->onDelete('cascade');
            $table->enum('debt_type', ['maintenance', 'sale_installment',
'service']);
            $table->decimal('original_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // original + interest
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2);
            $table->date('due_date');
            $table->date('grace_period_ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'grace_period', 'overdue',
'blocked', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('blocked_at')->nullable();
            $table->integer('days_overdue')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
            $table->index(['tenant_id', 'crypt_id']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('debts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
    }
};
```
```

```
#### 4.3.8 `database/migrations/2026_07_10_000008_create_operations_tables.php`
```

```
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Tipos de órdenes de trabajo
        Schema::create('work_order_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // inhumation, exhumation,
transfer, cleaning, maintenance
            $table->string('name', 100);
            $table->boolean('requires_sanitary_validation')->default(false); //
RN-06
            $table->boolean('requires_death_certificate')->default(false);
            $table->boolean('requires_family_signature')->default(true);
            $table->integer('min_photos')->default(1);
            $table->integer('max_photos')->default(10);
            $table->text('description')->nullable();
            $table->timestamps();
```

```
        });
        // Cuadrillas
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('vehicle_plate', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        // Miembros de cuadrilla
        Schema::create('crew_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['crew_id', 'user_id']);
        });
        // Órdenes de trabajo (RN-06)
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_type_id')->constrained()-
>onDelete('restrict');
            $table->foreignId('crypt_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('crew_id')->nullable()->constrained()-
>nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()-
>constrained('users')->nullOnDelete();
```

```
            $table->string('order_number', 50); // Folio único por tenant
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
```

```
            // RN-06: Validación sanitaria
            $table->boolean('sanitary_validated')->default(false);
            $table->string('death_certificate_url', 500)->nullable();
            $table->enum('body_type', ['corpse', 'urn'])->nullable();
            $table->string('coffin_type', 50)->nullable(); // Tipo de ataúd/urna
            $table->string('coffin_seal_number', 50)->nullable(); // Número de
sello
```

```
            // RN-04: Excepción judicial
            $table->boolean('judicial_exception')->default(false);
            $table->string('judicial_order_url', 500)->nullable();
            $table->text('judicial_notes')->nullable();
```

```
            // Evidencia
            $table->string('signature_url', 500)->nullable();
            $table->string('signature_hash', 64)->nullable();
            $table->string('signature_ip', 45)->nullable();
            $table->timestamp('signature_at')->nullable();
            $table->text('observations')->nullable();
```

```
            // Estado
            $table->enum('status', ['pending', 'assigned', 'in_progress',
'completed', 'cancelled', 'failed'])->default('pending');
            $table->enum('sync_status', ['pending', 'synced', 'conflict',
'error'])->default('pending'); // Para PWA offline
            $table->string('offline_id', 36)->nullable(); // UUID generado
offline
            $table->text('conflict_notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')-
>nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()-
>constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
            $table->index(['tenant_id', 'crypt_id']);
            $table->index(['tenant_id', 'crew_id']);
            $table->index('offline_id');
            $table->index('sync_status');
        });
        // Evidencias de OT (fotos, firmas)
        Schema::create('work_order_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()-
>onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['photo', 'signature', 'document']);
            $table->string('file_url', 500);
            $table->string('file_hash', 64); // SHA-256 del archivo
            $table->integer('file_size')->nullable(); // bytes
            $table->string('mime_type', 50);
            $table->json('metadata')->nullable(); // EXIF, GPS, etc.
            $table->string('gps_latitude', 20)->nullable();
            $table->string('gps_longitude', 20)->nullable();
            $table->timestamp('taken_at');
            $table->foreignId('uploaded_by_user_id')->constrained('users')-
>nullOnDelete();
            $table->timestamps();
            $table->index(['work_order_id', 'type']);
            $table->index('taken_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('work_order_evidences');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('crew_members');
        Schema::dropIfExists('crews');
        Schema::dropIfExists('work_order_types');
    }
};
```
#### 4.3.9 `database/migrations/2026_07_10_000009_create_audit_logs_table.php`
```php
<?php
```

```
use Illuminate\Database\Migrations\Migration;
```

```
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
```

`return new class extends Migration { public function up(): void { Schema::create('audit_logs', function (Blueprint $table) { $table->id(); $table->foreignId('tenant_id')->nullable()->constrained()>nullOnDelete(); // NULL para acciones del SuperAdmin $table->foreignId('user_id')->nullable()->constrained()>nullOnDelete(); // NULL para jobs del sistema $table->string('action', 20); // create, update, delete, restore, login, logout $table->string('model_type', 100); // App\Models\Crypt $table->unsignedBigInteger('model_id'); $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->string('ip_address', 45)->nullable(); $table->text('user_agent')->nullable(); $table->string('url', 500)->nullable(); $table->text('description')->nullable(); $table->json('tags')->nullable(); // Para categorización $table->timestamp('created_at')->useCurrent(); // Índices para consultas rápidas $table->index(['tenant_id', 'created_at']); $table->index(['tenant_id', 'user_id']); $table->index(['tenant_id', 'model_type', 'model_id']); $table->index('action'); $table->index('created_at'); //` ⚠️� `NO hay updated_at ni deleted_at (tabla inmutable) }); //  TRIGGER: Previene UPDATE en audit_logs (RN-07)` 🔒 `DB::unprepared(' CREATE TRIGGER prevent_audit_update BEFORE UPDATE ON audit_logs FOR EACH ROW BEGIN SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Audit logs are immutable. UPDATE is not allowed."; END '); //  TRIGGER: Previene DELETE en audit_logs (RN-07)` 🔒 `DB::unprepared(' CREATE TRIGGER prevent_audit_delete BEFORE DELETE ON audit_logs FOR EACH ROW BEGIN SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Audit logs are immutable. DELETE is not allowed."; END '); } public function down(): void {` 

```
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_update');
        Schema::dropIfExists('audit_logs');
```

```
    }
};
```
```

```
#### 4.3.10
```

```
`database/migrations/2026_07_10_000010_create_settings_and_notifications_tables.
php`
```

```
```php
<?php
```

```
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

```
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('key', 100);
            $table->json('value');
            $table->string('description', 255)->nullable();
            $table->timestamps();
```

```
            $table->unique(['tenant_id', 'key']);
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->nullable()->constrained()-
>nullOnDelete();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel', 20); // email, whatsapp, sms, in_app
            $table->string('type', 50); // debt_reminder, contract_expiry,
work_order_assigned
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])-
>default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
```

```
            $table->index(['tenant_id', 'status']);
            $table->index(['notifiable_type', 'notifiable_id']);
        });
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->morphs('documentable'); // contract, customer, work_order,
etc.
            $table->string('type', 50); // contract_signed, death_certificate,
heir_declaration, judicial_order
            $table->string('name', 255);
            $table->string('file_url', 500);
```

```
            $table->string('file_hash', 64);
            $table->integer('file_size');
            $table->string('mime_type', 100);
            $table->foreignId('uploaded_by_user_id')->constrained('users')-
>nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'type']);
            $table->index(['documentable_type', 'documentable_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('settings');
    }
};
```
```

```
### 4.4 Seeders de Datos Iniciales
```

```
#### `database/seeders/CatalogSeeder.php`
```

```
```php
<?php
namespace Database\Seeders;
```

```
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Estados de cripta
        DB::table('crypt_statuses')->insert([
            ['code' => 'available', 'name' => 'Disponible', 'color' =>
'#10B981', 'is_available_for_sale' => true, 'is_operational' => true, 'order' =>
1],
            ['code' => 'occupied', 'name' => 'Ocupada', 'color' => '#EF4444',
'is_available_for_sale' => false, 'is_operational' => true, 'order' => 2],
            ['code' => 'reserved', 'name' => 'Reservada', 'color' => '#F59E0B',
'is_available_for_sale' => false, 'is_operational' => true, 'order' => 3],
            ['code' => 'maintenance', 'name' => 'En Mantenimiento', 'color' =>
'#3B82F6', 'is_available_for_sale' => false, 'is_operational' => false, 'order'
=> 4],
            ['code' => 'decaying', 'name' => 'En Decadencia', 'color' =>
'#8B5CF6', 'is_available_for_sale' => false, 'is_operational' => false, 'order'
=> 5],
            ['code' => 'blocked_debt', 'name' => 'Bloqueada por Morosidad',
'color' => '#6B7280', 'is_available_for_sale' => false, 'is_operational' =>
false, 'order' => 6],
        ]);
        // Tipos de cripta
        DB::table('crypt_types')->insert([
            ['code' => 'crypt', 'name' => 'Cripta', 'default_capacity' => 2,
'max_capacity' => 4],
            ['code' => 'niche', 'name' => 'Nicho', 'default_capacity' => 1,
'max_capacity' => 2],
            ['code' => 'mausoleum', 'name' => 'Mausoleo', 'default_capacity' =>
```

```
4, 'max_capacity' => 6],
            ['code' => 'ossuary', 'name' => 'Osario', 'default_capacity' => 1,
'max_capacity' => 1],
        ]);
```

```
        // Tipos de contrato
        DB::table('contract_types')->insert([
            ['code' => 'perpetual', 'name' => 'Perpetuidad', 'years' => null,
'is_temporary' => false, 'requires_renewal' => false],
            ['code' => 'temporary_10', 'name' => 'Temporal 10 años', 'years' =>
10, 'is_temporary' => true, 'requires_renewal' => true],
            ['code' => 'temporary_25', 'name' => 'Temporal 25 años', 'years' =>
25, 'is_temporary' => true, 'requires_renewal' => true],
            ['code' => 'temporary_50', 'name' => 'Temporal 50 años', 'years' =>
50, 'is_temporary' => true, 'requires_renewal' => true],
        ]);
```

```
        // Tipos de órdenes de trabajo
        DB::table('work_order_types')->insert([
            ['code' => 'inhumation', 'name' => 'Inhumación',
'requires_sanitary_validation' => true, 'requires_death_certificate' => true,
'requires_family_signature' => true, 'min_photos' => 2, 'max_photos' => 10],
            ['code' => 'exhumation', 'name' => 'Exhumación',
'requires_sanitary_validation' => true, 'requires_death_certificate' => false,
'requires_family_signature' => true, 'min_photos' => 2, 'max_photos' => 10],
            ['code' => 'transfer', 'name' => 'Traslado',
'requires_sanitary_validation' => true, 'requires_death_certificate' => false,
'requires_family_signature' => true, 'min_photos' => 1, 'max_photos' => 5],
            ['code' => 'cleaning', 'name' => 'Limpieza',
'requires_sanitary_validation' => false, 'requires_death_certificate' => false,
'requires_family_signature' => false, 'min_photos' => 2, 'max_photos' => 5],
            ['code' => 'maintenance', 'name' => 'Mantenimiento',
'requires_sanitary_validation' => false, 'requires_death_certificate' => false,
'requires_family_signature' => false, 'min_photos' => 1, 'max_photos' => 5],
        ]);
    }
}
```
```

```
---
```

```
## 5. 📑 MODELOS ELOQUENT
```

```
### 5.1 Modelo Base con Multi-tenancy: `app/Models/Traits/BelongsToTenant.php`
```

```
```php
<?php
```

```
namespace App\Models\Traits;
```

```
use App\Models\Scopes\TenantScope;
```

```
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
```

```
        // Aplica el TenantScope automáticamente a todas las consultas
        static::addGlobalScope(new TenantScope());
```

```
        // Asigna automáticamente el tenant_id al crear
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
```

```
        });
    }
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
```
```

```
### 5.2 TenantScope: `app/Models/Scopes/TenantScope.php`
```

```
```php
<?php
```

```
namespace App\Models\Scopes;
```

```
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // No aplicar si es SuperAdmin (ve todos los tenants)
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            return;
        }
        // Aplicar filtro por tenant_id
        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where($model->getTable() . '.tenant_id', auth()->user()-
>tenant_id);
        }
    }
    public function extend(Builder $builder): void
    {
        // Agregar macro para ignorar el scope (solo SuperAdmin)
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
```
```

```
### 5.3 Modelo Tenant: `app/Models/Tenant.php`
```

```
```php
<?php
```

```
namespace App\Models;
```

```
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
```

```
class Tenant extends Model
{
    use HasFactory, SoftDeletes;
```

```
    protected $fillable = [
        'name',
        'rfc',
        'subdomain',
        'plan',
        'grace_period_years',
        'debt_months_to_block',
        'moratorium_interest_rate',
        'reservation_days',
        'reservation_deposit_percent',
        'maintenance_grace_days',
        'settings',
        'is_active',
        'subscription_ends_at',
    ];
    protected $casts = [
        'grace_period_years' => 'integer',
        'debt_months_to_block' => 'integer',
        'moratorium_interest_rate' => 'decimal:4',
        'reservation_days' => 'integer',
        'reservation_deposit_percent' => 'decimal:2',
        'maintenance_grace_days' => 'integer',
        'settings' => 'array',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];
    protected $hidden = [
        'settings', // Configuraciones sensibles
    ];
    // Relaciones
    public function cemetery(): HasOne
    {
        return $this->hasOne(Cemetery::class);
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function crypts(): HasMany
    {
        return $this->hasMany(Crypt::class);
    }
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
    // Accessors
    public function getFullSubdomainAttribute(): string
    {
        return $this->subdomain . '.sgic.mx';
    }
```

```
    public function getIsActiveSubscriptionAttribute(): bool
    {
        return $this->is_active &&
               ($this->subscription_ends_at === null || $this-
>subscription_ends_at->isFuture());
    }
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeWithActiveSubscription($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('subscription_ends_at')
              ->orWhere('subscription_ends_at', '>', now());
        });
    }
}
```
```

```
### 5.4 Modelo Crypt (con validación RN-01): `app/Models/Crypt.php`
```php
<?php
namespace App\Models;
```

```
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\LogsActivity;
use App\Enums\CryptStatus;
class Crypt extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, LogsActivity;
    protected $fillable = [
        'level_id',
        'crypt_type_id',
        'crypt_status_id',
        'code',
        'capacity',
        'current_occupancy',
        'price',
        'dimensions',
        'door_type',
        'notes',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'blocked_by_user_id',
    ];
    protected $casts = [
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
```

```
        'price' => 'decimal:2',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];
```

```
    protected $appends = ['full_code', 'is_available_for_sale',
'available_capacity'];
```

```
    // Relaciones
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
    public function cryptType(): BelongsTo
    {
        return $this->belongsTo(CryptType::class, 'crypt_type_id');
    }
    public function cryptStatus(): BelongsTo
    {
        return $this->belongsTo(CryptStatus::class, 'crypt_status_id');
    }
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
    public function activeContract(): HasMany
    {
        return $this->hasMany(Contract::class)->where('status', 'active');
    }
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    // Accessors
    public function getFullCodeAttribute(): string
    {
        return "{$this->level->block->section->code}-{$this->level->block-
>code}-{$this->level->code}-{$this->code}";
    }
```

```
    public function getIsAvailableForSaleAttribute(): bool
    {
        return $this->cryptStatus->is_available_for_sale && !$this->is_blocked;
    }
    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
```

```
    }
    // Scopes (RN-01, RN-04)
    public function scopeAvailable($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code',
'available'))
                     ->where('is_blocked', false);
    }
    public function scopeOccupied($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code',
'occupied'));
    }
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }
    public function scopeDecaying($query)
    {
        return $query->whereHas('cryptStatus', fn($q) => $q->where('code',
'decaying'));
    }
    public function scopeWithCapacity($query, int $required = 1)
    {
        return $query->whereRaw('`capacity` - `current_occupancy` >= ?',
[$required]);
    }
    // Métodos de negocio
    public function canBeOccupied(): bool
    {
        // RN-01: No puede estar ocupada, reservada, en mantenimiento o
bloqueada
        return $this->is_available_for_sale &&
               $this->available_capacity > 0 &&
               !$this->is_blocked;
    }
    public function canBeInhumed(): bool
    {
        // RN-01 + RN-04 + RN-06
        return $this->canBeOccupied() &&
               $this->cryptStatus->code === 'occupied'; // Ya debe tener
contrato activo
    }
    public function canBeExhumed(): bool
    {
        // RN-04: No se puede exhumar si está bloqueada (salvo orden judicial)
        return !$this->is_blocked || $this->hasJudicialException();
    }
    public function block(string $reason, ?int $userId = null): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
            'blocked_by_user_id' => $userId,
```

```
        ]);
    }
    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
            'blocked_by_user_id' => null,
        ]);
    }
    public function incrementOccupancy(): void
    {
        if ($this->current_occupancy >= $this->capacity) {
            throw new \DomainException('La cripta ha alcanzado su capacidad
máxima.');
        }
        $this->increment('current_occupancy');
        // Si se llenó, cambiar estado a "occupied" completo
        if ($this->current_occupancy === $this->capacity) {
            $this->changeStatus('occupied');
        }
    }
    public function decrementOccupancy(): void
    {
        if ($this->current_occupancy <= 0) {
            throw new \DomainException('La cripta ya está vacía.');
        }
        $this->decrement('current_occupancy');
        // Si se vació completamente, cambiar a "available"
        if ($this->current_occupancy === 0) {
            $this->changeStatus('available');
        }
    }
    public function changeStatus(string $statusCode): void
    {
        $status = CryptStatusModel::where('code', $statusCode)->firstOrFail();
        $this->update(['crypt_status_id' => $status->id]);
    }
}
```
```

```
### 5.5 Modelo Contract (con RN-02, RN-03, RN-05): `app/Models/Contract.php`
```php
<?php
namespace App\Models;
```

```
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Traits\BelongsToTenant;
```

```
use App\Models\Traits\LogsActivity;
use Carbon\Carbon;
class Contract extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, LogsActivity;
    protected $fillable = [
        'customer_id',
        'crypt_id',
        'contract_type_id',
        'contract_number',
        'start_date',
        'end_date',
        'price',
        'annual_maintenance_fee',
        'payment_type',
        'installments_count',
        'is_succession_pending',
        'heir_document_url',
        'succession_completed_at',
        'signed_at',
        'signature_hash',
        'signature_ip',
        'signed_document_url',
        'status',
        'grace_period_ends_at',
        'decay_process_started_at',
        'notes',
        'created_by_user_id',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'annual_maintenance_fee' => 'decimal:2',
        'is_succession_pending' => 'boolean',
        'succession_completed_at' => 'datetime',
        'signed_at' => 'datetime',
        'grace_period_ends_at' => 'date',
        'decay_process_started_at' => 'date',
    ];
    protected $appends = ['is_expired', 'days_until_expiry',
'is_in_grace_period'];
```

```
    // Relaciones
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function crypt(): BelongsTo
    {
        return $this->belongsTo(Crypt::class);
    }
    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }
    public function beneficiaries(): HasMany
```

```
    {
        return $this->hasMany(Beneficiary::class);
    }
```

```
    public function heirs(): HasMany
    {
        return $this->hasMany(Heir::class);
    }
```

```
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
```

```
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
```

```
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
```

```
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
```

```
    // Accessors (RN-02, RN-03)
    public function getIsPerpetualAttribute(): bool
    {
        return !$this->contractType->is_temporary;
    }
```

```
    public function getIsTemporaryAttribute(): bool
    {
        return $this->contractType->is_temporary;
    }
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_date) return null;
        return now()->diffInDays($this->end_date, false);
    }
    public function getIsInGracePeriodAttribute(): bool
    {
        return $this->status === 'grace_period';
    }
    public function getMonthsOverdueAttribute(): int
    {
        if (!$this->is_expired) return 0;
        return (int) now()->diffInMonths($this->end_date);
    }
```

```
    // Scopes
    public function scopeActive($query)
```

```
    {
        return $query->where('status', 'active');
    }
    public function scopeTemporary($query)
    {
        return $query->whereHas('contractType', fn($q) => $q-
>where('is_temporary', true));
    }
    public function scopePerpetual($query)
    {
        return $query->whereHas('contractType', fn($q) => $q-
>where('is_temporary', false));
    }
    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->temporary()
                     ->active()
                     ->whereNotNull('end_date')
                     ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }
    public function scopeExpired($query)
    {
        return $query->temporary()
                     ->whereNotNull('end_date')
                     ->where('end_date', '<', now())
                     ->whereIn('status', ['active', 'expired', 'grace_period']);
    }
    public function scopeInGracePeriod($query)
    {
        return $query->where('status', 'grace_period');
    }
    public function scopeSuccessionPending($query)
    {
        return $query->where('is_succession_pending', true);
    }
```

```
    // Métodos de negocio (RN-03)
    public function enterGracePeriod(int $years): void
    {
        $this->update([
            'status' => 'grace_period',
            'grace_period_ends_at' => $this->end_date->addYears($years),
        ]);
    }
    public function startDecayProcess(): void
    {
        $this->update([
            'status' => 'decaying',
            'decay_process_started_at' => now(),
        ]);
        // Cambiar estado de la cripta (RN-03)
        $this->crypt->changeStatus('decaying');
    }
```

```
    public function renew(Carbon $newEndDate, decimal $newPrice): self
    {
```

```
        $renewed = $this->replicate()->fill([
            'start_date' => $this->end_date->addDay(),
            'end_date' => $newEndDate,
            'price' => $newPrice,
            'status' => 'active',
            'contract_number' => $this->generateContractNumber(),
        ]);
```

```
        $renewed->save();
```

```
        // Marcar contrato anterior como renovado
        $this->update(['status' => 'renewed']);
        return $renewed;
    }
    public function canBeTransferred(): bool
    {
        // RN-05: No se puede transferir si hay sucesión pendiente o adeudos
        return !$this->is_succession_pending &&
               $this->debts()->where('status', '!=', 'paid')->count() === 0;
    }
    // Helpers
    protected function generateContractNumber(): string
    {
        $year = now()->format('Y');
        $last = self::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereYear('created_at', $year)
                    ->max('id') ?? 0;
```

```
        return sprintf('CTR-%s-%05d', $year, $last + 1);
    }
}
```
```

```
### 5.6 Modelo AuditLog (inmutable, RN-07): `app/Models/AuditLog.php`
```

```
```php
<?php
```

```
namespace App\Models;
```

```
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

`class AuditLog extends Model { //` ⚠️� `NO usar SoftDeletes (tabla inmutable) public $timestamps = false; // Solo created_at` 

```
    protected $table = 'audit_logs';
```

```
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
```

```
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
    // Scopes
    public function scopeForTenant($query, ?int $tenantId = null)
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }
    public function scopeForModel($query, string $modelType, ?int $modelId =
null)
    {
        $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
```

`//` ⚠️� `Previene UPDATE (doble seguridad, además del trigger MySQL) protected static function boot() {` 

```
        parent::boot();
```

```
        static::updating(function () {
            throw new \DomainException('Audit logs are immutable. UPDATE is not
allowed.');
        });
        static::deleting(function () {
            throw new \DomainException('Audit logs are immutable. DELETE is not
allowed.');
        });
    }
}
```
```

```
### 5.7 Trait LogsActivity (Observer automático):
`app/Models/Traits/LogsActivity.php`
```php
<?php
namespace App\Models\Traits;
use App\Models\AuditLog;
trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('create');
        });
        static::updated(function ($model) {
            // Solo registrar si hay cambios reales
            if ($model->getOriginal() !== $model->getAttributes()) {
                $model->recordActivity('update');
            }
        });
        static::deleted(function ($model) {
            $model->recordActivity('delete');
        });
        static::restored(function ($model) {
            $model->recordActivity('restore');
        });
    }
    protected function recordActivity(string $action): void
    {
        // Modelos que no deben ser auditados (evitar loops)
        $excluded = [AuditLog::class];
        if (in_array(static::class, $excluded)) {
            return;
        }
        $properties = [
            'tenant_id' => $this->tenant_id ?? auth()->user()?->tenant_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => static::class,
            'model_id' => $this->getKey(),
            'old_values' => $action === 'create' ? null : $this-
```

```
>getOriginalExceptTimestamps(),
            'new_values' => $action === 'delete' ? null : $this-
>getAttributesExceptTimestamps(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'created_at' => now(),
        ];
        // Insertar directamente (evitar triggers de AuditLog)
        AuditLog::insert($properties);
    }
    protected function getOriginalExceptTimestamps(): array
    {
        return collect($this->getOriginal())
            ->except(['created_at', 'updated_at', 'deleted_at'])
            ->toArray();
    }
    protected function getAttributesExceptTimestamps(): array
    {
        return collect($this->getAttributes())
            ->except(['created_at', 'updated_at', 'deleted_at'])
            ->toArray();
    }
```

```
    // Método para personalizar qué atributos auditar
    protected function getAuditableAttributes(): array
    {
        return $this->getFillable();
    }
}
```
---
```

```
## 6. 📑 SERVICIOS (LÓGICA DE NEGOCIO)
```

```
### 6.1 CryptService: `app/Services/Inventory/CryptService.php`
```

```
```php
<?php
```

```
namespace App\Services\Inventory;
use App\Models\Crypt;
use App\Models\CryptStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CryptNotAvailableException;
```

```
class CryptService
{
    /**
     * Validar si una cripta puede ser vendida/concedida (RN-01)
     */
    public function validateForSale(Crypt $crypt): void
    {
        if (!$crypt->is_available_for_sale) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} no está disponible para venta. Estado
actual: {$crypt->cryptStatus->name}"
            );
```

```
        }
        if ($crypt->is_blocked) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} está bloqueada: {$crypt-
>blocked_reason}"
            );
        }
        if ($crypt->current_occupancy > 0) {
            throw new CryptNotAvailableException(
                "La cripta {$crypt->code} ya tiene ocupación ({$crypt-
>current_occupancy}/{$crypt->capacity})"
            );
        }
    }
    /**
     * Reservar una cripta
     */
    public function reserve(Crypt $crypt, int $customerId, int $days, decimal
$deposit): \App\Models\Reservation
    {
        $this->validateForSale($crypt);
        return DB::transaction(function () use ($crypt, $customerId, $days,
$deposit) {
            // Cambiar estado a "reserved"
            $crypt->changeStatus('reserved');
            // Crear reserva
            return \App\Models\Reservation::create([
                'tenant_id' => auth()->user()->tenant_id,
                'crypt_id' => $crypt->id,
                'customer_id' => $customerId,
                'deposit_amount' => $deposit,
                'reserved_at' => now(),
                'expires_at' => now()->addDays($days),
                'status' => 'active',
            ]);
        });
    }
    /**
     * Ocupar cripta (al firmar contrato)
     */
    public function occupy(Crypt $crypt): void
    {
        DB::transaction(function () use ($crypt) {
            $crypt->changeStatus('occupied');
            Log::info("Cripta {$crypt->code} marcada como ocupada");
        });
    }
    /**
     * Bloquear cripta por morosidad (RN-04)
     */
    public function blockForDebt(Crypt $crypt, int $monthsOverdue): void
    {
        if ($crypt->is_blocked) {
            return; // Ya está bloqueada
        }
        DB::transaction(function () use ($crypt, $monthsOverdue) {
```

```
            $crypt->block(
                reason: "Morosidad superior a {$monthsOverdue} meses",
                userId: null // Sistema
            );
            // Cambiar estado a "blocked_debt"
            $crypt->changeStatus('blocked_debt');
            Log::warning("Cripta {$crypt->code} bloqueada por morosidad
({$monthsOverdue} meses)");
        });
    }
    /**
     * Desbloquear cripta al liquidar adeudo (RN-04)
     */
    public function unblockAfterPayment(Crypt $crypt): void
    {
        if (!$crypt->is_blocked) {
            return;
        }
        DB::transaction(function () use ($crypt) {
            // Determinar estado correcto según contrato
            $hasActiveContract = $crypt->activeContract()->exists();
            $newStatus = $hasActiveContract ? 'occupied' : 'available';
            $crypt->unblock();
            $crypt->changeStatus($newStatus);
            Log::info("Cripta {$crypt->code} desbloqueada tras pago. Nuevo
estado: {$newStatus}");
        });
    }
    /**
     * Liberar cripta tras proceso de decadencia (RN-03)
     */
    public function releaseAfterDecay(Crypt $crypt): void
    {
        DB::transaction(function () use ($crypt) {
            $crypt->update([
                'current_occupancy' => 0,
                'is_blocked' => false,
                'blocked_reason' => null,
            ]);
            $crypt->changeStatus('available');
            Log::info("Cripta {$crypt->code} liberada tras proceso de
decadencia");
        });
    }
    /**
     * Obtener mapa jerárquico para visualización
     */
    public function getMapData(): array
    {
        return \App\Models\Section::with([
            'blocks.levels.crypts.cryptStatus',
            'blocks.levels.crypts.cryptType',
        ])->get()->map(function ($section) {
            return [
```

```
                'id' => $section->id,
                'code' => $section->code,
                'name' => $section->name,
                'blocks' => $section->blocks->map(function ($block) {
                    return [
                        'id' => $block->id,
                        'code' => $block->code,
                        'name' => $block->name,
                        'levels' => $block->levels->sortBy('height_order')-
>map(function ($level) {
                            return [
                                'id' => $level->id,
                                'code' => $level->code,
                                'crypts' => $level->crypts->map(function
($crypt) {
                                    return [
                                        'id' => $crypt->id,
                                        'code' => $crypt->code,
                                        'status_code' => $crypt->cryptStatus-
>code,
                                        'status_name' => $crypt->cryptStatus-
>name,
```

```
                                        'status_color' => $crypt->cryptStatus-
>color,
```

```
                                        'type_name' => $crypt->cryptType->name,
                                        'capacity' => $crypt->capacity,
                                        'occupancy' => $crypt-
>current_occupancy,
```

```
                                        'is_blocked' => $crypt->is_blocked,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ];
        })->toArray();
    }
}
```
```

```
### 6.2 DecayService (RN-03): `app/Services/Commercial/DecayService.php`
```

```
```php
<?php
```

```
namespace App\Services\Commercial;
```

```
use App\Models\Contract;
use App\Models\Crypt;
use App\Services\Inventory\CryptService;
use App\Services\Operations\WorkOrderService;
use App\Services\Audit\AuditService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
```

```
class DecayService
{
    public function __construct(
        private CryptService $cryptService,
        private WorkOrderService $workOrderService,
        private AuditService $auditService,
        private NotificationService $notificationService
```

```
    ) {}
    /**
     * Proceso diario: revisar contratos temporales vencidos (RN-03)
     * Ejecutado por ProcessDecayCheckJob
     */
    public function processExpiredContracts(): array
    {
        $stats = [
            'notified' => 0,
            'entered_grace_period' => 0,
            'started_decay' => 0,
            'released' => 0,
            'errors' => 0,
        ];
        // 1. Contratos activos temporales próximos a vencer (alertas 12/6/3
meses)
```

```
        $this->sendExpiryAlerts($stats);
```

```
        // 2. Contratos vencidos que deben entrar en periodo de gracia
        $this->enterGracePeriod($stats);
```

```
        // 3. Contratos en periodo de gracia que deben iniciar decadencia
        $this->startDecayProcess($stats);
```

```
        // 4. Contratos en decadencia que deben liberar cripta
        $this->releaseCrypts($stats);
        Log::info('Decay process completed', $stats);
        return $stats;
    }
    /**
     * Enviar alertas de vencimiento (12, 6, 3 meses antes)
     */
    private function sendExpiryAlerts(array &$stats): void
    {
        $alerts = [365 => '12 meses', 180 => '6 meses', 90 => '3 meses'];
        foreach ($alerts as $days => $label) {
            $contracts = Contract::temporary()
                ->active()
                ->whereNotNull('end_date')
                ->whereDate('end_date', now()->addDays($days)->toDateString())
                ->get();
            foreach ($contracts as $contract) {
                try {
                    $this->notificationService-
>sendContractExpiryAlert($contract, $label);
                    $stats['notified']++;
                } catch (\Exception $e) {
                    Log::error("Error enviando alerta de vencimiento", [
                        'contract_id' => $contract->id,
                        'error' => $e->getMessage()
                    ]);
                    $stats['errors']++;
                }
            }
        }
    }
    /**
```

```
     * Contratos vencidos entran en periodo de gracia
     */
    private function enterGracePeriod(array &$stats): void
    {
        $contracts = Contract::temporary()
            ->active()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now())
            ->whereNull('grace_period_ends_at')
            ->get();
        foreach ($contracts as $contract) {
            try {
                DB::transaction(function () use ($contract) {
                    $tenant = $contract->tenant;
                    $graceYears = $tenant->grace_period_years; // Parametrizable
por tenant
                    $contract->enterGracePeriod($graceYears);
                    $this->auditService->log(
                        action: 'contract_entered_grace_period',
                        model: $contract,
                        description: "Contrato entró en periodo de gracia
({$graceYears} años)"
                    );
                    $this->notificationService-
>sendGracePeriodNotification($contract, $graceYears);
                });
                $stats['entered_grace_period']++;
            } catch (\Exception $e) {
                Log::error("Error entrando en periodo de gracia", [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }
    }
    /**
     * Contratos en periodo de gracia vencido inician decadencia
     */
    private function startDecayProcess(array &$stats): void
    {
        $contracts = Contract::inGracePeriod()
            ->whereNotNull('grace_period_ends_at')
            ->whereDate('grace_period_ends_at', '<', now())
            ->get();
        foreach ($contracts as $contract) {
            try {
                DB::transaction(function () use ($contract) {
                    $contract->startDecayProcess();
                    // Generar OT para traslado a osario común
                    $this->workOrderService->createTransferToOssuary($contract);
                    $this->auditService->log(
                        action: 'decay_process_started',
                        model: $contract,
                        description: "Proceso de decadencia iniciado"
```

```
                    );
```

```
                    $this->notificationService-
>sendDecayProcessNotification($contract);
                });
                $stats['started_decay']++;
            } catch (\Exception $e) {
                Log::error("Error iniciando proceso de decadencia", [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }
    }
    /**
     * Contratos en decadencia con OT completada liberan cripta
     */
    private function releaseCrypts(array &$stats): void
    {
        $contracts = Contract::where('status', 'decaying')
            ->whereNotNull('decay_process_started_at')
            ->get();
        foreach ($contracts as $contract) {
            // Verificar que la OT de traslado esté completada
            $transferOT = $contract->crypt->workOrders()
                ->where('work_order_type_id', WorkOrderType::where('code',
'transfer')->first()->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', $contract->decay_process_started_at)
                ->exists();
            if ($transferOT) {
                try {
                    DB::transaction(function () use ($contract) {
                        $this->cryptService->releaseAfterDecay($contract-
>crypt);
                        $contract->update(['status' => 'terminated']);
                        $this->auditService->log(
                            action: 'crypt_released_after_decay',
                            model: $contract->crypt,
                            description: "Cripta liberada tras proceso de
decadencia"
                        );
                    });
                    $stats['released']++;
                } catch (\Exception $e) {
                    Log::error("Error liberando cripta", [
                        'contract_id' => $contract->id,
                        'error' => $e->getMessage()
                    ]);
                    $stats['errors']++;
                }
            }
        }
    }
}
```
```

```
### 6.3 DebtBlockingService (RN-04):
```

```
`app/Services/Financial/DebtBlockingService.php`
```

```
```php
<?php
```

```
namespace App\Services\Financial;
```

```
use App\Models\Contract;
use App\Models\Debt;
use App\Services\Inventory\CryptService;
use App\Services\Audit\AuditService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
```

```
class DebtBlockingService
{
    public function __construct(
        private CryptService $cryptService,
        private AuditService $auditService,
        private NotificationService $notificationService
    ) {}
    /**
     * Proceso diario: revisar morosidad y bloquear criptas (RN-04)
     * Ejecutado por CalculateMoratoriumInterestJob
     */
    public function processMoratorium(): array
    {
        $stats = ['blocked' => 0, 'unblocked' => 0, 'errors' => 0];
```

```
        // 1. Calcular intereses moratorios
        $this->calculateInterests();
        // 2. Bloquear criptas con morosidad superior al umbral
        $this->blockOverdueCrypts($stats);
        // 3. Desbloquear criptas que liquidaron adeudos
        $this->unblockPaidCrypts($stats);
        return $stats;
    }
    /**
     * Calcular intereses moratorios sobre adeudos vencidos
     */
    private function calculateInterests(): void
    {
        $overdueDebts = Debt::whereIn('status', ['overdue', 'grace_period'])
            ->where('due_date', '<', now())
            ->get();
        foreach ($overdueDebts as $debt) {
            $tenant = $debt->tenant;
            $monthlyRate = $tenant->moratorium_interest_rate;
            $daysOverdue = now()->diffInDays($debt->due_date);
            $months = ceil($daysOverdue / 30);
            $interest = $debt->original_amount * $monthlyRate * $months;
```

```
            $debt->update([
                'interest_amount' => $interest,
```

```
                'total_amount' => $debt->original_amount + $interest,
                'pending_amount' => $debt->original_amount + $interest - $debt-
>paid_amount,
                'days_overdue' => $daysOverdue,
                'status' => 'overdue',
            ]);
        }
    }
    /**
     * Bloquear criptas con morosidad superior al umbral del tenant
     */
    private function blockOverdueCrypts(array &$stats): void
    {
        $contracts = Contract::active()->get();
        foreach ($contracts as $contract) {
            $tenant = $contract->tenant;
            $monthsThreshold = $tenant->debt_months_to_block;
            // Contar meses de adeudo más antiguo impago
            $oldestDebt = $contract->debts()
                ->whereIn('status', ['overdue', 'grace_period'])
                ->orderBy('due_date', 'asc')
                ->first();
            if (!$oldestDebt) {
                continue;
            }
            $monthsOverdue = (int) now()->diffInMonths($oldestDebt->due_date);
            if ($monthsOverdue >= $monthsThreshold && !$contract->crypt-
>is_blocked) {
                try {
                    DB::transaction(function () use ($contract, $monthsOverdue)
{
                        $this->cryptService->blockForDebt($contract->crypt,
$monthsOverdue);
                        // Marcar adeudos como "blocked"
                        $contract->debts()
                            ->whereIn('status', ['overdue', 'grace_period'])
                            ->update([
                                'status' => 'blocked',
                                'blocked_at' => now(),
                            ]);
                        $this->auditService->log(
                            action: 'crypt_blocked_for_debt',
                            model: $contract->crypt,
                            description: "Bloqueada por morosidad de
{$monthsOverdue} meses"
                        );
                        $this->notificationService-
>sendDebtBlockNotification($contract);
                    });
                    $stats['blocked']++;
                } catch (\Exception $e) {
                    Log::error("Error bloqueando cripta", [
                        'contract_id' => $contract->id,
                        'error' => $e->getMessage()
```

```
                    ]);
                    $stats['errors']++;
                }
            }
        }
    }
    /**
     * Desbloquear criptas que liquidaron todos sus adeudos
     */
    private function unblockPaidCrypts(array &$stats): void
    {
        $blockedCrypts = \App\Models\Crypt::blocked()->get();
        foreach ($blockedCrypts as $crypt) {
            // Verificar si todos los adeudos están pagados
            $hasPendingDebts = $crypt->debts()
                ->whereIn('status', ['pending', 'overdue', 'grace_period',
'blocked'])
                ->exists();
            if (!$hasPendingDebts) {
                try {
                    DB::transaction(function () use ($crypt) {
                        $this->cryptService->unblockAfterPayment($crypt);
                        $this->auditService->log(
                            action: 'crypt_unblocked_after_payment',
                            model: $crypt,
                            description: "Desbloqueada tras liquidar adeudos"
                        );
                        if ($crypt->activeContract()->exists()) {
                            $this->notificationService-
>sendDebtUnblockNotification($crypt->activeContract->first());
                        }
                    });
                    $stats['unblocked']++;
                } catch (\Exception $e) {
                    Log::error("Error desbloqueando cripta", [
                        'crypt_id' => $crypt->id,
                        'error' => $e->getMessage()
                    ]);
                    $stats['errors']++;
                }
            }
        }
    }
}
```
```

```
### 6.4 WorkOrderService (RN-06): `app/Services/Operations/WorkOrderService.php`
```php
<?php
namespace App\Services\Operations;
```

```
use App\Models\WorkOrder;
use App\Models\WorkOrderEvidence;
use App\Models\Crypt;
use App\Models\Contract;
use App\Services\Inventory\CryptService;
```

```
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
```

```
class WorkOrderService
{
    public function __construct(
        private CryptService $cryptService,
        private AuditService $auditService
    ) {}
    /**
     * Crear OT de inhumación con validaciones (RN-01, RN-04, RN-06)
     */
    public function createInhumation(array $data): WorkOrder
    {
        $crypt = Crypt::findOrFail($data['crypt_id']);
        // RN-01: Validar capacidad
        if ($crypt->available_capacity < 1) {
            throw new \DomainException("La cripta {$crypt->code} no tiene
capacidad disponible.");
        }
        // RN-04: Validar que no esté bloqueada
        if ($crypt->is_blocked) {
            throw new \DomainException("La cripta {$crypt->code} está bloqueada.
No se puede inhumar.");
        }
        // RN-06: Validar requisitos sanitarios
        $this->validateSanitaryRequirements($data);
        return DB::transaction(function () use ($data, $crypt) {
            $workOrder = WorkOrder::create([
                'tenant_id' => auth()->user()->tenant_id,
                'work_order_type_id' => WorkOrderType::where('code',
'inhumation')->first()->id,
                'crypt_id' => $crypt->id,
                'customer_id' => $data['customer_id'],
                'contract_id' => $data['contract_id'] ?? null,
                'crew_id' => $data['crew_id'] ?? null,
                'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'scheduled_at' => $data['scheduled_at'],
                'sanitary_validated' => true,
                'death_certificate_url' => $data['death_certificate_url'],
                'body_type' => $data['body_type'],
                'coffin_type' => $data['coffin_type'] ?? null,
                'coffin_seal_number' => $data['coffin_seal_number'] ?? null,
                'status' => 'pending',
                'created_by_user_id' => auth()->id(),
            ]);
            $this->auditService->log(
                action: 'create',
                model: $workOrder,
                description: "OT de inhumación creada para cripta {$crypt-
>code}"
            );
            return $workOrder;
        });
```

```
    }
    /**
     * Crear OT de exhumación con validaciones (RN-04)
     */
    public function createExhumation(array $data): WorkOrder
    {
        $crypt = Crypt::findOrFail($data['crypt_id']);
```

```
        // RN-04: Validar que no esté bloqueada (salvo orden judicial)
        if ($crypt->is_blocked && empty($data['judicial_order_url'])) {
            throw new \DomainException("La cripta está bloqueada. Se requiere
orden judicial para exhumar.");
        }
```

```
        return DB::transaction(function () use ($data, $crypt) {
            return WorkOrder::create([
                'tenant_id' => auth()->user()->tenant_id,
                'work_order_type_id' => WorkOrderType::where('code',
'exhumation')->first()->id,
                'crypt_id' => $crypt->id,
                'customer_id' => $data['customer_id'],
                'contract_id' => $data['contract_id'] ?? null,
                'crew_id' => $data['crew_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'scheduled_at' => $data['scheduled_at'],
                'judicial_exception' => !empty($data['judicial_order_url']),
                'judicial_order_url' => $data['judicial_order_url'] ?? null,
                'judicial_notes' => $data['judicial_notes'] ?? null,
                'status' => 'pending',
                'created_by_user_id' => auth()->id(),
            ]);
        });
    }
    /**
     * Crear OT de traslado a osario (para decadencia, RN-03)
     */
    public function createTransferToOssuary(Contract $contract): WorkOrder
    {
        return WorkOrder::create([
            'tenant_id' => $contract->tenant_id,
            'work_order_type_id' => WorkOrderType::where('code', 'transfer')-
>first()->id,
            'crypt_id' => $contract->crypt_id,
            'customer_id' => $contract->customer_id,
            'contract_id' => $contract->id,
            'order_number' => $this->generateOrderNumber(),
            'scheduled_at' => now()->addDays(7), // Programar en 7 días
            'observations' => 'Traslado a osario común por proceso de
decadencia',
            'status' => 'pending',
            'created_by_user_id' => null, // Sistema
        ]);
    }
    /**
     * Completar OT con evidencia (RN-06)
     */
    public function complete(WorkOrder $workOrder, array $evidenceData): void
    {
        $workOrderType = $workOrder->workOrderType;
```

```
        // RN-06: Validar que tenga mínimo de fotos requeridas
```

```
        $photoCount = count($evidenceData['photos'] ?? []);
        if ($photoCount < $workOrderType->min_photos) {
            throw new \DomainException(
                "Se requieren mínimo {$workOrderType->min_photos} fotos. Solo se
proporcionaron {$photoCount}."
            );
        }
        // RN-06: Validar firma si es requerida
        if ($workOrderType->requires_family_signature &&
empty($evidenceData['signature'])) {
            throw new \DomainException("Se requiere firma de conformidad.");
        }
        DB::transaction(function () use ($workOrder, $evidenceData) {
            // Subir fotos a Object Storage
            foreach ($evidenceData['photos'] as $photo) {
                $this->uploadEvidence($workOrder, $photo, 'photo');
            }
            // Subir firma
            if (!empty($evidenceData['signature'])) {
                $signatureUrl = $this->uploadSignature($workOrder,
$evidenceData['signature']);
                $workOrder->update([
                    'signature_url' => $signatureUrl,
                    'signature_hash' => hash('sha256',
$evidenceData['signature']),
                    'signature_ip' => request()->ip(),
                    'signature_at' => now(),
                ]);
            }
            // Si es inhumación, incrementar ocupación de la cripta
            if ($workOrder->workOrderType->code === 'inhumation') {
                $this->cryptService->occupy($workOrder->crypt);
                $workOrder->crypt->incrementOccupancy();
            }
            // Si es exhumación, decrementar ocupación
            if ($workOrder->workOrderType->code === 'exhumation') {
                $workOrder->crypt->decrementOccupancy();
            }
            // Marcar OT como completada
            $workOrder->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by_user_id' => auth()->id(),
                'observations' => $evidenceData['observations'] ?? null,
            ]);
            $this->auditService->log(
                action: 'complete',
                model: $workOrder,
                description: "OT completada con {$photoCount} fotos y firma"
            );
        });
    }
    /**
     * Validar requisitos sanitarios (RN-06)
     */
```

```
    private function validateSanitaryRequirements(array $data): void
    {
        if (empty($data['death_certificate_url'])) {
            throw new \DomainException("Se requiere certificado de defunción
para inhumación.");
        }
        if (empty($data['body_type'])) {
            throw new \DomainException("Se debe especificar el tipo de restos
(cadáver o urna).");
        }
        if ($data['body_type'] === 'corpse' && empty($data['coffin_type'])) {
            throw new \DomainException("Se debe especificar el tipo de ataúd.");
        }
    }
    /**
     * Subir evidencia a Object Storage
     */
    private function uploadEvidence(WorkOrder $workOrder, $file, string $type):
WorkOrderEvidence
    {
        $path = $file->store("work-orders/{$workOrder->id}/evidences", 's3');
```

```
        return WorkOrderEvidence::create([
            'work_order_id' => $workOrder->id,
            'tenant_id' => $workOrder->tenant_id,
            'type' => $type,
            'file_url' => Storage::disk('s3')->url($path),
            'file_hash' => hash_file('sha256', $file->path()),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'metadata' => $this->extractExifData($file),
            'taken_at' => now(),
            'uploaded_by_user_id' => auth()->id(),
        ]);
    }
    /**
     * Subir firma digital
     */
    private function uploadSignature(WorkOrder $workOrder, string
$signatureBase64): string
    {
        $image = str_replace('data:image/png;base64,', '', $signatureBase64);
        $image = str_replace(' ', '+', $image);
        $imageName = "signature_{$workOrder->id}_" . Str::random(10) . '.png';
        $path = "work-orders/{$workOrder->id}/signatures/{$imageName}";
        Storage::disk('s3')->put($path, base64_decode($image), 'public');
        return Storage::disk('s3')->url($path);
    }
    /**
     * Extraer metadata EXIF de la foto
     */
    private function extractExifData($file): array
    {
        // Implementación simplificada - en producción usar librería exif
        return [
            'width' => null,
            'height' => null,
```

```
            'camera' => null,
            'gps' => null,
        ];
    }
    /**
     * Generar número de OT único por tenant
     */
    private function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $last =
WorkOrder::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereYear('created_at', $year)
            ->max('id') ?? 0;
        return sprintf('OT-%s-%05d', $year, $last + 1);
    }
}
```
### 6.5 AuditService (RN-07): `app/Services/Audit/AuditService.php`
```php
<?php
namespace App\Services\Audit;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
class AuditService
{
    /**
     * Registrar evento de auditoría (RN-07)
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
    /**
     * Consultar historial de un modelo específico
```

```
     */
    public function getHistory(Model $model, int $limit = 50):
\Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::forModel(get_class($model), $model->getKey())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    /**
     * Consultar auditoría por usuario
     */
    public function getByUser(int $userId, int $limit = 100):
\Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    /**
     * Exportar auditoría a Excel
     */
    public function exportToExcel(array $filters):
\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $logs = AuditLog::query();
        if (!empty($filters['from'])) {
            $logs->where('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $logs->where('created_at', '<=', $filters['to']);
        }
        if (!empty($filters['user_id'])) {
            $logs->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['action'])) {
            $logs->where('action', $filters['action']);
        }
        $logs = $logs->orderBy('created_at', 'desc')->get();
        // Usar Laravel Excel para exportar
        return \Excel::download(new \App\Exports\AuditLogExport($logs),
'audit_logs_' . now()->format('Y-m-d') . '.xlsx');
    }
}
```
---
```

`## 7.` 🎮 `CONTROLADORES Y RUTAS ### 7.1 Controlador de Criptas: `app/Http/Controllers/Inventory/CryptController.php` ```php <?php namespace App\Http\Controllers\Inventory;` 

```
use App\Http\Controllers\Controller;
use App\Models\Crypt;
use App\Http\Requests\Inventory\StoreCryptRequest;
use App\Http\Requests\Inventory\UpdateCryptRequest;
use App\Services\Inventory\CryptService;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
class CryptController extends Controller
{
    public function __construct(
        private CryptService $cryptService,
        private AuditService $auditService
    ) {
        $this->middleware('permission:view-crypts')->only(['index', 'show',
'map']);
        $this->middleware('permission:create-crypts')->only(['create',
'store']);
        $this->middleware('permission:edit-crypts')->only(['edit', 'update']);
        $this->middleware('permission:delete-crypts')->only(['destroy']);
    }
    public function index(Request $request)
    {
        $query = Crypt::with(['cryptStatus', 'cryptType',
'level.block.section']);
```

```
        // Filtros
        if ($request->filled('status')) {
            $query->whereHas('cryptStatus', fn($q) => $q->where('code',
$request->status));
        }
        if ($request->filled('type')) {
            $query->whereHas('cryptType', fn($q) => $q->where('code', $request-
>type));
        }
        if ($request->filled('section')) {
            $query->whereHas('level.block.section', fn($q) => $q->where('code',
$request->section));
        }
        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        if ($request->boolean('available_only')) {
            $query->available();
        }
        if ($request->boolean('blocked_only')) {
            $query->blocked();
        }
        $crypts = $query->orderBy('code')->paginate(20);
        return view('inventory.crypts.index', compact('crypts'));
    }
    public function map()
    {
        $mapData = $this->cryptService->getMapData();
        $statuses = \App\Models\CryptStatus::orderBy('order')->get();
        return view('inventory.crypts.map', compact('mapData', 'statuses'));
    }
```

```
    public function show(Crypt $crypt)
```

```
    {
        $crypt->load([
            'cryptStatus',
            'cryptType',
            'level.block.section',
            'contracts.customer',
            'contracts.contractType',
            'workOrders' => fn($q) => $q->orderBy('scheduled_at', 'desc')-
>limit(10),
            'debts' => fn($q) => $q->orderBy('due_date', 'desc')->limit(10),
            'documents',
        ]);
        $history = $this->auditService->getHistory($crypt);
        return view('inventory.crypts.show', compact('crypt', 'history'));
    }
    public function store(StoreCryptRequest $request)
    {
        $crypt = Crypt::create($request->validated());
        return redirect()
            ->route('inventory.crypts.show', $crypt)
            ->with('success', "Cripta {$crypt->code} creada exitosamente");
    }
    public function update(UpdateCryptRequest $request, Crypt $crypt)
    {
        $crypt->update($request->validated());
        return redirect()
            ->route('inventory.crypts.show', $crypt)
            ->with('success', "Cripta {$crypt->code} actualizada exitosamente");
    }
    public function destroy(Crypt $crypt)
    {
        // Validar que no tenga contratos activos
        if ($crypt->contracts()->active()->exists()) {
            return back()->with('error', 'No se puede eliminar una cripta con
contratos activos.');
        }
        $crypt->delete();
        return redirect()
            ->route('inventory.crypts.index')
            ->with('success', 'Cripta eliminada exitosamente');
    }
}
```
### 7.2 Controlador de Contratos:
`app/Http/Controllers/Commercial/ContractController.php`
```php
<?php
namespace App\Http\Controllers\Commercial;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Http\Requests\Commercial\StoreContractRequest;
```

```
use App\Services\Commercial\ContractService;
use App\Services\Inventory\CryptService;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
```

```
class ContractController extends Controller
{
    public function __construct(
        private ContractService $contractService,
        private CryptService $cryptService,
        private AuditService $auditService
    ) {
        $this->middleware('permission:view-contracts')->only(['index', 'show']);
        $this->middleware('permission:create-contracts')->only(['create',
'store']);
        $this->middleware('permission:sign-contracts')->only(['sign']);
    }
    public function store(StoreContractRequest $request)
    {
        $contract = $this->contractService->create($request->validated());
        return redirect()
            ->route('commercial.contracts.show', $contract)
            ->with('success', "Contrato {$contract->contract_number} creado
exitosamente");
    }
    public function sign(Request $request, Contract $contract)
    {
        $request->validate([
            'signature_image' => 'required|string', // Base64
        ]);
        $this->contractService->sign($contract, $request->signature_image);
        return redirect()
            ->route('commercial.contracts.show', $contract)
            ->with('success', 'Contrato firmado exitosamente');
    }
}
```
```

```
### 7.3 Controlador PWA para Campo:
`app/Http/Controllers/Operations/FieldController.php`
```php
<?php
```

```
namespace App\Http\Controllers\Operations;
```

```
use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\Operations\WorkOrderService;
use Illuminate\Http\Request;
```

```
class FieldController extends Controller
{
    public function __construct(
        private WorkOrderService $workOrderService
    ) {
        $this->middleware('role:operativo|supervisor');
    }
```

```
    public function index()
    {
        $pendingOrders = WorkOrder::where('assigned_to_user_id', auth()->id())
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->orderBy('scheduled_at')
            ->get();
        $completedToday = WorkOrder::where('assigned_to_user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();
        return view('operations.field.index', compact('pendingOrders',
'completedToday'));
    }
    public function execute(WorkOrder $workOrder)
    {
        // Validar que la OT esté asignada al usuario actual
        if ($workOrder->assigned_to_user_id !== auth()->id()) {
            abort(403, 'Esta OT no está asignada a usted.');
        }
        $workOrder->load(['crypt', 'customer', 'workOrderType']);
        return view('operations.field.execute', compact('workOrder'));
    }
    public function complete(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:5120', // 5MB max
            'signature' => 'nullable|string',
            'observations' => 'nullable|string|max:1000',
        ]);
        $this->workOrderService->complete($workOrder, $request->all());
        return response()->json([
            'success' => true,
            'message' => 'OT completada exitosamente',
            'work_order' => $workOrder->fresh(),
        ]);
    }
}
```
```

```
### 7.4 Controlador API para Sync PWA:
`app/Http/Controllers/Api/SyncController.php`
```php
<?php
namespace App\Http\Controllers\Api;
```

```
use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\Operations\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SyncController extends Controller
{
```

```
    public function __construct(
        private WorkOrderService $workOrderService
    ) {
        $this->middleware('auth:sanctum');
    }
    /**
     * Descargar OT pendientes para trabajo offline
     */
    public function download(Request $request)
    {
        $lastSync = $request->input('last_sync');
        $query = WorkOrder::where('assigned_to_user_id', auth()->id())
            ->whereIn('status', ['pending', 'assigned', 'in_progress']);
        if ($lastSync) {
            $query->where('updated_at', '>', $lastSync);
        }
        $orders = $query->with(['crypt.cryptStatus', 'crypt.cryptType',
'customer', 'workOrderType'])->get();
        return response()->json([
            'work_orders' => $orders,
            'synced_at' => now()->toIso8601String(),
        ]);
    }
    /**
     * Subir datos de OT completadas offline
     */
    public function upload(Request $request)
    {
        $request->validate([
            'work_orders' => 'required|array',
            'work_orders.*.offline_id' => 'required|uuid',
            'work_orders.*.status' => 'required|in:completed,failed',
            'work_orders.*.photos' => 'nullable|array',
            'work_orders.*.signature' => 'nullable|string',
            'work_orders.*.observations' => 'nullable|string',
        ]);
        $results = [];
        foreach ($request->work_orders as $data) {
            try {
                DB::transaction(function () use ($data, &$results) {
                    // Buscar OT por offline_id o server_id
                    $workOrder = WorkOrder::where('offline_id',
$data['offline_id'])
                        ->orWhere('id', $data['server_id'] ?? null)
                        ->firstOrFail();
                    // Verificar conflictos (si la OT fue modificada en
servidor)
                    if ($workOrder->updated_at > $data['client_updated_at']) {
                        $results[] = [
                            'offline_id' => $data['offline_id'],
                            'status' => 'conflict',
                            'message' => 'Conflicto: la OT fue modificada en el
servidor',
                        ];
                        return;
```

```
                    }
                    // Completar OT
                    if ($data['status'] === 'completed') {
                        $this->workOrderService->complete($workOrder, [
                            'photos' => $data['photos'] ?? [],
                            'signature' => $data['signature'] ?? null,
                            'observations' => $data['observations'] ?? null,
                        ]);
                    }
                    $results[] = [
                        'offline_id' => $data['offline_id'],
                        'status' => 'synced',
                        'server_id' => $workOrder->id,
                    ];
                });
            } catch (\Exception $e) {
                $results[] = [
                    'offline_id' => $data['offline_id'],
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }
        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'results' => $results,
        ]);
    }
}
```
```

```
### 7.5 Rutas: `routes/web.php`
```

```
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inventory\{CryptController, SectionController,
BlockController, LevelController};
use App\Http\Controllers\Commercial\{CustomerController, ContractController,
ReservationController, HeirController};
use App\Http\Controllers\Financial\{PaymentController, InvoiceController,
DebtController};
use App\Http\Controllers\Operations\{WorkOrderController, CrewController,
FieldController};
use App\Http\Controllers\Audit\AuditLogController;
use App\Http\Controllers\Admin\{TenantController, UserController,
SettingsController};
// Rutas públicas
Route::get('/', fn() => redirect()->route('login'));
// Rutas de autenticación (Breeze)
require __DIR__.'/auth.php';
// Rutas protegidas
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])-
```

```
>name('dashboard');
```

```
    // Inventario
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('crypts/map', [CryptController::class, 'map'])-
>name('crypts.map');
        Route::resource('crypts', CryptController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('blocks', BlockController::class);
        Route::resource('levels', LevelController::class);
    });
    // Comercial
    Route::prefix('commercial')->name('commercial.')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('contracts', ContractController::class);
        Route::post('contracts/{contract}/sign', [ContractController::class,
'sign'])->name('contracts.sign');
        Route::resource('reservations', ReservationController::class);
        Route::resource('heirs', HeirController::class);
    });
    // Financiero
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::resource('payments', PaymentController::class);
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class,
'cancel'])->name('invoices.cancel');
        Route::get('debts', [DebtController::class, 'index'])-
>name('debts.index');
        Route::get('debts/report', [DebtController::class, 'report'])-
>name('debts.report');
    });
```

```
    // Operaciones
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::resource('work-orders', WorkOrderController::class);
        Route::post('work-orders/{workOrder}/complete',
[WorkOrderController::class, 'complete'])->name('work-orders.complete');
        Route::resource('crews', CrewController::class);
```

```
        // PWA para campo
        Route::prefix('field')->name('field.')->group(function () {
            Route::get('/', [FieldController::class, 'index'])->name('index');
            Route::get('{workOrder}/execute', [FieldController::class,
'execute'])->name('execute');
            Route::post('{workOrder}/complete', [FieldController::class,
'complete'])->name('complete');
        });
    });
    // Auditoría
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('logs', [AuditLogController::class, 'index'])-
>name('logs.index');
        Route::get('logs/export', [AuditLogController::class, 'export'])-
>name('logs.export');
    });
    // Configuración (solo AdminCementerio)
    Route::prefix('admin')->name('admin.')->middleware('role:admin_cemetery')-
>group(function () {
        Route::get('settings', [SettingsController::class, 'index'])-
>name('settings.index');
```

```
        Route::put('settings', [SettingsController::class, 'update'])-
>name('settings.update');
        Route::resource('users', UserController::class);
    });
});
// Rutas SuperAdmin (gestión de tenants)
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')-
>name('super-admin.')->group(function () {
    Route::resource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/suspend', [TenantController::class,
'suspend'])->name('tenants.suspend');
    Route::post('tenants/{tenant}/activate', [TenantController::class,
'activate'])->name('tenants.activate');
});
```
```

```
### 7.6 Rutas API: `routes/api.php`
```

```
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{SyncController, WebhookController};
// Rutas API para PWA (autenticadas con Sanctum)
Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function
() {
    // Sincronización PWA
    Route::get('sync/download', [SyncController::class, 'download'])-
>name('sync.download');
    Route::post('sync/upload', [SyncController::class, 'upload'])-
>name('sync.upload');
});
// Webhooks (sin autenticación Sanctum, validados por firma)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('mercadopago', [WebhookController::class, 'mercadopago'])-
>name('mercadopago');
    Route::post('stripe', [WebhookController::class, 'stripe'])->name('stripe');
    Route::post('paypal', [WebhookController::class, 'paypal'])->name('paypal');
    Route::post('sat/{tenant}', [WebhookController::class, 'sat'])->name('sat');
});
```
```

```
---
```

```
## 8. 📑 APIs PRINCIPALES
### 8.1 Tabla de Endpoints
```

```
| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| **INVENTARIO** | | | |
| GET | `/inventory/crypts` | Listar criptas con filtros | Breeze |
| GET | `/inventory/crypts/map` | Mapa visual jerárquico | Breeze |
| GET | `/inventory/crypts/{id}` | Detalle de cripta | Breeze |
| POST | `/inventory/crypts` | Crear cripta | Breeze + permission |
| PUT | `/inventory/crypts/{id}` | Actualizar cripta | Breeze + permission |
| DELETE | `/inventory/crypts/{id}` | Eliminar cripta (soft) | Breeze +
permission |
| **COMERCIAL** | | | |
| GET | `/commercial/customers` | Listar clientes | Breeze |
| POST | `/commercial/customers` | Crear cliente | Breeze + permission |
```

```
| GET | `/commercial/contracts` | Listar contratos | Breeze |
| POST | `/commercial/contracts` | Crear contrato | Breeze + permission |
| POST | `/commercial/contracts/{id}/sign` | Firmar contrato | Breeze +
permission |
| **FINANCIERO** | | | |
| GET | `/financial/payments` | Listar pagos | Breeze |
| POST | `/financial/payments` | Registrar pago | Breeze + permission |
| GET | `/financial/invoices` | Listar facturas | Breeze |
| POST | `/financial/invoices` | Emitir CFDI | Breeze + permission |
| POST | `/financial/invoices/{id}/cancel` | Cancelar CFDI | Breeze + permission
|
| GET | `/financial/debts` | Listar adeudos | Breeze |
| **OPERACIONES** | | | |
| GET | `/operations/work-orders` | Listar OT | Breeze |
| POST | `/operations/work-orders` | Crear OT | Breeze + permission |
| POST | `/operations/work-orders/{id}/complete` | Completar OT | Breeze +
permission |
| **PWA CAMPO** | | | |
| GET | `/operations/field` | Lista de OT del operativo | Breeze + role |
| GET | `/operations/field/{id}/execute` | Vista de ejecución | Breeze + role |
| POST | `/operations/field/{id}/complete` | Completar OT (con evidencia) |
Breeze + role |
| **API PWA SYNC** | | | |
| GET | `/api/v1/sync/download` | Descargar OT para offline | Sanctum |
| POST | `/api/v1/sync/upload` | Subir OT completadas offline | Sanctum |
| **AUDITORÍA** | | | |
| GET | `/audit/logs` | Listar auditoría | Breeze + permission |
| GET | `/audit/logs/export` | Exportar a Excel | Breeze + permission |
| **WEBHOOKS** | | | |
| POST | `/api/webhooks/mercadopago` | Webhook MercadoPago | Firma |
| POST | `/api/webhooks/stripe` | Webhook Stripe | Firma |
| POST | `/api/webhooks/paypal` | Webhook PayPal | Firma |
| POST | `/api/webhooks/sat/{tenant}` | Webhook SAT PAC | Firma |
```

```
### 8.2 Ejemplo de Request/Response
#### POST `/api/v1/sync/upload` (PWA completa OT offline)
**Request:**
```json
{
  "work_orders": [
    {
      "offline_id": "550e8400-e29b-41d4-a716-446655440000",
      "server_id": 1234,
      "client_updated_at": "2026-07-09T10:30:00Z",
      "status": "completed",
      "photos": [
        "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
        "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
      ],
      "signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
      "observations": "Inhumación realizada sin novedad",
      "gps": {
        "latitude": "19.4326",
        "longitude": "-99.1332"
      },
      "completed_at": "2026-07-09T10:32:15Z"
    }
  ]
}
```
**Response:**
```

```
```json
{
  "synced_at": "2026-07-09T10:35:00Z",
  "results": [
    {
      "offline_id": "550e8400-e29b-41d4-a716-446655440000",
      "status": "synced",
      "server_id": 1234
    }
  ]
}
```
---
```

```
## 9. 📑 ESTRATEGIA DE MULTI-TENANCY
```

```
### 9.1 Implementación Técnica
```

```
#### Middleware de Identificación de Tenant:
`app/Http/Middleware/IdentifyTenant.php`
```php
<?php
namespace App\Http\Middleware;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Identificar tenant por subdominio
        $host = $request->getHost(); // ej: "cementerio-sanjose.s Bic.mx"
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            // Ignorar subdominios reservados
            if (in_array($subdomain, ['www', 'admin', 'api'])) {
                return $next($request);
            }
            $tenant = Tenant::where('subdomain', $subdomain)
                ->where('is_active', true)
                ->first();
            if (!$tenant) {
                abort(404, 'Cementerio no encontrado');
            }
            if (!$tenant->is_active_subscription) {
                abort(402, 'Suscripción vencida. Contacte al administrador.');
            }
```

```
            // Guardar tenant en la request y en config
            $request->attributes->set('tenant', $tenant);
            config(['app.tenant_id' => $tenant->id]);
```

```
            // Compartir con vistas
```

```
            view()->share('currentTenant', $tenant);
        }
```

```
        return $next($request);
    }
}
```
```

```
#### Registro en `bootstrap/app.php` (Laravel 11)
```

```
```php
<?php
```

```
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
```

```
return Application::configure(basePath: dirname(__DIR__))
```

```
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
```

- `->withMiddleware(function (Middleware $middleware) { $middleware->alias([` 

```
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
```

- `'role' => \Spatie\Permission\Middleware\RoleMiddleware::class, 'permission' =>` 

- `\Spatie\Permission\Middleware\PermissionMiddleware::class,` 

- `]);` 

- `$middleware->web(append: [` 

- `\App\Http\Middleware\IdentifyTenant::class,` 

- `]);` 

- `})` 

- `->withExceptions(function (Exceptions $exceptions) {` 

- `//` 

- `})->create(); ```` 

```
### 9.2 Flujo de Multi-tenancy
```

```
```
```

`1. Usuario accede a: https://cementerio-sanjose.s Bic.mx` 

- `↓` 

`2. Middleware IdentifyTenant extrae subdominio: "cementerio-sanjose"` 

- `↓` 

`3. Busca Tenant en BD: SELECT * FROM tenants WHERE subdomain = 'cementeriosanjose'` 

- `↓` 

`4. Valida: is_active = true, subscription válida` 

- `↓` 

`5. Guarda tenant en request y config` 

- `↓` 

`6. Usuario inicia sesión → User tiene tenant_id = 5` 

- `↓` 

`7. Todas las consultas Eloquent aplican TenantScope automáticamente: WHERE tenant_id = 5` 

- `↓` 

`8. Usuario solo ve datos de su cementerio` 

- ````` 

```
### 9.3 Aislamiento de Datos
```

```
| Capa | Mecanismo | Garantía |
```

```
|------|-----------|----------|
```

```
| **Query Builder** | Global Scope `TenantScope` | Todas las consultas filtran
por `tenant_id` |
```

```
| **Creación** | Observer en `BelongsToTenant` trait | Asigna `tenant_id`
automáticamente |
```

```
| **Actualización** | Global Scope | Solo puede actualizar datos de su tenant |
```

```
| **Eliminación** | Global Scope | Solo puede eliminar datos de su tenant |
```

```
| **SuperAdmin** | Bypass de TenantScope | Ve todos los tenants (con cuidado) |
```

```
| **APIs** | Middleware + Sanctum | Token vinculado a tenant específico |
```

```
| **Jobs/Queues** | Tenant en payload | Jobs procesan solo datos de su tenant |
```

```
---
```

## `## 10. 📑 SEGURIDAD` 

```
### 10.1 Autenticación
```

- `**Web (Admin/Operativo):** Laravel Breeze (session-based, cookies httpOnly) - **API/PWA:** Laravel Sanctum (tokens personales)` 

```
- **Familias (V1.0):** Sanctum con tokens de corta duración
```

```
### 10.2 Autorización (RBAC con Spatie)
```

```
```php
// Roles predefinidos
$roles = [
    'super_admin' => 'Administrador del SaaS (proveedor)',
    'admin_cemetery' => 'Administrador del cementerio (cliente)',
    'admin' => 'Administrativo (ventas, cobranza)',
    'operativo' => 'Operativo de campo (sepulturero)',
    'consulta' => 'Solo lectura',
];
// Permisos granulares
$permissions = [
    // Inventario
    'view-crypts', 'create-crypts', 'edit-crypts', 'delete-crypts',
    'view-map', 'export-inventory',
    // Comercial
    'view-customers', 'create-customers', 'edit-customers',
    'view-contracts', 'create-contracts', 'sign-contracts',
    // Financiero
    'view-payments', 'create-payments',
    'view-invoices', 'create-invoices', 'cancel-invoices',
    'view-debts',
    // Operaciones
    'view-work-orders', 'create-work-orders', 'complete-work-orders',
    'assign-crews',
    // Auditoría
    'view-audit-logs', 'export-audit-logs',
    // Configuración
    'manage-settings', 'manage-users',
];
```
```

```
### 10.3 Cifrado de Datos Sensibles
```

```
```php
// app/Models/Customer.php
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
class Customer extends Model
{
    // RFC y CURP cifrados en BD
    protected $casts = [
        'rfc_encrypted' => 'encrypted',
        'curp_encrypted' => 'encrypted',
    ];
    // Hash para búsqueda (no reversible)
    public function setRfcAttribute($value)
    {
        $this->attributes['rfc_encrypted'] = Crypt::encryptString($value);
        $this->attributes['rfc_hash'] = hash('sha256', $value .
config('app.key'));
    }
    public function getRfcAttribute()
    {
        return Crypt::decryptString($this->attributes['rfc_encrypted']);
    }
    // Búsqueda por RFC
    public function scopeByRfc($query, string $rfc)
    {
        $hash = hash('sha256', $rfc . config('app.key'));
        return $query->where('rfc_hash', $hash);
    }
}
```
```

```
### 10.4 Object Storage Seguro
```

```
```php
// URLs firmadas con expiración
Storage::disk('s3')->temporaryUrl(
    'contracts/contract_1234_signed.pdf',
    now()->addMinutes(15)
);
// Políticas de bucket S3
// - No público
// - Solo acceso vía URLs firmadas
// - Cifrado en reposo (AES-256)
// - Versionado habilitado
// - Lifecycle: mover a Glacier después de 1 año
```
```

```
### 10.5 Headers de Seguridad
```

```
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-
```

```
origin');
```

```
    $response->headers->set('Permissions-Policy', 'camera=(self),
geolocation=(self)');
    $response->headers->set('Content-Security-Policy', "default-src 'self';
script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src
'self' data: https:;");
```

```
    if (app()->environment('production')) {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000;
includeSubDomains');
    }
```

```
    return $response;
}
```
```

```
### 10.6 Cumplimiento LFPDPPP
```

- `📑 Aviso de privacidad accesible en login y registro` 

- `📑 Consentimiento explícito para datos sensibles (RFC, CURP, datos de difuntos)` 

- `📑 Derecho de acceso, rectificación, cancelación y oposición (ARCO)` 

- `📑 Retención de datos según normativa (mínimo 5 años para datos fiscales)` 

- `📑 Cifrado en reposo y tránsito` 

- `📑 Bitácora de acceso a datos sensibles (audit_logs)` 

```
---
```

```
## 11. 📑 ESTRATEGIA DE TESTING
```

```
### 11.1 Pirámide de Testing
```

```
```
```

**==> picture [373 x 118] intentionally omitted <==**

```
### 11.2 Pruebas Unitarias (Ejemplo)
```

```
```php
```

```
// tests/Unit/Services/CryptServiceTest.php
use App\Models\Crypt;
use App\Models\CryptStatus;
use App\Services\Inventory\CryptService;
use App\Exceptions\CryptNotAvailableException;
```

```
test('valida que cripta disponible puede venderse', function () {
    $crypt = Crypt::factory()->create([
```

```
        'crypt_status_id' => CryptStatus::where('code', 'available')->first()-
>id,
```

```
        'is_blocked' => false,
```

```
        'current_occupancy' => 0,
```

```
    ]);
```

```
    $service = app(CryptService::class);
```

```
    $service->validateForSale($crypt); // No debe lanzar excepción
```

```
    expect(true)->toBeTrue();
});
test('lanza excepción si cripta está ocupada', function () {
    $crypt = Crypt::factory()->create([
        'crypt_status_id' => CryptStatus::where('code', 'occupied')->first()-
>id,
    ]);
    $service = app(CryptService::class);
    $service->validateForSale($crypt);
})->throws(CryptNotAvailableException::class);
test('lanza excepción si cripta está bloqueada', function () {
    $crypt = Crypt::factory()->create([
        'crypt_status_id' => CryptStatus::where('code', 'available')->first()-
>id,
        'is_blocked' => true,
        'blocked_reason' => 'Morosidad',
    ]);
    $service = app(CryptService::class);
    $service->validateForSale($crypt);
})->throws(CryptNotAvailableException::class);
test('incrementa ocupación correctamente', function () {
    $crypt = Crypt::factory()->create([
        'capacity' => 4,
        'current_occupancy' => 2,
    ]);
    $crypt->incrementOccupancy();
    expect($crypt->fresh()->current_occupancy)->toBe(3);
});
test('no permite incrementar si está llena', function () {
    $crypt = Crypt::factory()->create([
        'capacity' => 2,
        'current_occupancy' => 2,
    ]);
    $crypt->incrementOccupancy();
})->throws(DomainException::class);
```
```

```
### 11.3 Pruebas de Reglas de Negocio
```php
// tests/Feature/BusinessRulesTest.php
// RN-01: Unicidad y Capacidad
test('RN-01: no se puede vender cripta ocupada', function () {
    $crypt = Crypt::factory()->occupied()->create();
    $contract = Contract::factory()->make(['crypt_id' => $crypt->id]);
    $this->post(route('commercial.contracts.store'), $contract->toArray())
        ->assertSessionHasErrors('crypt_id');
});
```

```
// RN-03: Decadencia
test('RN-03: contrato temporal vencido entra en periodo de gracia', function ()
```

```
{
    $contract = Contract::factory()
        ->temporary()
        ->expired(30) // Vencido hace 30 días
        ->create();
    $service = app(DecayService::class);
    $service->processExpiredContracts();
    expect($contract->fresh()->status)->toBe('grace_period');
    expect($contract->fresh()->grace_period_ends_at)->not->toBeNull();
});
// RN-04: Bloqueo por morosidad
test('RN-04: cripta se bloquea tras X meses de morosidad', function () {
    $tenant = Tenant::factory()->create(['debt_months_to_block' => 3]);
    $contract = Contract::factory()->for($tenant)->create();
    Debt::factory()->for($contract)->overdue(4)->create(); // 4 meses de atraso
    $service = app(DebtBlockingService::class);
    $service->processMoratorium();
    expect($contract->fresh()->crypt->is_blocked)->toBeTrue();
    expect($contract->fresh()->crypt->cryptStatus->code)->toBe('blocked_debt');
});
// RN-06: Validación sanitaria
test('RN-06: inhumación requiere certificado de defunción', function () {
    $crypt = Crypt::factory()->available()->create();
    $response = $this->post(route('operations.work-orders.store'), [
        'type' => 'inhumation',
        'crypt_id' => $crypt->id,
        // Falta death_certificate_url
    ]);
    $response->assertSessionHasErrors('death_certificate_url');
});
// RN-07: Auditoría inmutable
test('RN-07: audit_logs no se puede actualizar', function () {
    $log = AuditLog::factory()->create();
    expect(fn() => $log->update(['description' => 'hack']))
        ->toThrow(DomainException::class);
});
test('RN-07: audit_logs no se puede eliminar', function () {
    $log = AuditLog::factory()->create();
    expect(fn() => $log->delete())
        ->toThrow(DomainException::class);
});
```
```

```
### 11.4 Pruebas de Multi-tenancy
```php
// tests/Feature/MultiTenancyTest.php
test('usuario solo ve datos de su tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
```

```
    $crypt1 = Crypt::factory()->for($tenant1)->create(['code' => 'A-001']);
    $crypt2 = Crypt::factory()->for($tenant2)->create(['code' => 'B-001']);
    $user1 = User::factory()->for($tenant1)->create();
```

```
    $this->actingAs($user1)
        ->get(route('inventory.crypts.index'))
        ->assertSee('A-001')
        ->assertDontSee('B-001'); // No debe ver criptas de otro tenant
});
test('super_admin puede ver todos los tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    Crypt::factory()->for($tenant1)->create(['code' => 'A-001']);
    Crypt::factory()->for($tenant2)->create(['code' => 'B-001']);
    $superAdmin = User::factory()->create()->assignRole('super_admin');
    $this->actingAs($superAdmin)
        ->get(route('inventory.crypts.index'))
        ->assertSee('A-001')
        ->assertSee('B-001');
});
```
```

```
### 11.5 Cobertura Objetivo
```

```
| Componente | Cobertura Mínima |
|------------|------------------|
| Services (lógica de negocio) | 90% |
| Models (scopes, accessors) | 80% |
| Controllers | 70% |
| Reglas de Negocio (RN-01 a RN-07) | 100% |
| Multi-tenancy | 100% |
| APIs | 75% |
```

```
---
```

```
## 12. 📑 DESPLIEGUE Y OPERACIÓN
```

```
### 12.1 Arquitectura de Despliegue
```

```
```
```

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLOUDFLARE (CDN + DNS)                        │
│            SSL/TLS, DDoS Protection, WAF                        │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              LARAVEL FORGE (Orquestación)                        │
│    Auto-deploy desde GitHub, zero-downtime, SSL automático      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│           DIGITALOCEAN DROPLET (Producción)                      │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Docker Containers:                                       │  │
│  │  • Nginx (reverse proxy)                                  │  │
│  │  • PHP 8.2-FPM (Laravel app)                              │  │
│  │  • Redis (cache + queues)                                 │  │
```

```
│  │  • Laravel Horizon (queue worker)                         │  │
│  │  • Laravel Scheduler (cron jobs)                          │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────────┬────────────────────────────────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
        ┌──────────┐  ┌──────────┐  ┌──────────┐
        │ MySQL 8  │  │ AWS S3   │  │ Sentry   │
        │ (Managed │  │ / R2     │  │ (Errors) │
        │  DB)     │  │ (Files)  │  │          │
        └──────────┘  └──────────┘  └──────────┘
```
```

```
### 12.2 Variables de Entorno (`.env.production`)
```

```
```bash
# App
APP_NAME="SGIC 2.0"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://sgic.mx
```

```
# Multi-tenancy
APP_DOMAIN=sgic.mx
APP_WILDCARD_DNS=true
```

```
# Database (Managed MySQL)
DB_CONNECTION=mysql
DB_HOST=db-mysql-nyc3-12345.do-db.com
DB_PORT=3306
DB_DATABASE=sgic_production
DB_USERNAME=sgic_user
DB_PASSWORD=...
```

```
# Redis
REDIS_HOST=redis-nyc3-12345.do-db.com
REDIS_PORT=6379
REDIS_PASSWORD=...
```

```
# Queue
QUEUE_CONNECTION=redis
QUEUE_RETRY_AFTER=90
```

```
# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

```
# Mail (SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@sgic.mx
MAIL_FROM_NAME="SGIC 2.0"
```

```
# Object Storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=sgic-production
```

```
AWS_USE_PATH_STYLE_ENDPOINT=false
```

```
# SAT CFDI (Facturama)
SAT_PAC_PROVIDER=facturama
SAT_PAC_USER=...
SAT_PAC_PASSWORD=...
SAT_PAC_BRANCH_ID=...
SAT_CSD_CERTIFICATE_PATH=/path/to/CSD.cer
SAT_CSD_KEY_PATH=/path/to/CSD.key
SAT_CSD_KEY_PASSWORD=...
```

```
# Pasarelas de Pago
MERCADOPAGO_PUBLIC_KEY=...
MERCADOPAGO_ACCESS_TOKEN=...
STRIPE_PUBLIC_KEY=...
STRIPE_SECRET_KEY=...
STRIPE_WEBHOOK_SECRET=...
```

```
# Notificaciones
SENDGRID_API_KEY=...
TWILIO_SID=...
TWILIO_AUTH_TOKEN=...
TWILIO_WHATSAPP_NUMBER=...
```

```
# Monitoreo
SENTRY_LARAVEL_DSN=...
UPTIMEROBOT_API_KEY=...
```

```
# Horizon
HORIZON_MAX_PROCESSES=10
HORIZON_BALANCE=max
```
```

```
### 12.3 Comandos de Despliegue
```

```
```bash
# Deploy script (Laravel Forge)
cd /home/forge/sgic.mx
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
php artisan horizon:terminate && php artisan horizon
sudo -u forge bash
```

