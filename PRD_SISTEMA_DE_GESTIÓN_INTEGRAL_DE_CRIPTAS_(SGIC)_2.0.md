```
PRD — SISTEMA DE GESTIÓN INTEGRAL DE CRIPTAS (SGIC) 2.0
```

```
**PRODUCT REQUIREMENTS DOCUMENT**
```

```
**Versión:** 1.0 | **Fecha:** 09 de Julio, 2026 | **Autor:** Product Manager
Senior
```

```
**Stack:** PHP Laravel 11 + MySQL 8 + Blade + Livewire + Tailwind CSS
**Arquitectura:** SaaS Multi-tenant (Single DB + `tenant_id`)
```

```
---
```

```
## 📑 ÍNDICE
```

`1. Resumen Ejecutivo` 

`2. Usuarios y Personas` 

`3. Requisitos Funcionales (Epics + User Stories)` 

`4. Requisitos No Funcionales` 

`5. Diseño de Interfaz (Wireframes Textuales)` 

`6. Flujos de Proceso` 

`7. Métricas y Analytics` 

`8. Criterios de Lanzamiento` 

`9. Roadmap y Fases` 

`10. Apéndices` 

```
---
```

## `## 1. 📑 RESUMEN EJECUTIVO` 

```
### 1.1 Visión del Producto
```

```
**SGIC 2.0** es una plataforma SaaS B2B que digitaliza integralmente la
administración de cementerios y complejos funerarios en México, reemplazando los
procesos manuales (libros físicos, Excel, archivos muertos) por un ecosistema
digital que garantiza la trazabilidad legal, sanitaria y financiera de cada
espacio (criptas, nichos, mausoleos y osarios), optimizando la ocupación,
mejorando la experiencia de las familias y asegurando el cumplimiento normativo
federal y municipal.
```

## `### 1.2 Objetivos` 

```
- **OBJ-01:** Centralizar el 100% del inventario físico, contratos y datos de
clientes en una "fuente única de verdad" accesible desde cualquier dispositivo.
- **OBJ-02:** Reducir en 70% el tiempo administrativo dedicado a búsqueda de
expedientes y conciliación de cobros.
```

```
- **OBJ-03:** Disminuir en 40% la cartera vencida de cuotas de mantenimiento
mediante automatización de recordatorios y bloqueo por morosidad.
```

```
- **OBJ-04:** Garantizar el cumplimiento de las 7 Reglas de Negocio críticas
(RN-01 a RN-07) y la normativa sanitaria/fiscal mexicana (NOM-013, NOM-133, CFDI
4.0).
```

```
- **OBJ-05:** Habilitar operaciones de campo con evidencia fotográfica y firma
digital, incluso sin conectividad (offline-first).
```

## `### 1.3 Éxito del Producto` 

```
| KPI | Meta | Instrumento de Medición |
```

```
|-----|------|-------------------------|
```

```
| Reducción tiempo administrativo | -70% | Time-tracking de búsquedas de
expedientes |
```

```
| Aumento tasa de ocupación | +15% | Reporte de inventario vs. línea base |
```

```
| Reducción de morosidad | -40% | Aging de cartera (días de atraso) |
```

```
| Digitalización de contratos | 100% | % de expedientes con contrato digital |
```

- `| NPS portal familias (V1.0) | >70 | Encuesta in-app post-MVP |` 

- `| Uptime SaaS | ≥99.5% | Monitoreo Sentry/UptimeRobot |` 

- `| Tiempo de carga promedio | <2s | Lighthouse + Laravel Telescope |` 

```
---
```

```
## 2. 📑 USUARIOS Y PERSONAS
```

```
### 2.1 User Persona 1: "Don Roberto" — Administrador del Cementerio
```

```
| Atributo | Descripción |
```

```
|----------|-------------|
```

```
| **Rol** | Dueño / Director General del cementerio (cliente del SaaS) |
| **Edad** | 52 años |
```

```
| **Nivel técnico** | Bajo-Medio (usa WhatsApp, Excel básico, correo) |
| **Contexto de uso** | Oficina administrativa, desktop, uso diario |
| **Objetivos** | Tener control total del negocio, reducir morosidad, cumplir
normativas, tomar decisiones con datos |
```

```
| **Frustraciones** | No sabe cuántas criptas están libres realmente, pierde
tiempo conciliando cobros, teme auditorías sanitarias |
```

```
| **Acceso a** | Todos los módulos, dashboard ejecutivo, configuración del
tenant |
```

```
### 2.2 User Persona 2: "María" — Administrativa de Ventas y Cobranza
```

```
| Atributo | Descripción |
```

- `|----------|-------------|` 

```
| **Rol** | Personal administrativo (ventas, atención a familias, cobranza) |
| **Edad** | 34 años |
```

```
| **Nivel técnico** | Medio (maneja sistemas, Excel avanzado) |
```

```
| **Contexto de uso** | Oficina, desktop, uso intensivo durante jornada |
| **Objetivos** | Vender criptas rápido, emitir facturas SAT sin errores,
mantener cartera al día |
| **Frustraciones** | Busca expedientes físicos, olvida cobrar mantenimientos,
duplica información |
```

```
| **Acceso a** | Inventario, Clientes, Contratos, Pagos, Facturación |
```

```
### 2.3 User Persona 3: "Juan" — Operativo de Campo (Sepulturero)
```

```
| Atributo | Descripción |
```

```
|----------|-------------|
```

```
| **Rol** | Jefe de cuadrilla / Sepulturero |
```

```
| **Edad** | 45 años |
```

```
| **Nivel técnico** | Bajo (usa smartphone básico, WhatsApp) |
```

```
| **Contexto de uso** | Campo (cementerio), smartphone Android, conectividad
intermitente |
| **Objetivos** | Recibir órdenes claras, registrar trabajo con evidencia, no
perder tiempo en papeleo |
| **Frustraciones** | Órdenes en papel que se pierden, tener que volver a la
oficina a reportar, falta de claridad en ubicación |
```

```
| **Acceso a** | PWA de campo (OT asignadas, toma de fotos, firma) |
```

```
### 2.4 User Persona 4: "Ing. García" — SuperAdmin del SaaS (Proveedor)
```

```
| Atributo | Descripción |
```

```
|----------|-------------|
```

```
| **Rol** | Administrador de la plataforma SaaS (yo / equipo interno) |
| **Edad** | 38 años |
```

```
| **Nivel técnico** | Alto |
| **Contexto de uso** | Oficina, desktop, gestión multi-tenant |
| **Objetivos** | Onboardear nuevos cementerios, monitorear salud del SaaS,
gestionar suscripciones |
| **Frustraciones** | Soporte reactivo, tenants mal configurados, falta de
visibilidad global |
```

```
| **Acceso a** | Gestión de tenants, configuración global, monitoreo, logs del
sistema |
```

```
### 2.5 User Persona 5: "Doña Lupita" — Familia / Titular (Post-MVP, V1.0)
```

```
| Atributo | Descripción |
```

```
|----------|-------------|
```

```
| **Rol** | Titular / Heredero de una cripta |
```

```
| **Edad** | 58 años |
```

```
| **Nivel técnico** | Bajo (usa smartphone, redes sociales) |
| **Contexto de uso** | Hogar, smartphone, uso esporádico |
```

```
| **Objetivos** | Saber dónde está la cripta de su ser querido, pagar
mantenimiento sin ir al cementerio, solicitar servicios |
```

```
| **Frustraciones** | No recuerda ubicación exacta, tiene que ir a pagar en
persona, no sabe cuánto debe |
```

```
| **Acceso a** | Portal de autogestión (consulta, pagos, solicitudes) |
```

```
---
```

`## 3.` 📦 `REQUISITOS FUNCIONALES` 

```
### 📑 EPIC 1: Multi-tenancy, Autenticación y Seguridad (Transversal)
```

```
#### US-1.1: Registro y gestión de tenants (cementerios)
```

```
**COMO** SuperAdmin del SaaS
```

```
**QUIERO** crear y configurar nuevos tenants (cementerios) con sus parámetros
locales
```

```
**PARA** onboardear clientes sin intervención técnica
```

```
**Criterios de Aceptación:**
```

```
- 📑 El SuperAdmin puede crear un tenant con: nombre, RFC, dirección, municipio,
representante legal, plan contratado.
```

- `📑 Al crear un tenant, se genera automáticamente un subdominio único` 

```
(`{tenant}.sgic.mx`) y un usuario AdminCementerio inicial.
```

- `📑 El SuperAdmin puede parametrizar por tenant: periodo de gracia (RN-03), meses de bloqueo por morosidad (RN-04), tasas de interés moratorio.` 

- `📑 El SuperAdmin puede suspender/activar un tenant (ej. por falta de pago de suscripción).` 

```
- 📑 Los datos de un tenant son completamente invisibles para otros tenants
(aislamiento garantizado por Global Scopes).
```

```
#### US-1.2: Autenticación de usuarios internos
```

```
**COMO** usuario administrativo u operativo
```

- `**QUIERO** iniciar sesión de forma segura con mis credenciales` 

- `**PARA** acceder al sistema según mi rol` 

```
**Criterios de Aceptación:**
```

- `📑 Login con email + password (hash bcrypt).` 

- `📑 Recuperación de contraseña vía email con token temporal (15 min).` 

- `📑 Sesión con timeout configurable (default 8 horas).` 

- `📑 Bloqueo tras 5 intentos fallidos (15 minutos).` 

- `📑 Cada usuario pertenece a un tenant y solo ve datos de su tenant.` 

```
#### US-1.3: Roles y permisos granulares (RBAC)
```

```
**COMO** Administrador del Cementerio
```

- `**QUIERO** asignar roles y permisos específicos a cada usuario` 

- `**PARA** controlar qué puede ver y hacer cada persona` 

```
**Criterios de Aceptación:**
```

- `📑 Roles predefinidos: SuperAdmin, AdminCementerio, Administrativo, Operativo, Consulta.` 

- `📑 Permisos granulares por recurso: `crypts.view`, `crypts.create`, `crypts.edit`, `crypts.delete`, `contracts.sign`, `invoices.stamp`, etc.` 

- `📑 El AdminCementerio puede crear roles personalizados.` 

- `📑 Los permisos se aplican por tenant (un rol "Admin" en el Tenant A es` 

```
independiente del Tenant B).
```

- `📑 Implementación vía Spatie Laravel Permission.` 

```
#### US-1.4: Bitácora de auditoría inmutable (RN-07)
```

```
**COMO** Administrador del Cementerio o SuperAdmin
```

```
**QUIERO** que toda acción crítica quede registrada de forma inmutable
```

```
**PARA** cumplir con requisitos legales y de trazabilidad
```

```
**Criterios de Aceptación:**
```

- `📑 Se registran automáticamente: creación/edición/eliminación de criptas, contratos, pagos, OT, cambios de titularidad.` 

```
- 📑 Cada registro incluye: `tenant_id`, `user_id`, `action`
(create/update/delete/restore), `model_type`, `model_id`, `old_values` (JSON),
`new_values` (JSON), `ip_address`, `user_agent`, `timestamp`.
```

- `📑 La tabla `audit_logs` **NO permite UPDATE ni DELETE** a nivel de BD (trigger o política).` 

- `📑 Consulta con filtros por: usuario, fecha, modelo, acción.` 

- `📑 Exportación a Excel/PDF.` 

```
---
```

```
### 📑 EPIC 2: Gestión de Infraestructura e Inventario (Mapa Digital)
```

```
#### US-2.1: Configuración de jerarquía del cementerio
```

```
**COMO** Administrador del Cementerio
```

```
**QUIERO** definir la estructura física del cementerio (Secciones → Bloques →
Niveles → Criptas)
```

```
**PARA** tener un inventario organizado y navegable
```

```
**Criterios de Aceptación:**
```

- `📑 Jerarquía: `Cementerio → Section → Block → Level → Crypt`.` 

- `📑 Cada entidad tiene: código alfanumérico único por tenant, nombre, descripción opcional.` 

- `📑 La cripta tiene atributos: `crypt_type_id` (Cripta/Nicho/Mausoleo/Osario), `capacity` (1-6 urnas/ataúdes), `dimensions`, `door_type`, `price`.` 

- `📑 Importación masiva desde CSV/Excel (para setup inicial de cementerios existentes).` 

- `📑 Validación: no se puede eliminar una sección si tiene criptas asociadas.` 

```
#### US-2.2: Mapa visual interactivo con código de colores
```

```
**COMO** usuario administrativo
```

```
**QUIERO** ver el estado de todas las criptas en un mapa visual
**PARA** identificar rápidamente espacios disponibles, ocupados o en
mantenimiento
```

```
**Criterios de Aceptación:**
```

- `📑 Vista tipo "grid" que replica la estructura física (Sección → Bloque → Nivel).` 

- `📑 Código de colores por estado: -` 🟢 `Verde = Disponible - 📑 Rojo = Ocupada -` 🟡 `Amarillo = Reservada - 📑 Azul = En Mantenimiento -` 🟣 `Morado = En Proceso de Decadencia -` ⚫ `Gris = Bloqueada por Morosidad - 📑 Click en cripta muestra popup con: código, tipo, capacidad, estado, titular actual (si aplica).` 

- `📑 Filtros por: sección, bloque, tipo, estado, capacidad.` 

- `📑 Leyenda de estados siempre visible.` 

```
#### US-2.3: Gestión de estados de cripta (RN-01)
```

```
**COMO** sistema
```

```
**QUIERO** validar que una cripta solo se venda/conceda si está en estado
"Disponible"
```

```
**PARA** cumplir con la RN-01 (Unicidad y Capacidad de Ocupación)
```

```
**Criterios de Aceptación:**
```

```
- 📑 Estados válidos: `available`, `occupied`, `reserved`, `maintenance`,
`decaying`, `blocked_debt`.
```

```
- 📑 Transiciones válidas definidas (ej. `available → reserved → occupied`,
`occupied → maintenance → available`).
```

```
- 📑 Transición `available → occupied` solo vía inhumación completada (RN-06).
```

- `📑 Transición automática a `blocked_debt` cuando se activa RN-04.` 

- `📑 Transición automática a `decaying` cuando se activa RN-03.` 

```
#### US-2.4: Ficha detallada de cripta
```

```
**COMO** usuario administrativo
```

```
**QUIERO** ver el historial completo de una cripta
```

```
**PARA** conocer su trazabilidad legal, sanitaria y financiera
```

```
**Criterios de Aceptación:**
```

- `📑 Ficha muestra: datos físicos, estado actual, contrato vigente, titular, beneficiarios, herederos.` 

- `📑 Timeline con: inhumaciones, exhumaciones, traslados, pagos, cambios de titularidad.` 

- `📑 Documentos adjuntos: contrato escaneado, actas, certificados.` 

- `📑 Evidencias fotográficas de OT relacionadas.` 

- `📑 Estado de cuenta (pagos y adeudos).` 

```
---
```

```
### 📑 EPIC 3: Gestión Comercial, Contratos y Titularidad
```

```
#### US-3.1: Registro de clientes (titulares, beneficiarios, herederos)
```

```
**COMO** administrativo
```

- `**QUIERO** registrar datos completos de clientes con validación fiscal mexicana **PARA** cumplir con requisitos legales y fiscales` 

```
**Criterios de Aceptación:**
```

- `📑 Tipos de cliente: Persona Física, Persona Moral.` 

- `📑 Campos obligatorios: nombre/razón social, RFC (validación SAT), CURP (PF), email, teléfono, dirección.` 

- `📑 Validación de RFC con algoritmo oficial mexicano (validación de formato + dígito verificador).` 

- `📑 Un cliente puede ser titular de múltiples criptas.` 

- `📑 Búsqueda por RFC/CURP/nombre.` 

- `📑 Documento de identidad adjunto (INE/Pasaporte escaneado).` 

```
#### US-3.2: Emisión de contratos perpetuos y temporales (RN-02)
```

```
**COMO** administrativo
```

- `**QUIERO** generar contratos de venta/concesión con cálculos automáticos **PARA** formalizar la tenencia de criptas` 

```
**Criterios de Aceptación:**
```

- `📑 Tipos de contrato: `perpetual` (perpetuidad), `temporary_10`, `temporary_25`, `temporary_50`.` 

- `📑 Contrato perpetuo: genera cobros anuales por "Cuota de Mantenimiento", nunca por renovación del espacio.` 

- `📑 Contrato temporal: calcula automáticamente `start_date`, `end_date`, alertas a 12/6/3 meses del vencimiento.` 

- `📑 Campos: cliente, cripta, tipo, precio, forma de pago, fecha firma, vigencia.` 

- `📑 Generación de PDF del contrato con plantilla parametrizable por tenant.` 

- `📑 Firma digital simple (imagen + hash + timestamp + IP).` 

- `📑 Al firmar contrato temporal → cripta cambia a estado `occupied`.` 

```
#### US-3.3: Gestión de reservas
```

```
**COMO** administrativo
```

```
**QUIERO** reservar una cripta para un cliente interesado
```

- `**PARA** asegurar el espacio mientras se formaliza la venta` 

```
**Criterios de Aceptación:**
```

- `📑 Reserva con fecha de expiración automática (configurable, default 15 días).` 

- `📑 Reserva requiere anticipo (configurable, default 20%).` 

- `📑 Cripta cambia a estado `reserved` (no puede venderse a otro).` 

- `📑 Si expira sin formalizar → cripta vuelve a `available`, anticipo según política del tenant.` 

- `📑 Job programado que limpia reservas expiradas diariamente.` 

```
#### US-3.4: Traspasos y sucesiones (RN-05)
```

## `**COMO** administrativo` 

```
**QUIERO** actualizar la titularidad de una cripta por defunción o venta
```

```
**PARA** mantener la información legal actualizada
```

```
**Criterios de Aceptación:**
```

- `📑 **Por defunción:** sistema bloquea transacciones comerciales hasta que se suba "Declaratoria de Herederos" o "Testamento" (documento obligatorio).` 

- `📑 **Por venta:** requiere contrato nuevo entre titular saliente y entrante, con validación de que no haya adeudos.` 

- `📑 Registro de herederos designados (pueden ser múltiples).` 

- `📑 Toda sucesión queda registrada en audit_logs (RN-07).` 

- `📑 Notificación automática a herederos registrados (cuando estén en portal).` 

```
---
```

```
### 📑 EPIC 4: Gestión Financiera y Cobranza
```

```
#### US-4.1: Registro de pagos manuales (caja)
```

## `**COMO** administrativo` 

- `**QUIERO** registrar pagos recibidos en ventanilla` 

- `**PARA** mantener el estado de cuenta actualizado` 

```
**Criterios de Aceptación:**
```

- `📑 Tipos de pago: venta de cripta, mantenimiento anual, servicios, reservas.` 

- `📑 Formas de pago: efectivo, transferencia, cheque, tarjeta (manual).` 

- `📑 Recibo con folio consecutivo por tenant (no global).` 

- `📑 Asignación automática a contrato/adeudo correspondiente.` 

- `📑 Generación de recibo PDF descargable.` 

```
#### US-4.2: Emisión de CFDI 4.0 (SAT México)
```

## `**COMO** administrativo` 

- `**QUIERO** emitir facturas electrónicas válidas ante el SAT` 

- `**PARA** cumplir con obligaciones fiscales mexicanas` 

```
**Criterios de Aceptación:**
```

- `📑 Integración con PAC (Proveedor Autorizado de Timbrado): Facturama o SW sapien.` 

- `📑 CFDI 4.0 con todos los complementos requeridos.` 

- `📑 Uso de CFDI configurable por tipo de ingreso (G03 - Honorarios, G04 - Venta de mercancía, etc.).` 

- `📑 Generación de XML + PDF con código QR.` 

- `📑 Cancelación de facturas con motivo y folio fiscal relacionado.` 

- `📑 Timbrado asíncrono vía colas (para no bloquear UI si PAC es lento).` 

- `📑 Almacén de XML/PDF en Object Storage (S3/R2) con URLs firmadas.` 

- `📑 Reporte mensual de facturas emitidas/timbradas/canceladas.` 

```
#### US-4.3: Cálculo automático de adeudos e intereses (RN-04)
```

```
**COMO** sistema
```

```
**QUIERO** calcular adeudos de mantenimiento e intereses moratorios
automáticamente
```

- `**PARA** mantener la cartera actualizada sin intervención manual` 

```
**Criterios de Aceptación:**
```

- `📑 Job diario que genera adeudos de mantenimiento anual para contratos perpetuos y temporales vigentes.` 

- `📑 Cálculo de intereses moratorios configurable por tenant (tasa mensual, días de gracia).` 

- `📑 Estados de cuenta por cliente con desglose de adeudos.` 

- `📑 Reporte de aging de cartera (0-30, 31-60, 61-90, 90+ días).` 

```
#### US-4.4: Bloqueo automático por morosidad (RN-04)
```

## `**COMO** sistema` 

```
**QUIERO** bloquear criptas con adeudos superiores a X meses
```

```
**PARA** incentivar el pago y cumplir con la política del cementerio
```

```
**Criterios de Aceptación:**
```

- `📑 Umbral configurable por tenant (default: 3 meses).` 

- `📑 Al superar umbral → cripta cambia a estado `blocked_debt`.` 

- `📑 Efectos del bloqueo:` 

- `📑 No se permiten nuevas inhumaciones.` 

- `📑 No se permiten exhumaciones (salvo orden judicial con flag especial).` 

- `📑 No se prestan servicios de mantenimiento estético.` 

- `📑 Sí se permiten pagos para desbloquear.` 

- `📑 Al liquidar adeudo → cripta vuelve a estado anterior automáticamente.` 

- `📑 Notificación al titular (email/WhatsApp) antes y después del bloqueo.` 

```
---
```

```
### 📑 EPIC 5: Operaciones de Campo (PWA Offline-First)
```

```
#### US-5.1: Generación de Órdenes de Trabajo (OT)
```

```
**COMO** administrativo
```

```
**QUIERO** generar OT para inhumaciones, exhumaciones, traslados y limpiezas
```

```
**PARA** formalizar y controlar las operaciones de campo
```

```
**Criterios de Aceptación:**
```

- `📑 Tipos de OT: `inhumation`, `exhumation`, `transfer`, `cleaning`, `maintenance`.` 

- `📑 Campos: tipo, cripta, cliente relacionado, fecha programada, cuadrilla asignada, observaciones.` 

- `📑 Validación RN-06 para inhumación: requiere certificado de defunción, tipo de ataúd/urna.` 

- `📑 Validación RN-01: inhumación solo si cripta tiene capacidad disponible.` 

- `📑 Validación RN-04: no se puede inhumar/exhumar si cripta está `blocked_debt` (excepto exhumación con flag judicial).` 

- `📑 Asignación a cuadrilla (grupo de operativos).` 

```
#### US-5.2: PWA offline-first para operativos de campo
```

```
**COMO** operativo de campo
```

- `**QUIERO** recibir mis OT en el smartphone y ejecutarlas sin depender de internet` 

- `**PARA** trabajar eficientemente aunque no haya señal en el cementerio` 

```
**Criterios de Aceptación:**
```

- `📑 PWA instalable en Android (manifest.json + service worker).` 

- `📑 Sincronización automática al abrir la app (descarga de OT pendientes).` 

- `📑 IndexedDB para almacenar OT pendientes localmente.` 

- `📑 Ejecución de OT offline: marcar como iniciada, tomar fotos, capturar firma.` 

- `📑 Cola de sincronización: al recuperar conectividad, sube datos automáticamente.` 

- `📑 Manejo de conflictos: si hay conflicto en sync, se marca para revisión manual.` 

- `📑 Indicador visual de estado de sincronización (synced / pending / error).` 

```
#### US-5.3: Captura de evidencia fotográfica y firma digital (RN-06)
```

```
**COMO** operativo de campo
```

- `**QUIERO** tomar fotos y capturar firma en la OT` 

- `**PARA** dejar evidencia legal y sanitaria del trabajo realizado` 

```
**Criterios de Aceptación:**
```

- `📑 Captura de foto desde cámara del dispositivo (no desde galería, para garantizar autenticidad).` 

- `📑 Mínimo 1 foto obligatoria, máximo 10 por OT.` 

- `📑 Compresión automática de imagen (max 1MB) para optimizar upload.` 

- `📑 Firma digital en canvas (táctil) con: imagen PNG, hash SHA-256, timestamp, IP, geolocalización (si disponible).` 

- `📑 Upload a Object Storage (S3/R2) con URLs firmadas (acceso temporal).` 

- `📑 OT no puede marcarse como "Completada" sin al menos 1 foto + firma (validación RN-06).` 

- `📑 Metadata EXIF preservada para auditoría.` 

```
#### US-5.4: Asignación y gestión de cuadrillas
```

```
**COMO** supervisor operativo
```

- `**QUIERO** crear cuadrillas y asignar OT a ellas` 

- `**PARA** organizar el trabajo de campo eficientemente` 

```
**Criterios de Aceptación:**
```

- `📑 Cuadrilla = grupo de operativos (1-5 personas) + vehículo opcional.` 

- `📑 Asignación de OT a cuadrilla por fecha.` 

- `📑 Vista de calendario con carga de trabajo por cuadrilla.` 

- `📑 Reporte de OT completadas/pendientes por cuadrilla.` 

```
---
```

```
### 📑 EPIC 6: Dashboard y BI Básico
```

```
#### US-6.1: Dashboard ejecutivo con KPIs
```

```
**COMO** Administrador del Cementerio
```

- `**QUIERO** ver en una pantalla los indicadores clave del negocio` 

- `**PARA** tomar decisiones informadas rápidamente` 

```
**Criterios de Aceptación:**
```

- `📑 KPIs en cards:` 

- `Total de criptas / Ocupadas / Disponibles / % Ocupación` 

- `Adeudo total vencido / % Morosidad` 

- `Ingresos del mes / Comparativa vs. mes anterior` 

- `OT pendientes / OT completadas hoy` 

- `📑 Gráficos:` 

- `Occupancy por sección (barras)` 

- `Ingresos últimos 12 meses (línea)` 

- `Aging de cartera (pie)` 

- `📑 Alertas críticas:` 

- `Criptas en proceso de decadencia (RN-03)` 

- `Contratos próximos a vencer (12/6/3 meses)` 

- `Morosidad alta (>X meses)` 

- `📑 Filtros por: sección, bloque, tipo, rango de fechas.` 

- `📑 Exportación de reportes a Excel/PDF.` 

```
#### US-6.2: Reportes operativos
```

```
**COMO** usuario administrativo
```

```
**QUIERO** generar reportes específicos para auditorías y toma de decisiones
```

```
**PARA** cumplir con requisitos internos y gubernamentales
```

```
**Criterios de Aceptación:**
```

- `📑 Reportes disponibles:` 

- `Inventario general con estados` 

- `Occupancy por sección/bloque/tipo` 

- `Cartera vencida con antigüedad` 

- `Contratos por vencer (próximos 12 meses)` 

- `Criptas en decadencia` 

- `OT completadas en periodo` 

- `Ingresos y egresos` 

- `📑 Filtros avanzados en cada reporte.` 

- `📑 Exportación a Excel (CSV) y PDF.` 

- `📑 Programación de reportes automáticos (ej. cada lunes por email).` 

```
---
```

```
### 📑 EPIC 7: Configuración del Tenant
```

```
#### US-7.1: Parametrización del tenant
```

```
**COMO** Administrador del Cementerio
```

```
**QUIERO** configurar los parámetros operativos y legales de mi cementerio
```

```
**PARA** adaptar el sistema a la normativa local y políticas internas
```

```
**Criterios de Aceptación:**
```

- `📑 Parámetros configurables:` 

- `Periodo de gracia para decadencia (RN-03, default 3 años)` 

- `Tiempo legal para liberación de cripta en decadencia` 

- `Meses de atraso para bloqueo por morosidad (RN-04, default 3)` 

- `Tasa de interés moratorio mensual` 

- `Duración de reserva (default 15 días)` 

- `Porcentaje de anticipo de reserva` 

- `Días de gracia para pago de mantenimiento` 

- `Plantillas de contratos y recibos` 

- `Logo y datos fiscales del cementerio` 

- `📑 Cambios quedan registrados en audit_logs.` 

- `📑 Validación de rangos permitidos (ej. tasa de interés 0-10% mensual).` 

```
---
```

## `###` 📊 `RESUMEN DE USER STORIES POR EPIC` 

- `| Epic | User Stories | Prioridad MVP |` 

- `|------|--------------|---------------|` 

- `| 1. Multi-tenancy y Seguridad | US-1.1, US-1.2, US-1.3, US-1.4 | 📑 MUST |` 

- `| 2. Infraestructura e Inventario | US-2.1, US-2.2, US-2.3, US-2.4 | 📑 MUST |` 

- `| 3. Comercial y Contratos | US-3.1, US-3.2, US-3.3, US-3.4 | 📑 MUST |` 

- `| 4. Financiero y Cobranza | US-4.1, US-4.2, US-4.3, US-4.4 | 📑 MUST |` 

- `| 5. Operaciones de Campo | US-5.1, US-5.2, US-5.3, US-5.4 | 📑 MUST |` 

- `| 6. Dashboard y BI | US-6.1, US-6.2 | 📑 MUST (básico) |` 

- `| 7. Configuración Tenant | US-7.1 | 📑 MUST |` 

- `**Total User Stories MVP:** 25 User Stories` 

```
---
```

## `## 4.  REQUISITOS NO FUNCIONALES` 🔒 

```
### 4.1 Rendimiento
```

- `| Métrica | Meta | Instrumento |` 

- `|---------|------|-------------|` 

- `| Tiempo de carga de páginas | < 2 segundos (P95) | Lighthouse + Laravel Telescope |` 

- `| Consultas MySQL complejas | < 500ms (P95) | Laravel Telescope + slow query log | | Usuarios concurrentes soportados | 200 por tenant / 5,000 globales | Load testing con k6 |` 

- `| Tiempo de respuesta API | < 300ms (P95) | APM (Sentry) |` 

- `| Timbrado SAT asíncrono | < 30 segundos (vía cola) | Queue monitoring |` 

## `### 4.2 Seguridad` 

- `📑 **Autenticación:** Laravel Breeze con bcrypt + rate limiting en login.` 

- `📑 **CSRF:** Protección automática en todos los formularios Blade.` 

- `📑 **SQL Injection:** Protegido por Eloquent ORM (consultas parametrizadas).` 

- `📑 **XSS:** Blade escapa automáticamente `{{ }}`. Uso de `{!! !!}` solo cuando sea estrictamente necesario y con HTMLPurifier.` 

- `📑 **Multi-tenancy:** Aislamiento garantizado por Global Scopes + validación en Policies.` 

- `📑 **Datos sensibles:** RFC/CURP cifrados en reposo (AES-256). Contraseñas con bcrypt (cost 12).` 

- `📑 **HTTPS:** Obligatorio en producción (Let's Encrypt vía Forge).` 

- `📑 **Headers de seguridad:** CSP, HSTS, X-Frame-Options, X-Content-TypeOptions.` 

- `📑 **Object Storage:** URLs firmadas con expiración (15 minutos para fotos, 1 hora para documentos).` 

- `📑 **Audit Logs:** Tabla inmutable (sin UPDATE/DELETE permitidos).` 

- `📑 **Cumplimiento LFPDPPP:** Aviso de privacidad, consentimiento explícito, derecho al olvido (anonymización, no borrado).` 

## `### 4.3 Usabilidad` 

- `📑 **Diseño responsive:** Mobile-first (Tailwind CSS).` 

- `📑 **Accesibilidad:** WCAG 2.1 nivel AA (contrastes, ARIA labels, navegación por teclado).` 

- `📑 **UX para usuarios de bajo nivel técnico:**` 

- `Iconografía clara con tooltips.` 

- `Wizard para flujos complejos (ej. creación de contrato).` 

- `Confirmaciones explícitas para acciones destructivas.` 

- `Mensajes de error en lenguaje natural (no técnico).` 

- `📑 **Capacitación:** Videos tutoriales embebidos (Loom/YouTube) en cada módulo.` 

- `📑 **Onboarding:** Tour guiado la primera vez que el usuario entra al sistema.` 

## `### 4.4 Compatibilidad` 

- `📑 **Navegadores web:** Chrome, Firefox, Safari, Edge (últimas 2 versiones principales).` 

- `📑 **PWA:** Android 8+ (Chrome, Samsung Internet). iOS 13+ (Safari, con limitaciones de PWA).` 

- `**Dispositivos:** Desktop (1366x768+), tablet (768px+), móvil (360px+).` 🖥️� 

- `📑 **Conectividad:** PWA funciona offline; sincroniza al recuperar conexión.` 

## `### 4.5 Escalabilidad` 

`-` 📈 `**Arquitectura:** Preparada para escalar horizontalmente (stateless app servers). -` 📈 `**Caché:** Redis para sesiones, caché de consultas frecuentes, colas.` 

- 📈 `**Colas:** Laravel Queue (Redis driver) para procesos pesados (timbrado` 

- `SAT, notificaciones, sync PWA).` 

`-` 📈 `**Base de datos:** MySQL 8.x con índices optimizados, particionamiento futuro si crece >1M registros. -` 📈 `**Object Storage:** S3/R2 con CDN (Cloudflare) para assets estáticos y fotos.` 

`-` 📈 `**Multi-tenancy:** Single DB soporta hasta 500 tenants sin degradación (con índices adecuados).` 

```
### 4.6 Disponibilidad
```

- `**Uptime objetivo:** 99.5% mensual (~3.6 horas de downtime permitido).` 

- ⏱️� 

- `- **Backups:** Diario automático (MySQL + Object Storage), retención 30 días.` ⏱️� 

- `- **RTO (Recovery Time Objective):** < 4 horas.` ⏱️� 

- `- **RPO (Recovery Point Objective):** < 1 hora.` ⏱️� 

- `- **Monitoreo:** Sentry (errores) + UptimeRobot (disponibilidad) + Laravel` ⏱️� 

- `Telescope (performance).` 

```
---
```

```
## 5. 📑 DISEÑO DE INTERFAZ (WIREFRAMES TEXTUALES)
```

```
### 5.1 Pantalla: Login
```

```
```
```

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│              ┌──────────────────────┐               │
│              │   [LOGO SGIC 2.0]    │               │
│              │                      │               │
│              │  Iniciar Sesión      │               │
│              │                      │               │
│              │  Email               │               │
│              │  [________________]  │               │
│              │                      │               │
│              │  Contraseña          │               │
│              │  [________________]  │               │
│              │                      │               │
│              │  [ ] Recordarme      │               │
│              │                      │               │
│              │  [ INGRESAR ]        │               │
│              │                      │               │
│              │  ¿Olvidaste tu       │               │
│              │  contraseña?         │               │
│              └──────────────────────┘               │
│                                                     │
│              © 2026 SGIC - Todos los derechos       │
└─────────────────────────────────────────────────────┘
```
```

```
### 5.2 Pantalla: Dashboard Ejecutivo
```

```
```
```

|`````|`````|
|---|---|
|`┌─────────────────────────────────────────────────────────────┐`||
|`│`|`[LOGO]  SGIC 2.0       [📑 3] [📑 Don Roberto ▼]           │`|
|`├──────────┬──────────────────────────────────────────────────┤`||
|`│`|`│                                                  │`|
|`│`|<br>`Home  │  Dashboard - Cementerio San José                 │`<br>🏠|
|`│`<br>`│`<br>`│`|`│                                                  │`<br> <br>`Inven │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────┐│`<br>🗺<br>`│  │ CRIPTAS  │ │ OCUPAC.  │ │ MOROSIDAD│ │ INGR.││`|
|`│`|`📑 Client│  │   300    │ │   62%    │ │  $45,200 │ │$120K ││`|
|`│`|`│  │  +5 hoy  │ │  +2% mes │ │  -8% mes │ │+15%  ││`|
|`│`|`📑 Contr │  └──────────┘ └──────────┘ └──────────┘ └──────┘│`|



`│          │                                                  │ │` 💰 `Finan │  ┌─────────────────────┐ ┌────────────────────┐ │ │          │  │  [Gráfico Occupancy]│ │ [Gráfico Ingresos] │ │ │ 📑 OT    │  │  por sección        │ │ últimos 12 meses   │ │ │          │  │  ▓▓▓░░░░░░░         │ │  ╱╲ ╱╲           │ │ │` 📊 `BI    │  │  ▓▓▓▓░░░░░          │ │               │ │╱ ╲╱ ╲ ╱ │          │  └─────────────────────┘ └────────────────────┘ │ │` ⚙️� `Config│                                                  │ │          │` ⚠️� `ALERTAS CRÍTICAS                             │ │ 📑 Salir │  • 3 contratos vencen en 3 meses                 │ │          │  • 2 criptas en proceso de decadencia            │ │          │  • 5 criptas bloqueadas por morosidad            │ └──────────┴──────────────────────────────────────────────────┘ ```` 

```
### 5.3 Pantalla: Mapa de Inventario
```

```
```
```

`┌─────────────────────────────────────────────────────────────┐ │ [LOGO]  Inventario > Mapa                   [+ Nueva Cripta]│ ├──────────┬──────────────────────────────────────────────────┤ │ Filtros: │  Leyenda:` 🟢 `Disp` 🟡 🟣 `Ocup` ⚫ `RD` **`e`** `s c 📑Bloq│Mant 📑 │ [Secc ▼] │                                                  │ │ [Bloq ▼] │  SECCIÓN A - "San Pedro"                         │ │ [Tipo ▼] │  ┌──────────────────────────────────────────┐   │ │ [Estado▼]│  │ BLOQUE 1                                 │   │ │          │  │  Nivel 3: [` 🟢 `A1][` 🟢 🟡 `A2][ A4][📑` **`A`** `35` **`]`** `[📑 │   │ │          │  │  Nivel 2: [` 🟢 `B2][📑` **`B`** `13` **`][📑`** `B4][📑B5] │   │ │          │  │  Nivel 1: [` 🟢 `C1][` 🟢 `C2][` 🟢 🟢 `C3][` **`C`** `45` **`]`** `[📑 │   │ │          │  └──────────────────────────────────────────┘   │ │          │  ┌──────────────────────────────────────────┐   │ │          │  │ BLOQUE 2                                 │   │ │          │  │  Nivel 2: [` 🟢 `D1][` 🟣 `D2][` 🟢 ⚫ `D3][` **`D`** `45` **`]`** `[📑 │   │ │          │  │  Nivel 1: [` 🟢 🟢 🟢 `E1][` **`E`** `24` **`][`** `📑` **`E`** `35` **`]`** `[📑 │   │ │          │  └──────────────────────────────────────────┘   │ │          │                                                  │ │          │  [Exportar Excel] [Imprimir]                     │ └──────────┴──────────────────────────────────────────────────┘` 

```
Popup al hacer click en cripta 📑A2:
```

```
┌─────────────────────────────┐
│ Cripta A2 - Estado: OCUPADA │
│ Tipo: Cripta (4 capacidades)│
│ Titular: Juan Pérez García  │
│ Contrato: Perpetuo #1234    │
│ Inhumaciones: 2/4           │
│                             │
│ [Ver Detalle] [Ver Cuenta]  │
└─────────────────────────────┘
```
```

```
### 5.4 Pantalla: PWA de Campo - Lista de OT
```

```
```
```

`┌─────────────────────────────┐ │` ☰ `Mis Órdenes   [📑 Sync] │ ├─────────────────────────────┤ │ ● Sincronizado hace 5 min   │ ├─────────────────────────────┤ │` 📋 `HOY - 09/Jul/2026        │ │                             │ │ ┌─────────────────────────┐ │ │ │ OT-2026-0045            │ │ │ │ INHUMACIÓN              │ │` 

`│ │ Cripta: A2 - Sec. A     │ │ │ │ Cliente: Familia Pérez  │ │ │ │ 10:00 AM                │ │ │ │ [INICIAR ]             │ │▶ │ └─────────────────────────┘ │ │                             │ │ ┌─────────────────────────┐ │ │ │ OT-2026-0046            │ │ │ │ LIMPIEZA                │ │ │ │ Cripta: C3 - Sec. A     │ │ │ │ 11:30 AM                │ │ │ │ [INICIAR ]             │ │▶ │ └─────────────────────────┘ │ │                             │ │` 📋 `PENDIENTES (3)           │ │` 📋 `COMPLETADAS HOY (2)      │ └─────────────────────────────┘ ```` 

```
### 5.5 Pantalla: PWA de Campo - Ejecución de OT
```

```
```
```

**==> picture [187 x 339] intentionally omitted <==**

**----- Start of picture text -----**<br>
┌─────────────────────────────┐<br>│ ← OT-2026-0045              │<br>│ INHUMACIÓN - Cripta A2      │<br>├─────────────────────────────┤<br>│                             │<br>│ 📑 EVIDENCIA FOTOGRÁFICA    │<br>│ ┌─────┐ ┌─────┐ ┌─────┐   │<br>│ │  📷   │ │  📷   │ │  📷   │   │<br>│ │Foto1│ │Foto2│ │Foto3│   │<br>│ └─────┘ └─────┘ └─────┘   │<br>│ [+ Tomar otra foto]         │<br>│                             │<br>│  ✍️�  FIRMA DE CONFORMIDAD     │<br>│ ┌─────────────────────────┐ │<br>│ │                         │ │<br>│ │   [Firma de Juan P.]    │ │<br>│ │                         │ │<br>│ └─────────────────────────┘ │<br>│ [Limpiar firma]             │<br>│                             │<br>│ 📑 OBSERVACIONES            │<br>│ ┌─────────────────────────┐ │<br>│ │ Se realizó sin novedad  │ │<br>│ └─────────────────────────┘ │<br>│                             │<br>│ 📑 Ubicación: 19.4326, -99.1│<br>│  🕐  Hora: 10:32 AM           │<br>│                             │<br>│ [COMPLETAR OT ]            │✓<br>└─────────────────────────────┘<br>```<br>**----- End of picture text -----**<br>


```
---
```

```
## 6. 📑 FLUJOS DE PROCESO
```

```
### 6.1 Flujo Principal: Venta de Cripta (Ciclo Comercial Completo)
```

```
```
```

```
[Inicio: Cliente interesado]
           ↓
```

- `[1. Administrativo busca cripta disponible en mapa]` 

- `[2. ¿Cripta disponible?] ──NO──→ [Mostrar alternativas] → [2] ↓ SÍ` 

- `[3. Crear RESERVA (15 días, 20% anticipo)]` 

- `[4. Cripta → estado "reserved"]` 

- `[5. Cliente paga anticipo]` 

- `[6. Emisión de recibo de anticipo]` 

- `[7. ¿Cliente formaliza en 15 días?] ──NO──→ [Expira reserva]` 

   - `↓ SÍ                                    ↓` 

- `[8. Generar CONTRATO]                     [Cripta → "available"] ↓` 

- `[9. Validar datos fiscales (RFC)]` 

- `[10. Cliente firma digitalmente]` 

- `[11. Emisión de CFDI 4.0 (vía cola)]` 

- `[12. Cripta → estado "occupied"]` 

- `[13. Registrar titular, beneficiarios, herederos]` 

- `[14. Generar expediente digital completo]` 

- `[Fin: Venta completada]` 

```
```
```

```
**Descripción de pasos críticos:**
```

- `**Paso 3:** La reserva bloquea la cripta para otros clientes.` 

- `**Paso 10:** Firma digital simple con imagen + hash + timestamp + IP.` 

- `**Paso 11:** Timbrado SAT asíncrono. Si falla, se reintenta 3 veces.` 

- `**Paso 14:** Expediente incluye: contrato, RFC, INE, CFDI, actas futuras.` 

- `### 6.2 Flujo Principal: Inhumación (Operación de Campo)` 

```
```
```

- `[Inicio: Familia solicita inhumación]` 

- `[1. Administrativo verifica requisitos (RN-06)]` 

- `[2. ¿Certificado de defunción válido?] ──NO──→ [Solicitar documento] ↓ SÍ` 

- `[3. ¿Cripta tiene capacidad?] ──NO──→ [Rechazar solicitud]` 

- `[4. ¿Cripta está bloqueada por morosidad?] ──SÍ──→ [Solicitar pago previo]` 

- `[5. Generar OT tipo "inhumation"]` 

- `[6. Asignar a cuadrilla]` 

- `[7. Operativo recibe OT en PWA]` 

- `[8. Operativo ejecuta OT (offline si es necesario)]` 

- `[9. Toma fotos de evidencia (mín. 1)]` 

- `[10. Captura firma de familiar presente]` 

- `[11. Marca OT como "Completada"]` 

- `[12. Sync con servidor (automático al tener conexión)]` 

- `↓` 

- `[13. Sistema valida: foto + firma + requisitos sanitarios]` 

- `↓` 

- `[14. Cripta actualiza capacidad (ej. 2/4 inhumaciones)]` 

- `↓` 

- `[15. Si cripta llena → estado "occupied" (completo)]` 

- `↓` 

- `[16. Registro inmutable en audit_logs]` 

- `↓` 

- `[Fin: Inhumación completada y trazada]` 

- ````` 

```
### 6.3 Flujo Principal: Cobro de Mantenimiento + Bloqueo por Morosidad (RN-04)
```

```
```
```

- `[Job diario - 2:00 AM]` 

- `↓` 

- `[1. Sistema identifica contratos con mantenimiento por cobrar] ↓` 

- `[2. Genera adeudo automático con fecha de vencimiento]` 

- `↓` 

- `[3. ¿Dentro de días de gracia?] ──SÍ──→ [Esperar]` 

- `↓ NO` 

- `[4. Envía recordatorio (email + WhatsApp)]` 

- `↓` 

- `[5. ¿Pasaron X meses sin pago?] ──NO──→ [Calcular intereses moratorios] ↓ SÍ` 

- `[6. Cripta → estado "blocked_debt"]` 

- `↓` 

- `[7. Notifica al titular (email + WhatsApp + SMS)]` 

- `↓` 

- `[8. Bloquea operaciones: inhumación, exhumación, servicios] ↓` 

- `[9. Familia realiza pago (ventanilla o portal V1.0)]` 

- `↓` 

- `[10. Sistema valida pago y calcula intereses]` 

- `↓` 

- `[11. Adeudo liquidado]` 

- `↓` 

- `[12. Cripta → estado anterior (occupied/available)]` 

- `↓` 

- `[13. Notifica al titular: "Su cripta ha sido desbloqueada"]` 

- `↓` 

- `[Fin: Ciclo de cobranza completado]` 

- ````` 

```
### 6.4 Flujo Principal: Decadencia de Contrato Temporal (RN-03)
```

```
```
```

- `[Job diario - 3:00 AM]` 

- `[1. Sistema identifica contratos temporales próximos a vencer] ↓` 

- `[2. Alertas a 12/6/3 meses (email + WhatsApp)]` 

- `[3. ¿Contrato venció?] ──NO──→ [Fin del ciclo] ↓ SÍ` 

- `[4. Entra en "Periodo de Gracia" (configurable, default 3 años)] ↓` 

- `[5. Notificaciones periódicas al titular para renovar] ↓` 

- `[6. ¿Titular renueva?] ──SÍ──→ [Nuevo contrato temporal] ↓ NO` 

- `[7. ¿Pasó periodo de gracia?] ──NO──→ [Seguir notificando]` 

```
           ↓ SÍ
```

- `[8. Cripta → estado "decaying"]` 

- `↓` 

- `[9. Genera OT para traslado a osario común]` 

- `↓` 

- `[10. Operativo ejecuta OT con evidencia]` 

- `↓` 

- `[11. Cripta → estado "available"]` 

- `↓` 

- `[12. Notifica al titular (última notificación)]` 

- `↓ [13. Registro inmutable en audit_logs] ↓` 

```
[Fin: Cripta liberada para nueva venta]
```
```

```
---
```

## `## 7.` 📊 `MÉTRICAS Y ANALYTICS` 

```
### 7.1 Métricas de Uso
```

```
| Métrica | Cómo se mide | Frecuencia |
|---------|--------------|------------|
| **DAU/MAU** (Usuarios activos diarios/mensuales) | Login tracking | Diario |
| **Tiempo promedio en sistema** | Session duration | Diario |
```

```
| **Módulos más usados** | Page views por módulo | Semanal |
```

```
| **Tasa de adopción PWA** | % operativos que usan PWA vs. web | Mensual |
| **OT completadas offline** | Sync events sin conexión | Mensual |
| **Errores de sync PWA** | Failed sync attempts | Diario |
```

```
### 7.2 Métricas de Éxito del Producto
```

```
| Métrica | Meta | Instrumento |
|---------|------|-------------|
| **Reducción tiempo administrativo** | -70% | Time-tracking comparativo |
| **Tasa de ocupación** | +15% vs. línea base | Reporte de inventario |
```

```
| **Reducción de morosidad** | -40% | Aging de cartera |
| **Digitalización de contratos** | 100% | Auditoría de expedientes |
```

```
| **NPS portal familias** (V1.0) | >70 | Encuesta in-app |
| **Uptime SaaS** | ≥99.5% | UptimeRobot |
| **Tiempo de respuesta P95** | <2s | Lighthouse + APM |
```

```
| **Tasa de error en timbrado SAT** | <1% | Logs de colas |
```

```
### 7.3 Métricas de Negocio (para el cliente del SaaS)
```

```
| Métrica | Fórmula | Valor objetivo |
|---------|---------|----------------|
| **Ingreso promedio por cripta** | Ingresos totales / Criptas | Maximizar |
| **Costo de adquisición por tenant** | Marketing+Ventas / Nuevos tenants |
Minimizar |
```

```
| **Churn rate mensual** | Tenants perdidos / Total tenants | <2% |
```

```
| **MRR** (Monthly Recurring Revenue) | Suma suscripciones activas | Crecer 10%
mensual |
```

```
| **LTV** (Lifetime Value) | MRR × Vida promedio del tenant | >3x CAC |
```

```
---
```

## `## 8. 📑 CRITERIOS DE LANZAMIENTO` 

```
### 8.1 Criterios de Aceptación Generales
```

```
- 📑 **Todas las User Stories MUST HAVE** tienen criterios de aceptación
cumplidos al 100%.
```

```
- 📑 **Las 7 Reglas de Negocio (RN-01 a RN-07)** están implementadas y validadas
con pruebas automatizadas.
```

```
- 📑 **Multi-tenancy** funciona correctamente: datos de Tenant A son invisibles
para Tenant B (prueba de penetración).
```

```
- 📑 **Integración SAT CFDI 4.0** timbra facturas reales en ambiente de
producción del SAT.
```

```
- 📑 **PWA offline-first** funciona sin conectividad y sincroniza correctamente
al reconectar.
```

```
- 📑 **Bitácora de auditoría** es inmutable y registra todas las acciones
críticas.
```

```
- 📑 **Performance:** P95 de tiempo de carga < 2s en condiciones normales.
```

```
- 📑 **Seguridad:** Sin vulnerabilidades críticas en scan de seguridad (OWASP Top
10).
```

```
- 📑 **Backups** automáticos funcionando con restauración probada.
```

- `📑 **Documentación técnica y de usuario** completa.` 

```
### 8.2 Pruebas Requeridas
```

```
- [ ] **Pruebas unitarias** (Pest PHP): cobertura >80% en Services y Reglas de
Negocio.
- [ ] **Pruebas de integración**: flujos completos (venta, inhumación,
cobranza).
```

```
- [ ] **Pruebas de multi-tenancy**: aislamiento de datos entre tenants.
```

```
- [ ] **Pruebas de PWA offline**: sync, conflictos, manejo de errores.
```

```
- [ ] **Pruebas de seguridad**: SQL injection, XSS, CSRF, autorización.
- [ ] **Pruebas de rendimiento**: load testing con k6 (200 usuarios
concurrentes).
```

```
- [ ] **Pruebas de usuario (UAT)**: con cementerio piloto real.
```

```
- [ ] **Pruebas de integración SAT**: timbrado, cancelación, acuse de recibo.
```

```
- [ ] **Pruebas de backups**: restauración completa desde backup.
```

```
### 8.3 Validación
```

- `[ ] **Validación técnica:** Arquitecto de Software (revisión de SDD).` 

- `[ ] **Validación funcional:** Product Manager (revisión de PRD).` 

- `[ ] **Validación de usuario:** Administrador del cementerio piloto.` 

- `[ ] **Validación legal:** Asesor legal (cumplimiento LFPDPPP, NOM-013, CFDI 4.0).` 

- `[ ] **Aprobación final:** Stakeholder principal (dueño del producto).` 

```
---
```

`## 9. ROADMAP Y FASES` 🗺️� 

```
### 9.1 Fase 1: MVP (Meses 1-6)
```

```
**Duración:** 24 semanas (6 meses)
```

```
**Equipo:** 2 desarrolladores full-stack
```

```
**Funcionalidades:**
```

- `Multi-tenancy + Auth + RBAC (Epic 1)` 

- `Inventario y Mapa Digital (Epic 2)` 

- `Clientes y Contratos (Epic 3)` 

- `Pagos + Facturación SAT (Epic 4 parcial)` 

- `Órdenes de Trabajo + PWA (Epic 5)` 

- `Bitácora de Auditoría (US-1.4)` 

- `Dashboard básico (US-6.1)` 

- `Configuración de Tenant (Epic 7)` 

```
**Entregables:**
```

- 📦 `Aplicación web SaaS funcional en producción` 

- `-` 📦 `PWA para operativos de campo -` 📦 `Integración SAT CFDI 4.0 operativa -` 📦 `Documentación técnica y de usuario` 

`-` 📦 `Cementerio piloto operando en vivo` 

```
### 9.2 Fase 2: Versión 1.0 (Meses 7-10)
```

```
**Duración:** 16 semanas (4 meses)
```

```
**Funcionalidades:**
```

- `Portal de Autogestión para Familias (US adicionales)` 

- `Pasarelas de pago online (MercadoPago, Stripe, PayPal)` 

- `Motor de Decadencia completo (RN-03)` 

- `Notificaciones multicanal (Email + WhatsApp + SMS)` 

- `Reportes sanitarios para gobierno` 

- `Conciliación bancaria automática` 

- `BI avanzado (US-6.2 completo)` 

- `**Entregables:** -` 📦 `Portal de familias funcional -` 📦 `Pagos en línea operativos -` 📦 `Sistema de notificaciones automatizado -` 📦 `Reportes gubernamentales listos` 

```
### 9.3 Fase 3: Versión 2.0 (Meses 11+)
```

```
**Duración:** Continua
```

```
**Funcionalidades:**
```

- `App móvil nativa (si PWA no es suficiente)` 

- `Firma digital avanzada (e.firma SAT)` 

- `Marketplace de servicios funerarios` 

- `Integración con catastro municipal` 

- `API pública para terceros` 

- `Multi-idioma (expansión LATAM)` 

```
---
```

## `## 10.` 📚 `APÉNDICES` 

```
### 10.1 Glosario
```

```
| Término | Definición |
|---------|------------|
| **Cripta** | Espacio físico para depósito de restos (ataúd o urna). |
| **Nicho** | Espacio pequeño, generalmente para urnas cinerarias. |
| **Mausoleo** | Cripta de gran tamaño, familiar, con acceso interior. |
| **Osario** | Espacio común para restos provenientes de decadencia. |
| **Perpetuidad** | Derecho de uso indefinido de una cripta. |
| **Concesión temporal** | Derecho de uso por tiempo determinado (10, 25, 50
años). |
| **Decadencia** | Proceso legal de liberación de cripta temporal vencida. |
| **Inhumación** | Acto de depositar un cadáver/urna en una cripta. |
| **Exhumación** | Acto de retirar restos de una cripta. |
| **CFDI 4.0** | Comprobante Fiscal Digital por Internet, estándar SAT México. |
| **PAC** | Proveedor Autorizado de Timbrado (timbra facturas ante SAT). |
| **Tenant** | Cliente del SaaS (un cementerio). |
| **RBAC** | Role-Based Access Control (control de acceso por roles). |
| **PWA** | Progressive Web App (aplicación web instalable y offline). |
| **Global Scope** | Mecanismo de Eloquent para filtrar datos por tenant
automáticamente. |
| **LFPDPPP** | Ley Federal de Protección de Datos Personales en Posesión de los
Particulares. |
| **NOM-013** | Norma Oficial Mexicana para disposición de cadáveres. |
| **NOM-133** | Norma Oficial Mexicana para servicios funerarios. |
```

```
### 10.2 Referencias
```

```
- 📑 **PRD Base:** Documento "Sistema de Gestión Integral de Criptas (SGIC) 2.0"
provisto por el cliente.
```

- `📑 **Laravel Documentation:** https://laravel.com/docs/11.x` 

```
- 📑 **SAT CFDI 4.0:** https://www.sat.gob.mx/csatarjeta/catalogos
```

- `📑 **NOM-013-SSA2-1994:** Disposición de cadáveres.` 

- `📑 **NOM-133-SSA1-2012:** Servicios funerarios y cementerios.` 

- `📑 **LFPDPPP:** Ley Federal de Protección de Datos Personales.` 

- `📑 **Spatie Laravel Permission:** https://spatie.nl/docs/laravel-permission` 

- `📑 **PWA MDN:** https://developer.mozilla.org/es/docs/Web/Progressive_web_apps` 

## `### 10.3 Historial de Cambios` 

```
| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 09/Jul/2026 | Product Manager Senior | Versión inicial del PRD |
```

```
---
```

## `##` 📊 `RESUMEN EJECUTIVO DEL PRD` 

```
| Concepto | Valor |
```

```
|----------|-------|
```

```
| **User Personas** | 5 (SuperAdmin, AdminCementerio, Administrativo, Operativo,
Familia) |
```

```
| **Epics** | 7 (Multi-tenancy, Inventario, Comercial, Financiero, Operaciones,
BI, Configuración) |
```

```
| **User Stories MVP** | 25 |
```

- `| **Requisitos Funcionales MUST** | 25 |` 

```
| **Requisitos No Funcionales** | 6 categorías (Rendimiento, Seguridad,
Usabilidad, Compatibilidad, Escalabilidad, Disponibilidad) |
```

- `| **Wireframes** | 5 pantallas principales |` 

```
| **Flujos de Proceso** | 4 flujos críticos |
```

- `| **KPIs de éxito** | 8 métricas |` 

```
| **Criterios de lanzamiento** | 10 criterios + 9 tipos de pruebas |
```

- `| **Roadmap** | 3 fases (MVP 6m, V1.0 4m, V2.0 continuo) |` 

```
---
```

## `## 📑 OUTPUT DE FASE 3` 

## `📑 **FASE 3 COMPLETADA: PRD GENERADO**` 

📋 `**Documento PRD creado con:**` 

- `📑 5 User Personas detalladas` 

- `📑 7 Epics con 25 User Stories (criterios de aceptación incluidos)` 

- `📑 25 Requisitos Funcionales MUST HAVE` 

- `📑 6 categorías de Requisitos No Funcionales` 

- `📑 5 Wireframes textuales de pantallas principales` 

- `📑 4 Flujos de proceso críticos` 

- `📑 8 KPIs de éxito + métricas de uso` 

- `📑 10 Criterios de lanzamiento + 9 tipos de pruebas` 

- `📑 Roadmap de 3 fases` 

📋 `**PRD listo para revisión.**` 

```
---
```

## `## 📑 SIGUIENTE PASO` 

```
**¿Confirmas que el PRD es correcto y está alineado con tus expectativas?**
```

```
Si es así, avanzaremos a **FASE 4: SDD (Software Design Document)** donde
generaré:
```

`1. **Arquitectura de Alto Nivel (C4 Model)** — Context, Container, Component, Code` 

`2. **Stack Tecnológico Detallado** — Versiones, justificaciones, alternativas` 

`3. **Diseño de Base de Datos (ER Diagram)** — Enfocado en las 7 Reglas de Negocio` 

`4. **Modelos Eloquent** — Con relaciones, scopes, casts, accessors` 

`5. **Migraciones MySQL** — Estructura completa con índices` 

`6. **Controladores y Services** — Lógica de negocio encapsulada` 

`7. **APIs Principales** — Endpoints REST + PWA sync` 

`8. **Estrategia de Testing** — Unit, Feature, E2E` 

`9. **Seguridad y Multi-tenancy** — Implementación técnica detallada` 

`10. **Despliegue y Operación** — Docker, Forge, monitoreo` 

- `📑 **Nota:** El SDD será el documento técnico definitivo que usaremos como blueprint para la Fase 5 (Desarrollo).` 

```
**¿Procedemos con la Fase 4 (SDD)?** 📑
```

