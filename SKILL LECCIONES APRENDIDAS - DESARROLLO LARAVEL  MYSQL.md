# 🎯 SKILL: LECCIONES APRENDIDAS \- DESARROLLO LARAVEL \+ MYSQL

**Versión:** 1.0  
**Fecha:** 14 de Julio, 2026  
**Proyecto:** SGIC 2.0 \- Sistema de Gestión Integral de Criptas  
**Stack:** PHP 8.3 \+ Laravel 11 \+ MySQL 8 \+ Blade \+ Alpine.js \+ Tailwind CSS

---

## 📋 ÍNDICE

1. [Errores de Base de Datos y Eloquent](#1-errores-de-base-de-datos-y-eloquent)  
2. [Errores de Validación y Datos](#2-errores-de-validación-y-datos)  
3. [Errores de Frontend y JavaScript](#3-errores-de-frontend-y-javascript)  
4. [Errores de Arquitectura y Middleware](#4-errores-de-arquitectura-y-middleware)  
5. [Errores de Vistas Blade y Layouts](#5-errores-de-vistas-blade-y-layouts)  
6. [Errores de Migraciones](#6-errores-de-migraciones)  
7. [Errores de Multi-tenancy](#7-errores-de-multi-tenancy)  
8. [Errores de Debugging y Logs](#8-errores-de-debugging-y-logs)  
9. [Checklist Preventivo por Fase](#9-checklist-preventivo-por-fase)  
10. [Reglas de Oro (No Negociables)](#10-reglas-de-oro-no-negociables)

---

## 1\. ERRORES DE BASE DE DATOS Y ELOQUENT

### ❌ Error 1.1: Tabla no existe (plural vs singular)

**Síntoma:**

SQLSTATE\[42S02\]: Base table or view not found: 1146 Table 'sgic\_2.subscription\_histories' doesn't exist

**Causa Raíz:**

- Laravel Eloquent usa **plural automático** para nombres de tablas  
- El modelo `SubscriptionHistory` busca `subscription_histories`  
- La tabla en BD se llama `subscription_history` (singular)

**Solución:**

// En el modelo, especificar el nombre exacto de la tabla

class SubscriptionHistory extends Model

{

    protected $table \= 'subscription\_history'; // ✅ Forzar nombre singular

}

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar que el nombre de la tabla en la migración coincida con lo que Eloquent espera  
- ✅ **SIEMPRE** usar `php artisan tinker` para probar: `Model::first()` antes de usar en producción  
- ✅ **CONVENCIÓN:** Si la tabla es irregular (singular, nombre compuesto), declarar `protected $table`

---

### ❌ Error 1.2: Campo `tenant_id` no en `$fillable`

**Síntoma:**

SQLSTATE\[HY000\]: General error: 1364 Field 'tenant\_id' doesn't have a default value

**Causa Raíz:**

- El modelo usa el trait `BelongsToTenant` que asigna `tenant_id` automáticamente  
- Pero `tenant_id` **NO está en el array `$fillable`**  
- Eloquent ignora campos que no están en `$fillable` al usar `create()`  
- MySQL rechaza la inserción porque `tenant_id` es `NOT NULL`

**Solución:**

class Section extends Model

{

    use BelongsToTenant;

    protected $fillable \= \[

        'tenant\_id', // ✅ AGREGAR SIEMPRE

        'code',

        'name',

        // ... otros campos

    \];

}

**Regla Preventiva:**

- ✅ **SIEMPRE** incluir `tenant_id` en `$fillable` de todos los modelos multi-tenant  
- ✅ **SIEMPRE** verificar `$fillable` después de crear un modelo nuevo  
- ✅ **TEST:** Crear un registro con `Model::create(['tenant_id' => 1, ...])` en Tinker

---

### ❌ Error 1.3: Global Scope bloquea `findOrFail()`

**Síntoma:**

No query results for model \[App\\Models\\Block\]

**Causa Raíz:**

- El `TenantScope` filtra automáticamente por `tenant_id` del usuario autenticado  
- Si el usuario es **SuperAdmin** (sin `tenant_id`), el scope no aplica filtro  
- Pero si el usuario es **AdminCemetery**, solo ve datos de su tenant  
- Al buscar un bloque de otro tenant, `findOrFail()` lanza excepción

**Solución:**

// ✅ Usar withoutGlobalScope para búsquedas cross-tenant

$block \= Block::withoutGlobalScope(\\App\\Models\\Scopes\\TenantScope::class)

    \-\>findOrFail($validated\['block\_id'\]);

**Regla Preventiva:**

- ✅ **SIEMPRE** usar `withoutGlobalScope(TenantScope::class)` cuando:  
  - Un SuperAdmin necesita ver datos de todos los tenants  
  - Se valida existencia de un registro antes de crear un hijo  
  - Se heredan valores del padre (ej: `tenant_id` del bloque al nivel)  
- ✅ **NUNCA** usar `findOrFail()` directo en operaciones multi-tenant sin verificar el scope

---

### ❌ Error 1.4: N+1 Queries (Rendimiento)

**Síntoma:**

- Página tarda 5+ segundos en cargar  
- Laravel Debugbar muestra 100+ queries

**Causa Raíz:**

// ❌ MAL: Lazy loading (1 query por cada tenant)

$tenants \= Tenant::all();

foreach ($tenants as $tenant) {

    echo $tenant-\>cemetery-\>name; // Query adicional

}

**Solución:**

// ✅ BIEN: Eager loading (1 query total)

$tenants \= Tenant::with(\['cemetery', 'users'\])-\>get();

// ✅ BIEN: withCount para contadores

$tenants \= Tenant::withCount(\['crypts', 'users'\])-\>get();

foreach ($tenants as $tenant) {

    echo $tenant-\>crypts\_count; // Sin query adicional

}

**Regla Preventiva:**

- ✅ **SIEMPRE** usar `with()` cuando accedas a relaciones en un loop  
- ✅ **SIEMPRE** usar `withCount()` en lugar de `$model->relation()->count()`  
- ✅ **INSTALAR** Laravel Debugbar en desarrollo para detectar N+1  
- ✅ **REVISAR** el número de queries en cada página (meta: \<10 queries por vista)

---

## 2\. ERRORES DE VALIDACIÓN Y DATOS

### ❌ Error 2.1: Carbon recibe string en lugar de int

**Síntoma:**

Carbon\\Carbon::rawAddUnit(): Argument \#3 ($value) must be of type int|float, string given

**Causa Raíz:**

- Los formularios HTML envían **todos los valores como strings**  
- Laravel valida con `'subscription_months' => 'required|integer'` ✅  
- Pero **NO convierte automáticamente** el tipo de dato  
- `$validated['subscription_months']` sigue siendo `"12"` (string)  
- `now()->addMonths("12")` ❌ Carbon espera int

**Solución:**

// ✅ Cast explícito antes de usar con Carbon

$subscriptionMonths \= (int) $validated\['subscription\_months'\];

'ends\_at' \=\> now()-\>addMonths($subscriptionMonths),

**Regla Preventiva:**

- ✅ **SIEMPRE** hacer cast `(int)` o `(float)` antes de pasar valores a Carbon  
- ✅ **SIEMPRE** verificar tipos de datos en campos numéricos de formularios  
- ✅ **HELPER:** Crear un método en el controlador:

private function castToInt($value) {

    return is\_numeric($value) ? (int) $value : 0;

}

---

### ❌ Error 2.2: Validación RFC `size:13` rechaza 12 caracteres

**Síntoma:**

The rfc field must be 13 characters.

**Causa Raíz:**

- RFC de Persona Moral tiene **12 caracteres** (ej: `PSM200101ABC`)  
- RFC de Persona Física tiene **13 caracteres** (ej: `GOLM850101MDF`)  
- Validación `'rfc' => 'required|string|size:13'` ❌ rechaza 12 chars

**Solución:**

// ✅ Validación flexible

'rfc' \=\> \['required', 'string', 'min:12', 'max:13', 'unique:tenants,rfc'\],

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar requisitos de negocio antes de validar (RFC, CURP, etc.)  
- ✅ **SIEMPRE** usar `min:max` en lugar de `size:` cuando haya variabilidad  
- ✅ **DOCUMENTAR** en el código por qué se usa `min:12|max:13`

---

### ❌ Error 2.3: Duplicate entry en BD

**Síntoma:**

SQLSTATE\[23000\]: Integrity constraint violation: 1062 Duplicate entry '1-b' for key 'sections.sections\_tenant\_id\_code\_unique'

**Causa Raíz:**

- La tabla `sections` tiene un índice único compuesto: `['tenant_id', 'code']`  
- El código no valida duplicados antes de insertar  
- El usuario intenta crear una sección con código "B" que ya existe

**Solución:**

// ✅ Validar duplicados antes de insertar

$exists \= Section::withoutGlobalScope(TenantScope::class)

    \-\>where('tenant\_id', $tenantId)

    \-\>where('code', $validated\['code'\])

    \-\>exists();

if ($exists) {

    return response()-\>json(\[

        'success' \=\> false,

        'message' \=\> "Ya existe una sección con el código '{$validated\['code'\]}'"

    \], 422);

}

**Regla Preventiva:**

- ✅ **SIEMPRE** validar unicidad en el backend antes de insertar  
- ✅ **SIEMPRE** usar `unique` en validación de Laravel: `'code' => 'unique:sections,code,NULL,id,tenant_id,' . $tenantId`  
- ✅ **SIEMPRE** mostrar mensajes de error claros al usuario (no técnicos)  
- ✅ **INDEX:** Verificar que las tablas tengan índices únicos donde aplique

---

## 3\. ERRORES DE FRONTEND Y JAVASCRIPT

### ❌ Error 3.1: `Cannot read properties of undefined (reading 'push')`

**Síntoma:**

Error: Cannot read properties of undefined (reading 'push')

**Causa Raíz:**

- Alpine.js intenta hacer `section.blocks.push(data)`  
- Pero `section.blocks` es `undefined` porque la sección no tenía bloques cargados  
- El JSON de `$sections` no incluye el array `blocks` si está vacío

**Solución:**

// ✅ Inicializar array si no existe

const section \= this.sectionsData.find(s \=\> s.id \== this.selectedSection);

if (section) {

    if (\!section.blocks) {

        section.blocks \= \[\]; // ✅ Crear array vacío

    }

    section.blocks.push(data.data);

}

**Regla Preventiva:**

- ✅ **SIEMPRE** inicializar arrays en objetos JSON del backend  
- ✅ **SIEMPRE** verificar existencia antes de hacer `push()`: `if (!obj.array) obj.array = []`  
- ✅ **BACKEND:** Usar `->default([])` en casts de JSON o asegurar que las relaciones carguen arrays vacíos

---

### ❌ Error 3.2: Modales no se abren (scope de Alpine.js)

**Síntoma:**

- Botón "+" es visible pero no hace nada al hacer clic  
- No hay errores en consola

**Causa Raíz:**

\<\!-- ❌ MAL: Dos instancias separadas de x-data \--\>

\<form x-data="hierarchyManager()"\>

    \<button @click="openModal('section')"\>+\</button\>

\</form\>

\<div x-data="hierarchyManager()"\>

    \<\!-- Modales aquí (otra instancia, no ve el botón) \--\>

\</div\>

**Solución:**

\<\!-- ✅ BIEN: Todo dentro del MISMO x-data \--\>

\<div x-data="hierarchyManager()"\>

    \<form\>

        \<button @click="openModal('section')"\>+\</button\>

    \</form\>

    

    \<\!-- Modales aquí (misma instancia) \--\>

\</div\>

**Regla Preventiva:**

- ✅ **SIEMPRE** envolver formulario \+ modales \+ toast en el **MISMO** `x-data`  
- ✅ **NUNCA** crear múltiples instancias de `x-data` con la misma función  
- ✅ **VERIFICAR** que los botones `@click` estén dentro del scope del `x-data` que define el método

---

### ❌ Error 3.3: `Failed to fetch` en AJAX

**Síntoma:**

Error: Failed to fetch

**Causa Raíz:**

- El `fetch()` falla por varias razones posibles:  
  1. CSRF token faltante en el layout  
  2. Ruta no registrada  
  3. Error 500 en el backend (sin manejar)  
  4. CORS (raro en mismo dominio)

**Solución:**

// ✅ Verificar CSRF token antes de fetch

const csrfToken \= document.querySelector('meta\[name="csrf-token"\]');

if (\!csrfToken) {

    throw new Error('CSRF token no encontrado. Recarga la página.');

}

// ✅ Manejar errores del servidor

const response \= await fetch(url, { ... });

const data \= await response.json();

if (\!response.ok) {

    this.forms.section.error \= data.message || 'Error del servidor';

    console.error('Error:', data); // Ver en consola

}

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar que `<meta name="csrf-token">` exista en el layout  
- ✅ **SIEMPRE** manejar `response.ok` y mostrar errores reales (no genéricos)  
- ✅ **SIEMPRE** usar `console.error()` para debug en desarrollo  
- ✅ **BACKEND:** Retornar errores con `response()->json([...], 422)` o `500`

---

### ❌ Error 3.4: Tailwind no carga colores (solo blanco y negro)

**Síntoma:**

- Las vistas se ven en blanco y negro  
- No hay errores en consola

**Causa Raíz:**

- Conflicto entre Vite (compila Tailwind) y CDN de Tailwind  
- El layout usa `@vite(['resources/css/app.css'])` pero el CSS no está compilado  
- O el CDN de Tailwind está bloqueado por CSP

**Solución:**

\<\!-- ✅ Opción 1: Usar CDN directamente (desarrollo) \--\>

\<script src="https://cdn.tailwindcss.com"\>\</script\>

\<\!-- ✅ Opción 2: Compilar con Vite (producción) \--\>

@vite(\['resources/css/app.css', 'resources/js/app.js'\])

\<\!-- ✅ Opción 3: Ambos (fallback) \--\>

@vite(\['resources/css/app.css'\])

\<script src="https://cdn.tailwindcss.com"\>\</script\> \<\!-- Fallback \--\>

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar que Tailwind esté cargado (inspeccionar elementos, buscar clases `bg-emerald-600`)  
- ✅ **SIEMPRE** ejecutar `npm run dev` en desarrollo para compilar Tailwind  
- ✅ **VERIFICAR** que `tailwind.config.js` incluya las rutas de las vistas  
- ✅ **TEST:** Crear un `<div class="bg-red-500">` y verificar que sea rojo

---

## 4\. ERRORES DE ARQUITECTURA Y MIDDLEWARE

### ❌ Error 4.1: `Target class [tenant] does not exist`

**Síntoma:**

Illuminate\\Contracts\\Container\\BindingResolutionException

Target class \[tenant\] does not exist.

**Causa Raíz:**

- El `routes/web.php` usa `middleware(['auth', 'tenant', ...])`  
- Pero el alias `tenant` **NO está registrado** en `bootstrap/app.php`  
- Laravel busca una clase llamada literalmente `tenant` y no la encuentra

**Solución:**

// bootstrap/app.php

\-\>withMiddleware(function (Middleware $middleware) {

    $middleware-\>alias(\[

        'tenant' \=\> \\App\\Http\\Middleware\\IdentifyTenant::class, // ✅ Registrar

        'role' \=\> \\Spatie\\Permission\\Middleware\\RoleMiddleware::class,

    \]);

})

**Regla Preventiva:**

- ✅ **SIEMPRE** registrar middlewares personalizados en `bootstrap/app.php`  
- ✅ **VERIFICAR** con `php artisan route:list` que los middlewares se apliquen  
- ✅ **TEST:** Acceder a una ruta protegida y verificar que no lance "Target class does not exist"

---

### ❌ Error 4.2: SuperAdmin sin `tenant_id`

**Síntoma:**

SQLSTATE\[HY000\]: General error: 1364 Field 'tenant\_id' doesn't have a default value

**Causa Raíz:**

- El usuario `superadmin@sgic.mx` tiene `tenant_id = NULL`  
- El trait `BelongsToTenant` asigna `tenant_id` desde `auth()->user()->tenant_id`  
- Si es NULL, la inserción falla

**Solución:**

// ✅ Fallback para SuperAdmin

$tenantId \= auth()-\>user()-\>tenant\_id;

if (\!$tenantId) {

    // SuperAdmin: usar el tenant activo más reciente

    $tenantId \= Tenant::where('is\_active', true)

        \-\>orderBy('created\_at', 'desc')

        \-\>value('id');

    

    if (\!$tenantId) {

        return response()-\>json(\['message' \=\> 'No hay tenants disponibles'\], 422);

    }

}

$validated\['tenant\_id'\] \= $tenantId;

**Regla Preventiva:**

- ✅ **SIEMPRE** manejar el caso donde `auth()->user()->tenant_id` es NULL  
- ✅ **SIEMPRE** proporcionar un fallback lógico (tenant por defecto, más reciente, etc.)  
- ✅ **VALIDAR** que el usuario tenga `tenant_id` antes de crear registros multi-tenant

---

### ❌ Error 4.3: Middleware `role` con sintaxis incorrecta

**Síntoma:**

- El middleware `role:admin_cemetery|super_admin` no funciona  
- O lanza error de sintaxis

**Causa Raíz:**

- Spatie Laravel Permission usa `|` para múltiples roles  
- Pero la sintaxis correcta depende de la versión

**Solución:**

// ✅ Sintaxis correcta para Spatie v6

Route::middleware(\['role:admin\_cemetery|super\_admin'\])-\>group(...);

// ✅ O usar array

Route::middleware(\['role:admin\_cemetery', 'role:super\_admin'\])-\>group(...);

**Regla Preventiva:**

- ✅ **VERIFICAR** la versión de Spatie Permission instalada  
- ✅ **TEST:** Crear una ruta protegida y acceder con diferentes roles  
- ✅ **DOCUMENTAR** los roles disponibles en el README del proyecto

---

## 5\. ERRORES DE VISTAS BLADE Y LAYOUTS

### ❌ Error 5.1: `View [inventory.crypts.index] not found`

**Síntoma:**

InvalidArgumentException: View \[inventory.crypts.index\] not found.

**Causa Raíz:**

- El controlador retorna `view('inventory.crypts.index')`  
- Pero el archivo `resources/views/inventory/crypts/index.blade.php` **no existe**  
- O la carpeta `inventory/crypts/` no fue creada

**Solución:**

\# ✅ Crear la estructura de carpetas

mkdir \-p resources/views/inventory/crypts

touch resources/views/inventory/crypts/index.blade.php

**Regla Preventiva:**

- ✅ **SIEMPRE** crear las carpetas y archivos de vistas **antes** de probar la ruta  
- ✅ **VERIFICAR** que el nombre del archivo coincida exactamente (case-sensitive en Linux)  
- ✅ **CHECKLIST:** Antes de probar una ruta, verificar:  
  - [ ] Carpeta existe  
  - [ ] Archivo existe  
  - [ ] Nombre coincide con `view('...')`

---

### ❌ Error 5.2: `unexpected end of file, expecting "endif"`

**Síntoma:**

syntax error, unexpected end of file, expecting "elseif" or "else" or "endif"

**Causa Raíz:**

- Una directiva Blade (`@if`, `@foreach`, `@section`) se abre pero **no se cierra**  
- O el archivo fue **truncado** al copiar y pegar

**Solución:**

\<\!-- ✅ Verificar que todas las directivas estén cerradas \--\>

@if ($condition)

    ...

@endif \<\!-- ✅ Cerrar \--\>

@foreach ($items as $item)

    ...

@endforeach \<\!-- ✅ Cerrar \--\>

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar que el archivo termine correctamente (no truncado)  
- ✅ **USAR** un linter de Blade (VS Code extension: Laravel Blade Snippets)  
- ✅ **VERIFICAR** el último carácter del archivo (debe ser `>` o `}`)  
- ✅ **LIMPIAR** caché de vistas: `php artisan view:clear`

---

### ❌ Error 5.3: Layout con superposición de elementos

**Síntoma:**

- El sidebar se superpone con el contenido principal  
- El topbar no se ve correctamente

**Causa Raíz:**

- El layout no usa `flex` correctamente  
- Falta `overflow-hidden` en el contenedor principal  
- El sidebar no tiene `flex-shrink-0`

**Solución:**

\<\!-- ✅ Layout correcto \--\>

\<div class="flex h-screen overflow-hidden"\>

    \<\!-- Sidebar \--\>

    \<aside class="w-64 flex-shrink-0"\>...\</aside\>

    

    \<\!-- Main Content \--\>

    \<div class="flex-1 flex flex-col min-w-0"\>

        \<header class="h-16 flex-shrink-0"\>...\</header\>

        \<main class="flex-1 overflow-y-auto"\>...\</main\>

    \</div\>

\</div\>

**Regla Preventiva:**

- ✅ **SIEMPRE** usar `flex` \+ `h-screen` \+ `overflow-hidden` en el contenedor raíz  
- ✅ **SIEMPRE** usar `flex-shrink-0` en sidebar y header  
- ✅ **SIEMPRE** usar `flex-1` \+ `min-w-0` en el contenido principal  
- ✅ **TEST:** Redimensionar la ventana y verificar que no haya superposición

---

## 6\. ERRORES DE MIGRACIONES

### ❌ Error 6.1: `getDoctrineSchemaManager does not exist`

**Síntoma:**

BadMethodCallException: Method Illuminate\\Database\\MySqlConnection::getDoctrineSchemaManager does not exist.

**Causa Raíz:**

- Laravel 11 **eliminó** `getDoctrineSchemaManager()`  
- El código usa este método para verificar índices existentes

**Solución:**

//  MAL (Laravel 10\)

$sm \= Schema::getConnection()-\>getDoctrineSchemaManager();

$indexes \= collect($sm-\>listTableIndexes('audit\_logs'));

// ✅ BIEN (Laravel 11\)

$existingIndexes \= collect(Schema::getIndexes('audit\_logs'))

    \-\>pluck('name')

    \-\>toArray();

**Regla Preventiva:**

- ✅ **VERIFICAR** la versión de Laravel antes de usar métodos de Schema  
- ✅ **USAR** `Schema::getIndexes()` en Laravel 11+  
- ✅ **CONSULTAR** la documentación oficial de Laravel 11 para cambios breaking

---

### ❌ Error 6.2: `Duplicate key name` en migraciones

**Síntoma:**

SQLSTATE\[42000\]: Syntax error or access violation: 1061 Duplicate key name 'audit\_logs\_tenant\_id\_created\_at\_index'

**Causa Raíz:**

- La migración intenta crear un índice que **ya existe**  
- No hay verificación previa de existencia

**Solución:**

// ✅ Verificar antes de crear índice

$existingIndexes \= collect(Schema::getIndexes('audit\_logs'))

    \-\>pluck('name')

    \-\>toArray();

if (\!in\_array('audit\_logs\_tenant\_id\_created\_at\_index', $existingIndexes)) {

    $table-\>index(\['tenant\_id', 'created\_at'\]);

}

**Regla Preventiva:**

- ✅ **SIEMPRE** verificar existencia de índices antes de crearlos  
- ✅ **USAR** `try-catch` para índices opcionales  
- ✅ **DOCUMENTAR** qué índices ya existen en la tabla

---

## 7\. ERRORES DE MULTI-TENANCY

### ❌ Error 7.1: `crypts_count` no existe

**Síntoma:**

'crypts' \=\> $tenant-\>crypts\_count ?? 0, // Siempre devuelve 0

**Causa Raíz:**

- La query NO usa `withCount('crypts')`  
- Por lo tanto, `crypts_count` no existe en el modelo  
- El `?? 0` enmascara el error

**Solución:**

// ✅ Usar withCount en la query

$tenants \= Tenant::withCount(\['crypts', 'users'\])-\>get();

// Ahora sí existe

foreach ($tenants as $tenant) {

    echo $tenant-\>crypts\_count; // ✅ Funciona

}

**Regla Preventiva:**

- ✅ **SIEMPRE** usar `withCount()` cuando necesites contar relaciones  
- ✅ **NUNCA** usar `$model->relation()->count()` en un loop (N+1)  
- ✅ **VERIFICAR** que el atributo `_count` exista antes de usarlo

---

### ❌ Error 7.2: `users.roles` carga N+1

**Síntoma:**

- La vista de listado de tenants hace 100+ queries

**Causa Raíz:**

// ❌ MAL

$tenants \= Tenant::with(\['users'\])-\>get();

foreach ($tenants as $tenant) {

    foreach ($tenant-\>users as $user) {

        echo $user-\>roles-\>first()-\>name; // Query por cada usuario

    }

}

**Solución:**

// ✅ BIEN: Nested eager loading

$tenants \= Tenant::with(\['users.roles'\])-\>get();

**Regla Preventiva:**

- ✅ **SIEMPRE** usar eager loading anidado: `with(['users.roles'])`  
- ✅ **INSTALAR** Laravel Debugbar para detectar N+1  
- ✅ **REVISAR** el número de queries en cada página

---

## 8\. ERRORES DE DEBUGGING Y LOGS

### ❌ Error 8.1: Error silencioso sin logs

**Síntoma:**

- La página no carga pero no hay error visible  
- No hay nada en `storage/logs/laravel.log`

**Causa Raíz:**

- El código no tiene logs de debug  
- El error ocurre antes de llegar al log  
- O el log está en otro archivo

**Solución:**

// ✅ Agregar logs en puntos críticos

\\Log::info('=== TENANT STORE INICIO \===', $request-\>all());

\\Log::info('Paso 1: Validando datos...');

\\Log::info('✅ Validación OK', \['validated' \=\> $validated\]);

try {

    // Código

} catch (\\Exception $e) {

    \\Log::error('❌ ERROR EN TRANSACCIÓN', \[

        'message' \=\> $e-\>getMessage(),

        'file' \=\> $e-\>getFile(),

        'line' \=\> $e-\>getLine(),

    \]);

    throw $e;

}

**Regla Preventiva:**

- ✅ **SIEMPRE** agregar logs en operaciones críticas (crear, actualizar, eliminar)  
- ✅ **SIEMPRE** loguear el inicio y fin de transacciones  
- ✅ **SIEMPRE** loguear excepciones con contexto completo  
- ✅ **VERIFICAR** los logs con: `tail -f storage/logs/laravel.log`

---

### ❌ Error 8.2: No ver errores de validación reales

**Síntoma:**

validation.required

(Mensaje genérico, no dice qué campo falta)

**Causa Raíz:**

- Laravel usa mensajes de validación en inglés por defecto  
- No se personalizaron los mensajes

**Solución:**

// ✅ Mensajes personalizados

$validated \= $request-\>validate(\[

    'name' \=\> 'required|string|max:150',

    'rfc' \=\> 'required|string|min:12|max:13',

\], \[

    'name.required' \=\> 'El nombre comercial es obligatorio.',

    'rfc.required' \=\> 'El RFC es obligatorio.',

    'rfc.min' \=\> 'El RFC debe tener al menos 12 caracteres.',

\]);

**Regla Preventiva:**

- ✅ **SIEMPRE** personalizar mensajes de validación en español  
- ✅ **SIEMPRE** mostrar el nombre del campo en el error  
- ✅ **CREAR** un archivo `resources/lang/es/validation.php` con mensajes globales

---

## 9\. CHECKLIST PREVENTIVO POR FASE

### 🏗️ Fase 1: Creación de Modelo

- [ ] Modelo creado con nombre correcto (PascalCase singular)  
- [ ] `$fillable` incluye `tenant_id` (si es multi-tenant)  
- [ ] `$casts` define tipos de datos (integer, decimal, boolean, datetime)  
- [ ] Relaciones definidas (`belongsTo`, `hasMany`)  
- [ ] Scopes creados si aplica (`scopeActive`, `scopeAvailable`)  
- [ ] Accessors/Mutators si aplica  
- [ ] Trait `BelongsToTenant` agregado (si aplica)  
- [ ] **TEST:** `Model::create([...])` en Tinker funciona

### 🗄️ Fase 2: Migración

- [ ] Nombre de tabla coincide con convención Laravel (snake\_case plural)  
- [ ] Índices únicos donde aplique (`unique()`)  
- [ ] Foreign keys con `constrained()` y `onDelete()`  
- [ ] Campos `NOT NULL` tienen valor por defecto o son obligatorios  
- [ ] **TEST:** `php artisan migrate` funciona sin errores  
- [ ] **TEST:** `php artisan migrate:rollback` funciona

### Fase 3: Vista Blade

- [ ] Archivo creado en ruta correcta (`resources/views/...`)  
- [ ] Todas las directivas Blade cerradas (`@endif`, `@endforeach`)  
- [ ] Usa el layout correcto (`<x-app-layout>`)  
- [ ] Slots definidos (`title`, `header`)  
- [ ] **TEST:** La vista carga sin errores 500

### 🔌 Fase 4: Controlador

- [ ] Método retorna `view()` o `response()->json()`  
- [ ] Validación con mensajes personalizados  
- [ ] Manejo de errores con `try-catch`  
- [ ] Logs en operaciones críticas  
- [ ] **TEST:** La ruta responde correctamente

### Fase 5: Testing

- [ ] Crear registro con `Model::create()`  
- [ ] Actualizar registro con `Model::update()`  
- [ ] Eliminar registro (soft delete si aplica)  
- [ ] Verificar relaciones cargan correctamente  
- [ ] Verificar scopes filtran correctamente  
- [ ] **TEST:** No hay errores N+1 (Debugbar)

---

## 10\. REGLAS DE ORO (NO NEGOCIABLES)

### 🔴 CRÍTICAS (Siempre aplicar)

1. **`tenant_id` en `$fillable`** \- Todos los modelos multi-tenant  
2. **Cast explícito para Carbon** \- `(int)` antes de `addMonths()`, `addDays()`, etc.  
3. **Eager loading** \- `with()` para evitar N+1  
4. **CSRF token en AJAX** \- Verificar `<meta name="csrf-token">`  
5. **Mensajes de error claros** \- No técnicos, en español

### 🟡 IMPORTANTES (Casi siempre aplicar)

6. **Validar duplicados** \- Antes de insertar con índices únicos  
7. **Logs en operaciones críticas** \- Inicio, fin, errores  
8. **`withoutGlobalScope`** \- Cuando SuperAdmin necesita ver todo  
9. **Inicializar arrays** \- `if (!obj.array) obj.array = []`  
10. **Mismo scope Alpine.js** \- Formulario \+ modales en un solo `x-data`

### RECOMENDADAS (Mejores prácticas)

11. **Debugbar en desarrollo** \- Detectar N+1 y queries lentas  
12. **Mensajes de validación personalizados** \- En español, claros  
13. **Fallback para SuperAdmin** \- Cuando `tenant_id` es NULL  
14. **Verificar existencia de índices** \- Antes de crear en migraciones  
15. **Tailwind cargado** \- Verificar con un div rojo de prueba

---

## 📚 RECURSOS ADICIONALES

### Comandos Artisan Útiles

\# Ver rutas registradas

php artisan route:list

\# Ver migraciones pendientes

php artisan migrate:status

\# Limpiar caché

php artisan config:clear

php artisan route:clear

php artisan view:clear

php artisan cache:clear

\# Probar en Tinker

php artisan tinker

\>\>\> Model::first()

\>\>\> Model::create(\[...\])

\# Ver logs en tiempo real

tail \-f storage/logs/laravel.log

\# Ver queries ejecutadas (Debugbar)

\# Instalar: composer require barryvdh/laravel-debugbar \--dev

### Extensiones VS Code Recomendadas

- **Laravel Blade Snippets** \- Autocompletado de directivas Blade  
- **Laravel Extra Intellisense** \- Rutas, vistas, config  
- **PHP Intelephense** \- Análisis estático de PHP  
- **Tailwind CSS IntelliSense** \- Autocompletado de clases Tailwind  
- **ESLint** \- Linting de JavaScript/Alpine.js

### Herramientas de Debugging

- **Laravel Debugbar** \- Queries, views, routes  
- **Laravel Telescope** \- Requests, exceptions, jobs  
- **Clockwork** \- Alternativa a Debugbar  
- **Ray** \- Debugging avanzado (paid)

---

## 🔄 ACTUALIZACIONES FUTURAS

Este documento es **vivo** y se actualizará con cada nuevo error encontrado.

### Cómo agregar una nueva lección:

1. **Identificar el error** \- Síntoma, causa raíz, solución  
2. **Categorizar** \- BD, Validación, Frontend, Arquitectura, etc.  
3. **Agregar al índice** \- Si es una categoría nueva  
4. **Escribir la regla preventiva** \- Cómo evitarlo en el futuro  
5. **Actualizar el checklist** \- Si aplica a alguna fase  
6. **Commit** \- Con mensaje: `docs: agregar lección aprendida [error]`

---

**Última actualización:** 14 de Julio, 2026  
**Mantenido por:** Equipo de Desarrollo SGIC 2.0  
**Versión:** 1.0

---

## 📝 NOTAS FINALES

Este documento es la **fuente única de verdad** para evitar errores repetidos en el proyecto SGIC 2.0 y futuros proyectos Laravel.

**Regla de oro:** Si cometes un error, **agrégalo aquí inmediatamente**. El tiempo que inviertas en documentar el error te ahorrará horas de debugging en el futuro.

**¿Necesitas agregar una nueva lección?** Sigue el formato establecido y haz un pull request. 🛠️  
Aquí tienes el consolidado de las **nuevas lecciones aprendidas** derivadas de los errores recientes (Laravel 11, Seeders y Migraciones).

Copia y pega este bloque directamente en tu archivo `SKILL LECCIONES APRENDIDAS - DESARROLLO LARAVEL MYSQL.md`, preferiblemente al final de las secciones correspondientes o como nuevos puntos en las categorías existentes.

---

### ➕ AGREGAR A: `1. ERRORES DE BASE DE DATOS Y ELOQUENT` (o crear subsección de Archivos/Storage)

#### ❌ Error 1.5: Ruta de archivo incorrecta en Laravel 11 (`storage_path` vs `Storage::path`)

**Síntoma:**  
`"Archivo no encontrado"` o `FileNotFoundException` al intentar procesar un archivo recién subido (ej. importación CSV), a pesar de que la subida parece exitosa.

**Causa Raíz:**  
En Laravel 11, la configuración por defecto del disco `local` en `config/filesystems.php` cambió de `storage_path('app')` a `storage_path('app/private')`. Si el código construye la ruta manualmente con `storage_path('app/' . $path)`, generará una ruta inexistente.

**Solución:**  
Usar el método `path()` del Facade `Storage` en lugar de concatenar manualmente:

// ❌ MAL (Ruta hardcoded que falla en Laravel 11\)

$fullPath \= storage\_path('app/' . $path);

// ✅ BIEN (Respeta la configuración real del disco)

$fullPath \= \\Storage::disk('local')-\>path($path);

**Regla Preventiva:**  
✅ **SIEMPRE** usar `\Storage::disk('nombre_disco')->path($ruta)` para obtener la ruta absoluta de un archivo subido, nunca construir la ruta manualmente con `storage_path()`.

---

### ➕ AGREGAR A: `2. ERRORES DE VALIDACIÓN Y DATOS`

#### ❌ Error 2.4: `Array to string conversion` en Seeders con columnas JSON

**Síntoma:**  
`Illuminate\Database\QueryException: Array to string conversion` al intentar insertar datos en una columna de tipo `json` o `array` desde un Seeder.

**Causa Raíz:**  
Aunque el modelo Eloquent tenga `'campo' => 'array'` definido en su propiedad `$casts`, el uso de `DB::table('tabla')->insert()` o ciertos contextos de seeders bypassan el sistema de casting de Eloquent. PDO recibe un array crudo de PHP en lugar de un string JSON válido.

**Solución:**  
Forzar la conversión a string JSON explícitamente en el seeder, o usar el modelo Eloquent directamente:

// ✅ OPCIÓN A: Usar json\_encode() explícitamente (Recomendado para DB::table)

'settings' \=\> json\_encode(\[

    'key' \=\> 'value'

\])

// ✅ OPCIÓN B: Usar el Modelo Eloquent (respeta los $casts automáticamente)

\\App\\Models\\Tenant::create(\[

    'settings' \=\> \['key' \=\> 'value'\] // Eloquent lo convierte a JSON solo

\]);

**Regla Preventiva:**  
✅ **SIEMPRE** usar `json_encode()` en seeders para columnas de tipo `json`/`array` si se usa `DB::table()`, o confiar exclusivamente en `Model::create()` para que los casts de Eloquent funcionen.

---

### ➕ AGREGAR A: `6. ERRORES DE MIGRACIONES`

#### ❌ Error 6.3: `migrate:fresh` falla a mitad de camino dejando la BD inconsistente

**Síntoma:**  
El comando `php artisan migrate:fresh --seed` falla con un error (ej. "Table doesn't exist" en un `ALTER TABLE`), y comandos posteriores como `php artisan tinker` fallan diciendo que las tablas no existen.

**Causa Raíz:**  
Existe una migración "huérfana" (ej. `add_columns_to_X_table`) que intenta modificar una tabla cuya migración base (`create_X_table`) fue eliminada, renombrada o nunca se ejecutó. Al fallar a mitad del proceso, `migrate:fresh` deja la base de datos en un estado corrupto (tablas borradas pero no recreadas).

**Solución:**

1. Identificar la migración problemática revisando el error o con `dir database\migrations\*tabla*`.  
2. Eliminar el archivo de migración huérfana (si la tabla ya no es necesaria) O crear la migración base faltante.  
3. Volver a ejecutar `php artisan migrate:fresh --seed`.

**Regla Preventiva:**  
✅ **NUNCA** eliminar archivos de migración que ya hayan sido ejecutados en un entorno compartido sin crear una nueva migración de reversión (`down`).  
✅ **SIEMPRE** revisar `php artisan migrate:status` si una migración falla, para identificar archivos huérfanos antes de intentar recuperarla.

---

### 📝 Instrucciones para actualizar tu documento Skill:

1. Abre `SKILL LECCIONES APRENDIDAS - DESARROLLO LARAVEL MYSQL.md`.  
2. Pega el **Error 1.5** al final de la sección `1. ERRORES DE BASE DE DATOS Y ELOQUENT`.  
3. Pega el **Error 2.4** al final de la sección `2. ERRORES DE VALIDACIÓN Y DATOS`.  
4. Pega el **Error 6.3** al final de la sección `6. ERRORES DE MIGRACIONES`.  
5. Actualiza la versión del documento a `1.1` y la fecha de última actualización.

¿Deseas que procedamos ahora con la **Fase 2.5 (Ficha Detallada de Cripta)** o prefieres saltar al **EPIC 3 (Clientes y Contratos)**? 🛠️  
