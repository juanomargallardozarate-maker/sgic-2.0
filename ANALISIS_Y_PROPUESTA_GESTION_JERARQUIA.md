# 📋 ANÁLISIS Y PROPUESTA: GESTIÓN DE JERARQUÍA DE INFRAESTRUCTURA

## 🔍 ANÁLISIS DEL PROBLEMA

### Contexto Actual
El sistema SGIC 2.0 cuenta con una estructura jerárquica para organizar las criptas:
```
Cementerio → Sección → Bloque → Nivel → Cripta
```

**Modelos existentes:**
- `Section` (Secciones)
- `Block` (Bloques) 
- `Level` (Niveles)
- `Crypt` (Criptas)

**Funcionalidad actual:**
1. ✅ Métodos API en `CryptController` para creación rápida:
   - `storeSection()` - Línea 254
   - `storeBlock()` - Línea 310
   - `storeLevel()` - Línea 356
2. ✅ Rutas definidas en `web.php` para estas operaciones AJAX
3. ❌ **NO EXISTE** interfaz administrativa dedicada para gestión CRUD completa
4. ❌ **NO EXISTE** opción para editar o eliminar elementos existentes
5. ❌ **NO EXISTE** vista de listado/tabla para administrar la jerarquía

### Problema Identificado
Durante importaciones masivas de criptas desde Excel/CSV, si los nombres de secciones, bloques o niveles no existen previamente, el proceso falla o crea datos inconsistentes. Actualmente no hay una forma intuitiva para que el **Administrador del Cementerio** pueda:

1. Dar de alta masivamente secciones, bloques y niveles ANTES de importar criptas
2. Corregir nombres o códigos mal capturados
3. Eliminar elementos creados por error
4. Visualizar la estructura completa del cementerio de forma organizada
5. Activar/desactivar elementos sin eliminarlos (soft delete lógico)

---

## 🎯 REQUERIMIENTO

Crear un módulo administrativo exclusivo para la **Gestión de Infraestructura** (Secciones, Bloques y Niveles) que permita:

### Funcionales (CRUD Completo)
- [ ] **ABM de Secciones**: Alta, Baja (lógica) y Modificación
- [ ] **ABM de Bloques**: Alta, Baja (lógica) y Modificación
- [ ] **ABM de Niveles**: Alta, Baja (lógica) y Modificación
- [ ] **Vista Jerárquica**: Visualización en árbol o anidada de toda la estructura
- [ ] **Validaciones**: Evitar duplicados por tenant, códigos únicos
- [ ] **Estados**: Activar/Desactivar elementos (is_active)

### No Funcionales
- [ ] **Seguridad**: Solo accesible para roles `admin_cemetery` y `super_admin`
- [ ] **Multi-tenant**: Aislamiento total por tenant_id
- [ ] **Auditoría**: Registrar todos los cambios en `audit_logs`
- [ ] **UX/UI**: Interfaz intuitiva, responsiva, consistente con Design System
- [ ] **Performance**: Carga eficiente con eager loading

---

## 📍 UBICACIÓN EN EL SISTEMA

### 1. **Ubicación en el Menú de Navegación**

**Propuesta:** Crear un submenú dentro de "Inventario" llamado **"Infraestructura"** o **"Jerarquía"**

```
📦 INVENTARIO (menú principal)
├── 🗺️ Mapa de Criptas
├── ➕ Nueva Cripta
├── 📊 Listado de Criptas
├── 📥 Importar Criptas
└── ⚙️ Infraestructura  ← NUEVO
    ├── Secciones
    ├── Bloques
    └── Niveles
```

**Alternativa:** Menú independiente de alto nivel si se considera crítico:
```
🏗️ INFRAESTRUCTURA (menú principal)
├── Secciones
├── Bloques
└── Niveles
```

**Recomendación:** Primera opción (submenú de Inventario) porque:
- La jerarquía es parte fundamental del inventario
- Mantiene agrupados todos los conceptos relacionados con criptas
- Sigue el patrón mental del usuario: "Primero configuro la estructura, luego creo las criptas"

---

### 2. **Ubicación en la Arquitectura MVC**

#### Controllers (`app/Http/Controllers/Inventory/`)
```
app/Http/Controllers/Inventory/
├── CryptController.php (existente)
├── HierarchyController.php (NUEVO - recomendado)
│   O alternativamente:
├── SectionController.php (NUEVO)
├── BlockController.php (NUEVO)
└── LevelController.php (NUEVO)
```

**Recomendación:** `HierarchyController.php` único porque:
- Las 3 entidades están íntimamente relacionadas
- Reduce duplicación de código (validaciones, autorizaciones)
- Facilita la vista jerárquica consolidada
- Los métodos `storeSection`, `storeBlock`, `storeLevel` ya existen en `CryptController`, se moverían aquí

#### Views (`resources/views/inventory/`)
```
resources/views/inventory/
├── crypts/ (existente)
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   ├── map.blade.php
│   └── import.blade.php
└── hierarchy/ (NUEVO)
    ├── index.blade.php       ← Vista principal en árbol/tabla
    ├── sections.blade.php    ← Gestión específica de secciones
    ├── blocks.blade.php      ← Gestión específica de bloques
    └── levels.blade.php      ← Gestión específica de niveles
```

#### Routes (`routes/web.php`)
```php
// Dentro del grupo middleware auth + role
Route::prefix('inventory')->name('inventory.')->group(function () {
    
    // ... rutas existentes de crypts ...
    
    // ==========================================
    // NUEVO: Gestión de Infraestructura
    // ==========================================
    Route::prefix('hierarchy')->name('hierarchy.')->group(function () {
        // Vista principal jerárquica
        Route::get('/', [\App\Http\Controllers\Inventory\HierarchyController::class, 'index'])
            ->name('index');
        
        // CRUD Secciones
        Route::get('/sections', [\App\Http\Controllers\Inventory\HierarchyController::class, 'sections'])
            ->name('sections');
        Route::post('/sections', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeSection'])
            ->name('sections.store');
        Route::put('/sections/{section}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateSection'])
            ->name('sections.update');
        Route::delete('/sections/{section}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroySection'])
            ->name('sections.destroy');
        Route::patch('/sections/{section}/toggle-status', [\App\Http\Controllers\Inventory\HierarchyController::class, 'toggleSectionStatus'])
            ->name('sections.toggle-status');
        
        // CRUD Bloques
        Route::get('/blocks', [\App\Http\Controllers\Inventory\HierarchyController::class, 'blocks'])
            ->name('blocks');
        Route::post('/blocks', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeBlock'])
            ->name('blocks.store');
        Route::put('/blocks/{block}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateBlock'])
            ->name('blocks.update');
        Route::delete('/blocks/{block}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroyBlock'])
            ->name('blocks.destroy');
        Route::patch('/blocks/{block}/toggle-status', [\App\Http\Controllers\Inventory\HierarchyController::class, 'toggleBlockStatus'])
            ->name('blocks.toggle-status');
        
        // CRUD Niveles
        Route::get('/levels', [\App\Http\Controllers\Inventory\HierarchyController::class, 'levels'])
            ->name('levels');
        Route::post('/levels', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeLevel'])
            ->name('levels.store');
        Route::put('/levels/{level}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateLevel'])
            ->name('levels.update');
        Route::delete('/levels/{level}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroyLevel'])
            ->name('levels.destroy');
        Route::patch('/levels/{level}/toggle-status', [\App\Http\Controllers\Inventory\HierarchyController::class, 'toggleLevelStatus'])
            ->name('levels.toggle-status');
        
        // API para carga dinámica (selects dependientes)
        Route::get('/sections/{section}/blocks', [\App\Http\Controllers\Inventory\HierarchyController::class, 'getBlocks'])
            ->name('sections.blocks');
        Route::get('/blocks/{block}/levels', [\App\Http\Controllers\Inventory\HierarchyController::class, 'getLevels'])
            ->name('blocks.levels');
    });
});
```

---

## 🏗️ ARQUITECTURA PROPUESTA

### Controller: `HierarchyController.php`

```php
<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Block;
use App\Models\Level;
use App\Services\Inventory\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HierarchyController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        // Middleware de permisos (opcional, se puede manejar en routes)
        // $this->middleware('permission:manage-hierarchy');
    }

    // =====================
    // VISTA PRINCIPAL
    // =====================
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        
        // Cargar jerarquía completa con eager loading
        $sections = Section::with(['blocks.levels'])
            ->where('tenant_id', $tenantId)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        // Estadísticas
        $stats = [
            'total_sections' => $sections->count(),
            'total_blocks' => $sections->sum(fn($s) => $s->blocks->count()),
            'total_levels' => $sections->sum(fn($s) => 
                $s->blocks->sum(fn($b) => $b->levels->count())
            ),
            'active_sections' => $sections->where('is_active', true)->count(),
        ];
        
        return view('inventory.hierarchy.index', compact('sections', 'stats'));
    }

    // =====================
    // SECCIONES
    // =====================
    public function storeSection(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);
        
        // Validar unicidad
        $exists = Section::where('tenant_id', $tenantId)
            ->where('code', $validated['code'])
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['code' => 'Ya existe una sección con este código']);
        }
        
        $validated['tenant_id'] = $tenantId;
        $validated['is_active'] = true;
        
        $section = Section::create($validated);
        
        // Auditoría
        $this->auditService->log('create', $section, 'Sección creada');
        
        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección '{$section->name}' creada exitosamente");
    }

    public function updateSection(Request $request, Section $section)
    {
        $this->authorizeTenant($section);
        
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);
        
        // Validar unicidad (excluyendo el registro actual)
        $exists = Section::where('tenant_id', $section->tenant_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $section->id)
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['code' => 'Ya existe otra sección con este código']);
        }
        
        $oldValues = $section->only(array_keys($validated));
        $section->update($validated);
        
        // Auditoría
        $this->auditService->log('update', $section, 'Sección actualizada', $oldValues);
        
        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección '{$section->name}' actualizada");
    }

    public function destroySection(Section $section)
    {
        $this->authorizeTenant($section);
        
        // Validar que no tenga bloques asociados
        if ($section->blocks()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: tiene bloques asociados']);
        }
        
        $sectionName = $section->name;
        $section->delete();
        
        // Auditoría
        $this->auditService->log('delete', $section, 'Sección eliminada');
        
        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección '{$sectionName}' eliminada");
    }

    public function toggleSectionStatus(Section $section)
    {
        $this->authorizeTenant($section);
        
        $newStatus = !$section->is_active;
        $section->update(['is_active' => $newStatus]);
        
        // Auditoría
        $this->auditService->log('update', $section, 
            "Sección " . ($newStatus ? 'activada' : 'desactivada'));
        
        return back()->with('success', 
            "Sección " . ($newStatus ? 'activada' : 'desactivada'));
    }

    // =====================
    // BLOQUES (patrón similar)
    // =====================
    public function storeBlock(Request $request)
    {
        // Implementación similar a Sections
        // Validar section_id pertenece al tenant
        // Validar unicidad de code dentro de la sección
    }

    public function updateBlock(Request $request, Block $block)
    {
        // Implementación similar
    }

    public function destroyBlock(Block $block)
    {
        // Validar que no tenga niveles asociados
        if ($block->levels()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: tiene niveles asociados']);
        }
    }

    // =====================
    // NIVELES (patrón similar)
    // =====================
    public function storeLevel(Request $request)
    {
        // Implementación similar
        // Validar block_id pertenece al tenant
        // Validar unicidad de code dentro del bloque
    }

    public function updateLevel(Request $request, Level $level)
    {
        // Implementación similar
    }

    public function destroyLevel(Level $level)
    {
        $this->authorizeTenant($level);
        
        // Validar que no tenga criptas asociadas
        if ($level->crypts()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: tiene criptas asociadas']);
        }
        
        $level->delete();
        
        // Auditoría
        $this->auditService->log('delete', $level, 'Nivel eliminado');
        
        return redirect()->route('inventory.hierarchy.index')
            ->with('success', 'Nivel eliminado exitosamente');
    }

    // =====================
    // APIs PARA SELECTS DEPENDIENTES
    // =====================
    public function getBlocks(Section $section)
    {
        $this->authorizeTenant($section);
        
        $blocks = $section->blocks()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
            
        return response()->json(['blocks' => $blocks]);
    }

    public function getLevels(Block $block)
    {
        $this->authorizeTenant($block);
        
        $levels = $block->levels()
            ->where('is_active', true)
            ->orderBy('height_order')
            ->get(['id', 'code', 'name']);
            
        return response()->json(['levels' => $levels]);
    }

    // =====================
    // HELPERS
    // =====================
    private function authorizeTenant($model)
    {
        $tenantId = auth()->user()->tenant_id;
        
        // SuperAdmin puede acceder a todo
        if (auth()->user()->hasRole('super_admin')) {
            return true;
        }
        
        // Admin de cemetery solo ve su tenant
        if ($model->tenant_id != $tenantId) {
            abort(403, 'No tienes permiso para gestionar este elemento');
        }
    }
}
```

---

## 🎨 DISEÑO DE INTERFAZ (Wireframe Textual)

### Vista Principal: `inventory.hierarchy.index`

```
╔══════════════════════════════════════════════════════════════════════╗
║  🏗️ GESTIÓN DE INFRAESTRUCTURA                                      ║
║  Inventario → Infraestructura                                        ║
╠══════════════════════════════════════════════════════════════════════╣
║                                                                      ║
║  ┌─────────────────────────────────────────────────────────────┐    ║
║  │ 📊 RESUMEN                                                  │    ║
║  │ ┌──────────┬──────────┬──────────┬──────────┐              │    ║
║  │ │Secciones │ Bloques  │ Niveles  │ Activos  │              │    ║
║  │ │   12     │    48    │   192    │   240    │              │    ║
║  │ └──────────┴──────────┴──────────┴──────────┘              │    ║
║  └─────────────────────────────────────────────────────────────┘    ║
║                                                                      ║
║  ┌─────────────────────────────────────────────────────────────┐    ║
║  │ 🔍 FILTROS                                                  │    ║
║  │ [Buscar: __________] [Estado: Todos ▼] [➡️ Filtrar]        │    ║
║  └─────────────────────────────────────────────────────────────┘    ║
║                                                                      ║
║  ┌─────────────────────────────────────────────────────────────┐    ║
║  │ ➕ AGREGAR                                                  │    ║
║  │ [📄 Nueva Sección]                                          │    ║
║  └─────────────────────────────────────────────────────────────┘    ║
║                                                                      ║
║  ┌─────────────────────────────────────────────────────────────┐    ║
║  │ 🌳 ESTRUCTURA DEL CEMENTERIO                                │    ║
║  │                                                             │    ║
║  │ ▼ SECCIÓN A - "San Pedro"                    [✏️] [🗑️] [⚙️] │    ║
║  │   Código: A | Orden: 1 | Estado: 🟢 Activo                 │    ║
║  │   ┌─────────────────────────────────────────────────────┐  │    ║
║  │   │ ▶ BLOQUE 1                             [✏️][🗑️][⚙️] │  │    ║
║  │   │   Código: 1 | Orden: 1                              │  │    ║
║  │   │   ┌───────────────────────────────────────────────┐│  │    ║
║  │   │   │ • Nivel 1 (Piso)             [✏️][🗑️][⚙️]   ││  │    ║
║  │   │   │   Orden: 1 | Criptas: 12                      ││  │    ║
║  │   │   ├───────────────────────────────────────────────┤│  │    ║
║  │   │   │ • Nivel 2 (Primer Piso)      [✏️][🗑️][⚙️]   ││  │    ║
║  │   │   │   Orden: 2 | Criptas: 12                      ││  │    ║
║  │   │   └───────────────────────────────────────────────┘│  │    ║
║  │   │                                                     │  │    ║
║  │   │ ▶ BLOQUE 2                             [✏️][🗑️][⚙️] │  │    ║
║  │   │   Código: 2 | Orden: 2                              │  │    ║
║  │   │   ┌───────────────────────────────────────────────┐│  │    ║
║  │   │   │ • Nivel 1 (Piso)             [✏️][🗑️][⚙️]   ││  │    ║
║  │   │   │   Orden: 1 | Criptas: 8                       ││  │    ║
║  │   │   └───────────────────────────────────────────────┘│  │    ║
║  │   └─────────────────────────────────────────────────────┘  │    ║
║  │                                                             │    ║
║  │ ▼ SECCIÓN B - "Jardín de la Luz"             [✏️] [🗑️] [⚙️] │    ║
║  │   Código: B | Orden: 2 | Estado: 🟢 Activo                 │    ║
║  │   ...                                                       │    ║
║  │                                                             │    ║
║  │ ▸ SECCIÓN C - "Osarios"                      [✏️] [🗑️] [⚙️] │    ║
║  │   Código: C | Orden: 3 | Estado: 🔴 Inactivo               │    ║
║  │                                                             │    ║
║  └─────────────────────────────────────────────────────────────┘    ║
║                                                                      ║
║  [← Anterior]  Página 1 de 3  [Siguiente →]                         ║
║                                                                      ║
╚══════════════════════════════════════════════════════════════════════╝
```

### Modal: Nueva Sección

```
╔══════════════════════════════════════════════════════════════╗
║  ➕ NUEVA SECCIÓN                                     [X]   ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  Código *                                                    ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ A                                                   │     ║
║  └────────────────────────────────────────────────────┘     ║
║  Ej: "A", "SAN_PEDRO", "1"                                 ║
║                                                              ║
║  Nombre *                                                    ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ San Pedro                                           │     ║
║  └────────────────────────────────────────────────────┘     ║
║  Nombre descriptivo de la sección                           ║
║                                                              ║
║  Descripción                                                 ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ Sección principal del cementerio                    │     ║
║  │ (opcional)                                          │     ║
║  └────────────────────────────────────────────────────┘     ║
║                                                              ║
║  Orden de visualización                                      ║
║  ┌──────┐                                                    ║
║  │  1   │  (Menor número = aparece primero)                 ║
║  └──────┘                                                    ║
║                                                              ║
║  ┌──────────────────────────────────────────────────────┐   ║
║  │ [❌ Cancelar]          [💾 Guardar Sección]          │   ║
║  └──────────────────────────────────────────────────────┘   ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```

### Modal: Editar Bloque

```
╔══════════════════════════════════════════════════════════════╗
║  ✏️ EDITAR BLOQUE                                     [X]   ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  Sección Padre                                               ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ A - San Pedro                                       │ 🔒  ║
║  └────────────────────────────────────────────────────┘     ║
║  (No editable)                                               ║
║                                                              ║
║  Código *                                                    ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ 1                                                   │     ║
║  └────────────────────────────────────────────────────┘     ║
║                                                              ║
║  Nombre *                                                    ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ Bloque 1                                            │     ║
║  └────────────────────────────────────────────────────┘     ║
║                                                              ║
║  Descripción                                                 ║
║  ┌────────────────────────────────────────────────────┐     ║
║  │ Primer bloque de la sección A                       │     ║
║  └────────────────────────────────────────────────────┘     ║
║                                                              ║
║  Orden de visualización                                      ║
║  ┌──────┐                                                    ║
║  │  1   │                                                    ║
║  └──────┘                                                    ║
║                                                              ║
║  ┌──────────────────────────────────────────────────────┐   ║
║  │ [❌ Cancelar]          [💾 Guardar Cambios]          │   ║
║  └──────────────────────────────────────────────────────┘   ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```

---

## 🔐 SEGURIDAD Y PERMISOS

### Roles Requeridos
```php
// En routes/web.php
Route::middleware(['auth', 'role:super_admin|admin_cemetery'])
    ->prefix('inventory/hierarchy')
    ->name('inventory.hierarchy.')
    ->group(function () {
        // ... rutas ...
    });
```

### Permisos Granulares (Spatie)
```php
// En RolesAndPermissionsSeeder
$permissions = [
    'view-hierarchy',
    'create-section',
    'edit-section',
    'delete-section',
    'create-block',
    'edit-block',
    'delete-block',
    'create-level',
    'edit-level',
    'delete-level',
];

// Rol admin_cemetery tiene todos los permisos de hierarchy
$adminCemetery->givePermissionTo($permissions);

// Rol operativo solo puede ver
$operativo->givePermissionTo('view-hierarchy');
```

---

## 📝 PLAN DE IMPLEMENTACIÓN

### Fase 1: Backend (2-3 días)
1. [ ] Crear `HierarchyController.php` con todos los métodos CRUD
2. [ ] Mover métodos `storeSection`, `storeBlock`, `storeLevel` desde `CryptController`
3. [ ] Agregar validaciones de negocio (no eliminar con hijos, unicidad)
4. [ ] Integrar con `AuditService` para logging
5. [ ] Crear methods helper `authorizeTenant`

### Fase 2: Rutas y Middleware (0.5 día)
1. [ ] Definir rutas en `web.php`
2. [ ] Configurar middleware de roles/permisos
3. [ ] Agregar APIs para selects dependientes

### Fase 3: Frontend - Vistas (2-3 días)
1. [ ] Crear `resources/views/inventory/hierarchy/index.blade.php`
   - Vista en árbol con Alpine.js o Vue.js para colapsar/expandir
   - Botones de acción por cada nivel (editar, eliminar, toggle status)
   - Modal para nueva sección/bloque/nivel
2. [ ] Crear modales reutilizables con Livewire o Alpine.js
3. [ ] Agregar notificaciones toast para feedback

### Fase 4: UI/UX y Testing (1-2 días)
1. [ ] Aplicar Design System (Tailwind CSS)
2. [ ] Testing manual de todos los flujos CRUD
3. [ ] Validar restricciones de eliminación
4. [ ] Verificar auditoría de cambios
5. [ ] Pruebas de multi-tenant

### Fase 5: Documentación y Deploy (0.5 día)
1. [ ] Actualizar documentación del sistema
2. [ ] Capacitación a administradores
3. [ ] Deploy a producción

**Total estimado: 6-10 días hombre**

---

## 🔄 FLUJO DE USO TÍPICO

### Escenario: Configuración inicial antes de importación masiva

1. **Admin accede** a `Inventario → Infraestructura`
2. **Visualiza** estructura actual (vacía o parcial)
3. **Crea Secciones:**
   - Click en "Nueva Sección"
   - Ingresa código "A", nombre "San Pedro"
   - Guarda
   - Repite para todas las secciones necesarias
4. **Crea Bloques:**
   - Expande sección "A"
   - Click en "+ Agregar Bloque" dentro de la sección
   - Ingresa código "1", nombre "Bloque 1"
   - Guarda
   - Repite para todos los bloques de cada sección
5. **Crea Niveles:**
   - Expande bloque "1"
   - Click en "+ Agregar Nivel"
   - Ingresa código "1", nombre "Piso", orden 1
   - Guarda
   - Repite para niveles 2, 3, 4...
6. **Verifica** estructura completa en vista de árbol
7. **Procede** a importar criptas desde Excel

**Resultado:** La importación masiva encuentra todos los niveles jerárquicos existentes y asigna correctamente cada cripta.

---

## ⚠️ CONSIDERACIONES ESPECIALES

### 1. **Reglas de Negocio para Eliminación**
```
❌ NO eliminar Sección si tiene Bloques
❌ NO eliminar Bloque si tiene Niveles
❌ NO eliminar Nivel si tiene Criptas
✅ SÍ permitir desactivar (is_active = false)
```

### 2. **Manejo de Errores en Importación**
Cuando se importen criptas y no exista la jerarquía:
- **Opción A (Recomendada):** Error claro: "La sección 'X' no existe. Registre la infraestructura primero."
- **Opción B:** Crear automáticamente la jerarquía faltante (puede generar basura)
- **Opción C:** Permitir mapeo durante importación: "¿A qué sección existente mapea 'X'?"

### 3. **Performance con Grandes Volúmenes**
Si un cementerio tiene 50+ secciones, 200+ bloques, 800+ niveles:
- Usar **lazy loading** en el árbol (cargar bloques al expandir sección)
- Implementar **paginación** por sección
- Agregar **índices** en BD: `tenant_id`, `is_active`, `order`

### 4. **Auditoría Detallada**
Registrar en `audit_logs`:
```json
{
  "action": "update",
  "model_type": "Section",
  "model_id": 15,
  "old_values": {"name": "San Pedro", "code": "A"},
  "new_values": {"name": "San Pedro II", "code": "A"},
  "user_id": 42,
  "ip_address": "192.168.1.100",
  "timestamp": "2026-07-16T10:30:00Z"
}
```

---

## 📊 MÉTRICAS DE ÉXITO

| Métrica | Meta | Instrumento de Medición |
|---------|------|------------------------|
| Tiempo de configuración inicial | < 30 min | Time-tracking de usuarios |
| Errores en importación por jerarquía faltante | -90% | Logs de importación |
| Satisfacción de administradores | > 8/10 | Encuesta UX post-implementación |
| Reducción de soporte técnico por este tema | -80% | Tickets de soporte |

---

## 🎯 CONCLUSIÓN Y RECOMENDACIÓN

**Ubicación recomendada:**
- **Menú:** `Inventario → Infraestructura` (submenú)
- **Controller:** `app/Http/Controllers/Inventory/HierarchyController.php`
- **Vistas:** `resources/views/inventory/hierarchy/`
- **Rutas:** Prefijo `inventory/hierarchy` bajo middleware `role:super_admin|admin_cemetery`

**Justificación:**
1. ✅ Centraliza toda la gestión de jerarquía en un solo lugar
2. ✅ Exclusivo para administradores (requisito del usuario)
3. ✅ Previene errores en importaciones masivas
4. ✅ Mejora UX al proporcionar CRUD completo (no solo creación)
5. ✅ Mantiene consistencia con arquitectura MVC existente
6. ✅ Facilita auditoría y trazabilidad de cambios

**Prioridad:** **ALTA** - Es un habilitador crítico para la importación masiva efectiva de criptas.

---

*Documento elaborado para SGIC 2.0 | Fecha: Julio 2026 | Autor: Asistente de Desarrollo Senior*
