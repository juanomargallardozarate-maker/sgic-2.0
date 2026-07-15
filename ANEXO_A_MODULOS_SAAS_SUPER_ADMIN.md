# ANEXO A — MÓDULOS SAAS SUPER ADMIN (SGIC 2.0)

**Documento:** Anexo A al PRD/SDD SGIC 2.0  
**Versión:** 1.0  
**Fecha:** Julio 2026  
**Autor:** Product Manager Senior  
**Estado:** Aprobado  

---

## 📑 RESUMEN EJECUTIVO

Este anexo detalla los módulos exclusivos del **Super Admin SaaS** para la gestión comercial, contractual y financiera de los tenants (cementeros) que se suscriben a la plataforma SGIC 2.0. Estos módulos no son visibles para los usuarios de los tenants (Admin Cementerio, Administrativo, Operativo).

### Alcance
- Gestión del ciclo de vida completo del tenant (onboarding → operación → baja)
- Contratos digitales de suscripción SaaS con firma electrónica
- Procesamiento de pagos recurrentes y únicos mediante múltiples pasarelas
- Cumplimiento fiscal mexicano (CFDI 4.0)
- Auditoría y trazabilidad de todas las operaciones comerciales

---

## 1. GESTIÓN DE TENANTS

### 1.1 Registro y Onboarding de Tenants

#### US-SA-1.1: Alta de nuevo tenant mediante wizard
```
COMO: Super Admin o proceso automatizado de signup
QUIERO: Registrar un nuevo cementerio como tenant del SaaS
PARA: Habilitar su acceso a la plataforma y comenzar su período de prueba
```

**Criterios de Aceptación:**
- ✅ Wizard de 4 pasos: Información Organización → Datos Contacto → Selección Plan → Configuración Inicial
- ✅ Validación en tiempo real de RFC único
- ✅ Generación automática de subdominio/namespacing (`{slug}.sgic.mx` o `sgic.mx/tenant/{slug}`)
- ✅ Creación automática de usuario Admin del tenant con credenciales temporales
- ✅ Envío automático de email de bienvenida con credentials y next steps
- ✅ Asignación inicial de plan "Prueba 14 días" o plan seleccionado

#### US-SA-1.2: Suspensión/Activación de tenants
```
COMO: Super Admin
QUIERO: Suspender o reactivar un tenant
PARA: Controlar el acceso por incumplimiento de pago o solicitud del cliente
```

**Criterios de Aceptación:**
- ✅ Toggle de estado (Activo/Suspendido/Baja) en perfil del tenant
- ✅ Al suspender: bloqueo inmediato de acceso para todos los usuarios del tenant
- ✅ Mensaje personalizado en pantalla de login informando motivo de suspensión
- ✅ Job programado para suspensión automática por mora >30 días
- ✅ Email automático de notificación de suspensión/reactivación

---

## 2. MÓDULO DE CONTRATOS SAAS

### 2.1 Plantillas de Contratos

#### US-SA-2.1: Gestión de plantillas de contratos
```
COMO: Super Admin
QUIERO: Crear y editar plantillas de contrato SaaS con variables dinámicas
PARA: Personalizar los términos contractuales según el plan y promociones vigentes
```

**Criterios de Aceptación:**
- ✅ Editor WYSIWYG para diseño de plantillas (Tiptap/Quill)
- ✅ Variables disponibles: `{razon_social}`, `{rfc}`, `{domicilio}`, `{plan}`, `{precio_mensual}`, `{fecha_inicio}`, `{vigencia}`, `{nombre_representante}`, `{cargo}`
- ✅ Versionado de plantillas (v1.0, v1.1, etc.)
- ✅ Previsualización en tiempo real con datos de ejemplo
- ✅ Plantillas por tipo de plan: Trial, Emprendedor, Pyme, Empresarial, Corporate

### 2.2 Generación y Firma de Contratos

#### US-SA-2.2: Generación de contrato para nuevo tenant
```
COMO: Super Admin o sistema automatizado
QUIERO: Generar contrato digital personalizado para cada nuevo tenant
PARA: Formalizar la relación comercial SaaS antes del inicio del servicio
```

**Criterios de Aceptación:**
- ✅ Generación automática de PDF al completar wizard de alta
- ✅ Inclusión de logo del tenant y de SGIC en encabezado
- ✅ Cálculo automático de precios según plan seleccionado (mensual/anual)
- ✅ Cláusulas específicas por plan (límites de usuarios, criptas, almacenamiento)
- ✅ Sección de anexos técnicos (SLA, políticas de uso, privacidad)

#### US-SA-2.3: Firma electrónica de contratos
```
COMO: Representante legal del tenant
QUIERO: Firmar electrónicamente el contrato SaaS
PARA: Validar legalmente el acuerdo sin necesidad de impresión física
```

**Criterios de Aceptación:**
- ✅ Captura de firma mediante canvas (firma manuscrita digital)
- ✅ Opción de subir firma escaneada (PNG/JPG con fondo transparente)
- ✅ Registro de IP, timestamp y user agent al momento de firmar
- ✅ Doble factor de autenticación para validación (email + SMS)
- ✅ Generación de acuse de recibo con folio único
- ✅ Envío automático de copia firmada a ambas partes (PDF con sello de "FIRMADO")

### 2.3 Visualización e Impresión de Contratos

#### US-SA-2.4: Consulta de contratos vigentes e históricos
```
COMO: Super Admin o Admin del tenant
QUIERO: Consultar y descargar contratos firmados
PARA: Revisar términos contractuales y mantener expediente digital
```

**Criterios de Aceptación:**
- ✅ Lista de contratos con filtros: vigente, vencido, cancelado, por vencer
- ✅ Vista previa embebida del PDF (sin descarga obligatoria)
- ✅ Descarga de PDF firmado con marca de agua "COPIA NO OFICIAL" si no es original
- ✅ Historial de versiones si hubo renovaciones o addendums
- ✅ Búsqueda por número de contrato, RFC, razón social

#### US-SA-2.5: Impresión de contratos para archivo físico
```
COMO: Admin del tenant o Super Admin
QUIERO: Imprimir contrato en formato carta/oficio
PARA: Resguardo físico o requisitos legales municipales
```

**Criterios de Aceptación:**
- ✅ Botón "Imprimir" que abre diálogo nativo del navegador
- ✅ Formato optimizado para impresión (márgenes, saltos de página, encabezados)
- ✅ Inclusión de códigos QR de validación en cada hoja
- ✅ Numeración automática de páginas "X de Y"
- ✅ Opción de incluir/excluir anexos técnicos en impresión

---

## 3. MÓDULO DE PAGOS EN LÍNEA

### 3.1 Integración de Pasarelas de Pago

#### US-SA-3.1: Configuración de múltiples pasarelas
```
COMO: Super Admin
QUIERO: Configurar Stripe, PayPal y Mercado Pago como métodos de pago
PARA: Ofrecer flexibilidad a los tenants y maximizar la conversión
```

**Criterios de Aceptación:**
- ✅ Panel de configuración con campos para API Keys de cada proveedor (Publishable Key, Secret Key, Webhook Secret)
- ✅ Modo sandbox/producción toggleable por pasarela
- ✅ Webhooks configurables para cada proveedor (URLs automáticas: `/api/webhooks/stripe`, `/api/webhooks/paypal`, `/api/webhooks/mercadopago`)
- ✅ Prueba de conectividad con cada API antes de activar
- ✅ Orden de prioridad configurable (ej. Stripe primero, luego MP, luego PayPal)

#### US-SA-3.2: Pagos con tarjeta de crédito/débito (Stripe)
```
COMO: Tenant (Admin Cementerio)
QUIERO: Pagar mi suscripción SaaS con tarjeta de crédito o débito
PARA: Formalizar mi suscripción de manera inmediata y segura
```

**Criterios de Aceptación:**
- ✅ Formulario de pago embebido con Stripe Elements (campos tokenizados, PCI compliant)
- ✅ Soporte para tarjetas mexicanas e internacionales (Visa, MasterCard, AMEX)
- ✅ 3D Secure 2.0 para autenticación reforzada
- ✅ Guardado seguro de tarjeta para cobros recurrentes (tokenización Stripe Customer)
- ✅ Manejo de errores: tarjeta declinada, fondos insuficientes, expirada
- ✅ Recibo inmediato por email con desglose (subtotal, IVA, total)

#### US-SA-3.3: Pagos con PayPal
```
COMO: Tenant
QUIERO: Pagar con mi cuenta de PayPal
PARA: Usar saldo existente o tarjetas vinculadas a PayPal
```

**Criterios de Aceptación:**
- ✅ Botón "Pagar con PayPal" que abre popup oficial de PayPal
- ✅ Redirección de retorno exitosa a dashboard del tenant
- ✅ Captura automática del payment ID y status
- ✅ Soporte para PayPal Checkout y PayPal Credit
- ✅ Manejo de disputas y chargebacks desde panel Super Admin

#### US-SA-3.4: Pagos con Mercado Pago
```
COMO: Tenant
QUIERO: Pagar con Mercado Pago (efectivo, tarjeta, saldo MP)
PARA: Acceder a métodos de pago locales populares en México
```

**Criterios de Aceptación:**
- ✅ Checkout Pro (redirección a Mercado Pago) y Checkout API (embebido)
- ✅ Generación de tickets para pago en OXXO, 7-Eleven, Farmacias
- ✅ Códigos QR para pago móvil
- ✅ Conciliación automática cuando Mercado Pago confirma el pago
- ✅ Soporte para pagos en pesos mexicanos (MXN)

### 3.2 Cobros Recurrentes y Suscripciones

#### US-SA-3.5: Facturación recurrente mensual/anual
```
COMO: Sistema SGIC
QUIERO: Cobrar automáticamente la suscripción cada ciclo
PARA: Mantener la continuidad del servicio sin intervención manual
```

**Criterios de Aceptación:**
- ✅ Creación de suscripciones en Stripe Billing / PayPal Subscriptions / MP Subscriptions
- ✅ Intentos de cobro automáticos los días 1, 5 y 10 del mes
- ✅ Reintentos inteligentes con backoff exponencial
- ✅ Notificaciones por email 3 días antes del cobro, día del cobro, y tras fallo
- ✅ Actualización automática del estado del tenant tras 3 intentos fallidos (→ suspendido)

#### US-SA-3.6: Upgrades y downgrades de plan
```
COMO: Tenant
QUIERO: Cambiar mi plan de suscripción
PARA: Ajustar el servicio a mis necesidades cambiantes
```

**Criterios de Aceptación:**
- ✅ Prorrateo automático del cambio de plan (cobro/reembolso proporcional)
- ✅ Aplicación inmediata o en siguiente ciclo (configurable)
- ✅ Nuevo contrato generado automáticamente si cambia plan significativamente
- ✅ Notificación de confirmación con nuevo monto y fecha de próximo cobro

### 3.3 Gestión de Transacciones y Facturación

#### US-SA-3.7: Historial de transacciones
```
COMO: Super Admin o Tenant
QUIERO: Consultar el historial completo de pagos
PARA: Auditoría interna y conciliación contable
```

**Criterios de Aceptación:**
- ✅ Tabla con columnas: Fecha, Concepto, Método, Monto, Estado, Folio Fiscal, Descargar XML/PDF
- ✅ Filtros por rango de fechas, método de pago, estado (exitoso, fallido, reembolsado)
- ✅ Exportación a CSV/Excel para contabilidad
- ✅ Búsqueda por número de transacción, orden de compra o RFC

#### US-SA-3.8: Timbrado de CFDI 4.0
```
COMO: Sistema SGIC
QUIERO: Generar facturas electrónicas timbradas por SAT
PARA: Cumplir con obligaciones fiscales mexicanas
```

**Criterios de Aceptación:**
- ✅ Integración con PAC autorizado (Facturama, Finkok, Ecodex)
- ✅ Captura de datos fiscales del tenant (RFC, régimen fiscal, código postal, uso de CFDI)
- ✅ Timbrado automático tras confirmación de pago
- ✅ Envío de XML y PDF al email del tenant
- ✅ Cancelación de facturas con motivo válido (01-08 del SAT)
- ✅ Almacenamiento mínimo 5 años según normativa SAT

#### US-SA-3.9: Reembolsos y notas de crédito
```
COMO: Super Admin
QUIERO: Procesar reembolsos parciales o totales
PARA: Atender solicitudes de cancelación o ajustes comerciales
```

**Criterios de Aceptación:**
- ✅ Botón "Reembolsar" en detalle de transacción
- ✅ Selección de monto (total o parcial)
- ✅ Motivo obligatorio (cancelación, error, promoción, cortesía)
- ✅ Generación automática de nota de crédito con timbre CFDI
- ✅ Reembolso a misma tarjeta/medio de pago original
- ✅ Tiempo de procesamiento: 5-10 días hábiles (según banco)

---

## 4. REPORTES FINANCIEROS SAAS

### 4.1 Dashboard Financiero Global

#### US-SA-4.1: KPIs financieros en tiempo real
```
COMO: Super Admin
QUIERO: Visualizar métricas clave del negocio SaaS
PARA: Tomar decisiones estratégicas basadas en datos
```

**KPIs Incluidos:**
- 📊 **MRR (Monthly Recurring Revenue):** Ingreso recurrente mensual total
- 📈 **ARR (Annual Recurring Revenue):** Proyección anualizada
- 💳 **Churn Rate:** Tasa de cancelación mensual
- 🎯 **LTV (Lifetime Value):** Valor promedio de vida del cliente
- 💰 **ARPU (Average Revenue Per User):** Ingreso promedio por tenant
- 📉 **Cartera Vencida:** Monto total en mora >30 días

### 4.2 Reportes Detallados

#### US-SA-4.2: Reporte de ingresos por plan
```
COMO: Super Admin
QUIERO: Desglosar ingresos por tipo de plan
PARA: Identificar planes más rentables y oportunidades de upsell
```

**Criterios de Aceptación:**
- ✅ Gráfico de barras apiladas por mes (últimos 12 meses)
- ✅ Tabla con: Plan, Tenants Activos, MRR, % del Total, Crecimiento MoM
- ✅ Drill-down a lista de tenants por plan
- ✅ Exportación a PDF/Excel

#### US-SA-4.3: Reporte de métodos de pago preferidos
```
COMO: Super Admin
QUIERO: Analizar distribución de métodos de pago
PARA: Optimizar comisiones y negociar con proveedores
```

**Criterios de Aceptación:**
- ✅ Gráfico circular: % Stripe vs PayPal vs Mercado Pago vs Transferencia
- ✅ Comparativo de comisiones por método
- ✅ Tasa de éxito por pasarela
- ✅ Recomendaciones de optimización (ej. "Mercado Pago tiene 15% menos comisiones en pagos OXXO")

---

## 5. AUDITORÍA Y CUMPLIMIENTO

### 5.1 Logs de Auditoría Comercial

#### US-SA-5.1: Trazabilidad de operaciones críticas
```
COMO: Super Admin / Auditor externo
QUIERO: Registrar todas las acciones sensibles del módulo SaaS
PARA: Cumplir con normativas y detectar fraudes
```

**Eventos Auditados:**
- ✅ Alta/modificación/baja de tenant
- ✅ Generación/firma de contrato
- ✅ Cambio de plan o precio
- ✅ Procesamiento de pago (exitoso/fallido)
- ✅ Reembolso o nota de crédito
- ✅ Timbrado de CFDI
- ✅ Suspensión/reactivación de tenant
- ✅ Cambio de configuración de pasarelas de pago

**Campos por Log:**
- Timestamp (UTC + timezone local)
- Usuario (ID, nombre, rol, IP)
- Acción (CRUD + descripción)
- Entidad afectada (tenant_id, contrato_id, transacción_id)
- Valores antes/después (diff JSON)
- Firma hash para integridad

### 5.2 Cumplimiento Normativo

#### US-SA-5.2: Retención documental
```
COMO: Sistema SGIC
QUIERO: Conservar contratos y facturas por períodos legales
PARA: Atender auditorías del SAT y autoridades mercantiles
```

**Criterios de Aceptación:**
- ✅ Contratos: 10 años mínimos (Código de Comercio)
- ✅ Facturas CFDI: 5 años mínimos (CFF Art. 30)
- ✅ Logs de auditoría: 7 años
- ✅ Backup automático diario con retención de 30 días
- ✅ Encriptación AES-256 en reposo y TLS 1.3 en tránsito

---

## 6. REQUERIMIENTOS TÉCNICOS

### 6.1 Seguridad

- 🔒 **PCI DSS Level 1:** No almacenar números de tarjeta completos (usar tokenización Stripe/MP/PayPal)
- 🔐 **Encriptación:** AES-256 para datos sensibles (API keys, tokens)
- 🛡️ **Protección CSRF/XSS:** Tokens en todos los formularios, sanitización de inputs
- 👥 **RBAC:** Roles Super Admin, Soporte, Finanzas, Auditor
- 📝 **2FA:** Obligatorio para Super Admin en operaciones críticas

### 6.2 Performance

- ⚡ **Tiempo de carga:** <2s para dashboard financiero
- 📦 **Paginación:** Máx 50 registros por página en tablas
- 🗄️ **Índices DB:** En `tenant_id`, `status`, `created_at`, `payment_method`
- 🔄 **Cache:** Redis para KPIs calculados (refresh cada 5 min)

### 6.3 Escalabilidad

- 📈 **Horizontal:** Microservicios independientes para pagos y facturación
- 🌍 **CDN:** Cloudflare para assets estáticos (contratos PDF, logos)
- 🗂️ **Storage:** AWS S3 o DigitalOcean Spaces para documentos (contratos, facturas)

---

## 7. CRITERIOS DE ACEPTACIÓN GENERALES

### 7.1 Funcionales
- ✅ Todos los flujos descritos funcionan end-to-end sin errores
- ✅ Mensajes de error claros y accionables para el usuario
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Soporte multi-navegador (Chrome, Firefox, Safari, Edge últimas 2 versiones)

### 7.2 No Funcionales
- ✅ Disponibilidad 99.9% SLA
- ✅ Tiempo de respuesta API <500ms p95
- ✅ Recuperación ante desastres (RTO <4h, RPO <15min)

### 7.3 Legales
- ✅ Contratos válidos bajo legislación mexicana (Código Civil Federal)
- ✅ CFDI timbrados conforme a especificaciones SAT 4.0
- ✅ Aviso de privacidad conforme a LFPDPPP
- ✅ Términos y condiciones aceptados explícitamente

---

## 8. ROADMAP DE IMPLEMENTACIÓN

| Fase | Módulo | Sprint | Prioridad |
|------|--------|--------|-----------|
| 1 | Gestión de Tenants (CRUD + Suspensiones) | S1-S2 | 🔴 Crítica |
| 2 | Módulo de Contratos (Plantillas + Firma) | S3-S4 | 🔴 Crítica |
| 3 | Stripe (Tarjetas + Recurrente) | S5-S6 | 🔴 Crítica |
| 4 | PayPal + Mercado Pago | S7-S8 | 🟠 Alta |
| 5 | Facturación CFDI 4.0 | S9-S10 | 🔴 Crítica |
| 6 | Reportes Financieros + BI | S11-S12 | 🟡 Media |
| 7 | Auditoría + Compliance | S13 | 🟢 Baja |

---

## 9. GLOSARIO

| Término | Definición |
|---------|------------|
| **Tenant** | Cementerio o empresa gestora que contrata el SaaS |
| **MRR** | Monthly Recurring Revenue (Ingreso Recurrente Mensual) |
| **Churn** | Tasa de cancelación de suscripciones |
| **CFDI** | Comprobante Fiscal Digital por Internet (México) |
| **PAC** | Proveedor Autorizado de Certificación (timbrado fiscal) |
| **PCI DSS** | Payment Card Industry Data Security Standard |
| **3D Secure** | Protocolo de autenticación para pagos online |
| **Tokenización** | Reemplazo de datos sensibles por tokens no reversibles |

---

## 10. REFERENCIAS

- **PRD SGIC 2.0:** Documento principal de requisitos de producto
- **SDD SGIC 2.0:** Documento de diseño de sistema
- **Especificaciones SAT CFDI 4.0:** https://www.sat.gob.mx/aplicacion/operacion/31068/guias-de-comprobante-fiscal-digital-cfdi
- **Stripe API Docs:** https://stripe.com/docs/api
- **PayPal Developer:** https://developer.paypal.com/
- **Mercado Pago Developers:** https://www.mercadopago.com.mx/developers/

---

**FIN DEL ANEXO A**

*Este documento complementa y forma parte integral del PRD y SDD de SGIC 2.0. Cualquier modificación debe ser aprobada por el Product Owner y el Tech Lead.*
