<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Block;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HierarchyController extends Controller
{
    /**
     * Constructor con autorización de seguridad
     * Solo administradores pueden acceder a esta funcionalidad
     */
    public function __construct()
    {
        // La lógica de middleware se ha movido a las rutas para compatibilidad con Laravel 11
        // Los middlewares 'auth' y 'role' ya están aplicados en la definición de la ruta
    }

    /**
     * Vista principal de gestión de jerarquía
     * Muestra árbol completo: Secciones → Bloques → Niveles
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Si es SuperAdmin sin tenant_id, usar el primer tenant activo
        if (!$tenantId && auth()->user()->hasRole('super_admin')) {
            $tenantId = \App\Models\Tenant::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->value('id');
        }

        if (!$tenantId) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes un cementerio asociado.');
        }

        // Query base con eager loading
        $query = Section::with([
            'blocks.levels' => function ($q) {
                $q->orderBy('height_order');
            }
        ])
        ->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
        ->where('tenant_id', $tenantId);

        // Filtros opcionales
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sections = $query->orderBy('order')->orderBy('code')->get();

        // Estadísticas
        $totalSections = $sections->count();
        $totalBlocks = $sections->sum(fn($s) => $s->blocks->count());
        $totalLevels = $sections->sum(fn($s) => 
            $s->blocks->sum(fn($b) => $b->levels->count())
        );

        $activeSections = $sections->where('is_active', true)->count();

        return view('inventory.hierarchy.index', compact(
            'sections',
            'totalSections',
            'totalBlocks',
            'totalLevels',
            'activeSections'
        ));
    }

    /**
     * Mostrar formulario para crear sección
     */
    public function createSection()
    {
        return view('inventory.hierarchy.sections.create');
    }

    /**
     * Guardar nueva sección
     */
    public function storeSection(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (!$tenantId && auth()->user()->hasRole('super_admin')) {
            $tenantId = \App\Models\Tenant::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->value('id');
        }

        if (!$tenantId) {
            return back()->with('error', 'No tienes un cementerio asociado.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        // Validar unicidad
        $exists = Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe una sección con el código '{$validated['code']}'."]);
        }

        $validated['tenant_id'] = $tenantId;
        $validated['is_active'] = true;

        Section::create($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección {$validated['code']} creada exitosamente.");
    }

    /**
     * Mostrar formulario para editar sección
     */
    public function editSection(Section $section)
    {
        $this->authorizeAccess($section);

        $section->load(['blocks.levels']);

        return view('inventory.hierarchy.sections.edit', compact('section'));
    }

    /**
     * Actualizar sección
     */
    public function updateSection(Request $request, Section $section)
    {
        $this->authorizeAccess($section);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Validar unicidad (excluyendo el registro actual)
        $exists = Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $section->tenant_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $section->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe una sección con el código '{$validated['code']}'."]);
        }

        $section->update($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección {$section->code} actualizada exitosamente.");
    }

    /**
     * Eliminar sección (solo si no tiene bloques)
     */
    public function destroySection(Section $section)
    {
        $this->authorizeAccess($section);

        if ($section->blocks()->count() > 0) {
            return back()->with('error', 
                "No se puede eliminar la sección '{$section->code}' porque contiene bloques. " .
                "Elimina primero todos los bloques asociados."
            );
        }

        $sectionName = $section->code;
        $section->delete();

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Sección {$sectionName} eliminada exitosamente.");
    }

    /**
     * Mostrar formulario para crear bloque
     */
    public function createBlock(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (!$tenantId && auth()->user()->hasRole('super_admin')) {
            $tenantId = \App\Models\Tenant::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->value('id');
        }

        $sections = Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $preselectedSectionId = $request->query('section_id');

        return view('inventory.hierarchy.blocks.create', compact('sections', 'preselectedSectionId'));
    }

    /**
     * Guardar nuevo bloque
     */
    public function storeBlock(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $section = Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->findOrFail($validated['section_id']);

        $this->authorizeAccess($section);

        // Validar unicidad
        $exists = Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $section->tenant_id)
            ->where('section_id', $validated['section_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe un bloque con el código '{$validated['code']}' en esta sección."]);
        }

        $validated['tenant_id'] = $section->tenant_id;
        $validated['is_active'] = true;

        Block::create($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Bloque {$validated['code']} creado exitosamente en sección {$section->code}.");
    }

    /**
     * Mostrar formulario para editar bloque
     */
    public function editBlock(Block $block)
    {
        $this->authorizeAccess($block);

        $block->load(['section', 'levels']);
        $sections = Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('inventory.hierarchy.blocks.edit', compact('block', 'sections'));
    }

    /**
     * Actualizar bloque
     */
    public function updateBlock(Request $request, Block $block)
    {
        $this->authorizeAccess($block);

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Validar unicidad (excluyendo el registro actual)
        $exists = Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $block->tenant_id)
            ->where('section_id', $validated['section_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $block->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe un bloque con el código '{$validated['code']}' en esta sección."]);
        }

        $block->update($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Bloque {$block->code} actualizado exitosamente.");
    }

    /**
     * Eliminar bloque (solo si no tiene niveles)
     */
    public function destroyBlock(Block $block)
    {
        $this->authorizeAccess($block);

        if ($block->levels()->count() > 0) {
            return back()->with('error', 
                "No se puede eliminar el bloque '{$block->code}' porque contiene niveles. " .
                "Elimina primero todos los niveles asociados."
            );
        }

        $blockName = $block->code;
        $block->delete();

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Bloque {$blockName} eliminado exitosamente.");
    }

    /**
     * Mostrar formulario para crear nivel
     */
    public function createLevel(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (!$tenantId && auth()->user()->hasRole('super_admin')) {
            $tenantId = \App\Models\Tenant::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->value('id');
        }

        $blocks = Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('section')
            ->orderBy('code')
            ->get();

        $preselectedBlockId = $request->query('block_id');

        return view('inventory.hierarchy.levels.create', compact('blocks', 'preselectedBlockId'));
    }

    /**
     * Guardar nuevo nivel
     */
    public function storeLevel(Request $request)
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'height_order' => 'required|integer|min:1',
        ]);

        $block = Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->findOrFail($validated['block_id']);

        $this->authorizeAccess($block);

        // Validar unicidad
        $exists = Level::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $block->tenant_id)
            ->where('block_id', $validated['block_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe un nivel con el código '{$validated['code']}' en este bloque."]);
        }

        $validated['tenant_id'] = $block->tenant_id;
        $validated['is_active'] = true;

        Level::create($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Nivel {$validated['code']} creado exitosamente en bloque {$block->code}.");
    }

    /**
     * Mostrar formulario para editar nivel
     */
    public function editLevel(Level $level)
    {
        $this->authorizeAccess($level);

        $level->load(['block.section']);
        $blocks = Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('is_active', true)
            ->with('section')
            ->orderBy('code')
            ->get();

        return view('inventory.hierarchy.levels.edit', compact('level', 'blocks'));
    }

    /**
     * Actualizar nivel
     */
    public function updateLevel(Request $request, Level $level)
    {
        $this->authorizeAccess($level);

        $validated = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'height_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Validar unicidad (excluyendo el registro actual)
        $exists = Level::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $level->tenant_id)
            ->where('block_id', $validated['block_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $level->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => "Ya existe un nivel con el código '{$validated['code']}' en este bloque."]);
        }

        $level->update($validated);

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Nivel {$level->code} actualizado exitosamente.");
    }

    /**
     * Eliminar nivel (solo si no tiene criptas)
     */
    public function destroyLevel(Level $level)
    {
        $this->authorizeAccess($level);

        if ($level->crypts()->count() > 0) {
            return back()->with('error', 
                "No se puede eliminar el nivel '{$level->code}' porque contiene criptas. " .
                "Elimina o reubica primero todas las criptas asociadas."
            );
        }

        $levelName = $level->code;
        $level->delete();

        return redirect()->route('inventory.hierarchy.index')
            ->with('success', "Nivel {$levelName} eliminado exitosamente.");
    }

    /**
     * Verificar acceso del usuario al elemento (multi-tenant)
     */
    private function authorizeAccess($model)
    {
        $userTenantId = auth()->user()->tenant_id;

        if (!$userTenantId && auth()->user()->hasRole('super_admin')) {
            // SuperAdmin puede acceder a cualquier tenant
            return true;
        }

        if ($model->tenant_id !== $userTenantId) {
            abort(403, 'No tienes permiso para gestionar este elemento.');
        }
    }
}
