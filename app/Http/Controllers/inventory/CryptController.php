<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Crypt;
use App\Models\CryptStatus;
use App\Models\CryptType;
use App\Models\Section;
use App\Models\Block;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CryptController extends Controller
{
    public function __construct()
    {
        // Opcional: agregar middleware de permisos aquí si usas Spatie
        // $this->middleware('permission:view-crypts')->only(['index', 'show', 'map']);
    }

    public function index(Request $request)
    {
        $query = Crypt::with(['cryptStatus', 'cryptType', 'level.block.section']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('crypt_status_id', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('crypt_type_id', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $crypts = $query->orderBy('code')->paginate(20);
        $statuses = CryptStatus::orderBy('order')->get();
        $types = CryptType::all();

        return view('inventory.crypts.index', compact('crypts', 'statuses', 'types'));
    }

    public function create()
    {
        $sections = Section::with('blocks.levels')->orderBy('code')->get();
        $statuses = CryptStatus::orderBy('order')->get();
        $types = CryptType::all();

        return view('inventory.crypts.create', compact('sections', 'statuses', 'types'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
    
        $validated = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'crypt_type_id' => 'required|exists:crypt_types,id',
            'crypt_status_id' => 'required|exists:crypt_statuses,id',
            'code' => 'required|string|max:30|unique:crypts,code,NULL,id,tenant_id,' . $tenantId,
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'dimensions' => 'nullable|string|max:50',
            'door_type' => 'nullable|in:marble,bronze,glass,stone,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['current_occupancy'] = 0;
        $validated['is_blocked'] = false;

        $crypt = Crypt::create($validated);

        // ✅ NUEVO: Usar CryptService para validar estado inicial
        $cryptService = app(\App\Services\Inventory\CryptService::class);
        try {
            $cryptService->validateForSale($crypt);
        } catch (\App\Exceptions\CryptNotAvailableException $e) {
            // Si la cripta no está disponible para venta al crearla, 
            // es un warning pero no bloquea la creación
            Log::warning("Cripta creada pero no disponible para venta: {$e->getMessage()}", [
                'crypt_id' => $crypt->id,
            ]);
        }

        return redirect()->route('inventory.crypts.index')
            ->with('success', "Cripta {$validated['code']} creada exitosamente.");
    }

    public function map(Request $request)
    {
        // Query base con eager loading
        $query = Section::with([
            'blocks.levels.crypts.cryptStatus',
            'blocks.levels.crypts.cryptType'
        ]);

        // FILTRO: Por sección específica
        if ($request->filled('section_id')) {
            $query->where('id', $request->section_id);
        }

        // FILTRO: Por estado de cripta
        if ($request->filled('status')) {
            $query->whereHas('blocks.levels.crypts.cryptStatus', function ($q) use ($request) {
                $q->where('code', $request->status);
            });
        }

        // FILTRO: Por tipo de cripta
        if ($request->filled('type')) {
            $query->whereHas('blocks.levels.crypts.cryptType', function ($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        // FILTRO: Solo disponibles
        if ($request->boolean('available_only')) {
            $query->whereHas('blocks.levels.crypts', function ($q) {
                $q->whereHas('cryptStatus', fn($sq) => $sq->where('code', 'available'))
                  ->where('is_blocked', false);
            });
        }

        $sections = $query->orderBy('order')->orderBy('name')->get();
        $statuses = CryptStatus::orderBy('order')->get();
        $types = CryptType::all();

        // Estadísticas para el resumen
        $totalCrypts = $sections->sum(fn($s) => 
            $s->blocks->sum(fn($b) => 
                $b->levels->sum(fn($l) => $l->crypts->count())
            )
        );

        $availableCrypts = $sections->sum(fn($s) => 
            $s->blocks->sum(fn($b) => 
                $b->levels->sum(fn($l) => 
                    $l->crypts->where('cryptStatus.code', 'available')->count()
                )
            )
        );

        return view('inventory.crypts.map', compact(
            'sections', 
            'statuses', 
            'types',
            'totalCrypts',
            'availableCrypts'
        ));
    }

    public function show(Crypt $crypt)
    {
        // ✅ Cargar SOLO las relaciones que existen actualmente (EPIC 2)
        $crypt->load([
            'cryptStatus',
            'cryptType',
            'level.block.section',
        ]);

        // ✅ Estadísticas placeholder hasta que se implementen EPIC 3, 4 y 5
        $totalContracts = 0;
        $activeContract = null;
        $totalWorkOrders = 0;
        $completedWorkOrders = 0;
        $totalDebts = 0;
        $pendingDebts = 0;
        $totalDocuments = 0;

        // ✅ Historial de cambios básico (placeholder hasta implementar AuditLog completo)
        $activityHistory = [
            [
                'action' => 'create',
                'description' => 'Cripta creada en el sistema',
                'user' => 'Sistema',
                'date' => $crypt->created_at,
                'icon' => 'fa-plus',
                'color' => 'emerald',
            ],
        ];

        if ($crypt->updated_at != $crypt->created_at) {
            array_unshift($activityHistory, [
                'action' => 'update',
                'description' => 'Última modificación de la cripta',
                'user' => 'Administrador',
                'date' => $crypt->updated_at,
                'icon' => 'fa-pen',
                'color' => 'indigo',
            ]);
        }

        return view('inventory.crypts.show', compact(
            'crypt',
            'totalContracts',
            'activeContract',
            'totalWorkOrders',
            'completedWorkOrders',
            'totalDebts',
            'pendingDebts',
            'totalDocuments',
            'activityHistory'
        ));
    }    

    public function edit(Crypt $crypt)
    {
        $sections = Section::with('blocks.levels')->orderBy('code')->get();
        $statuses = CryptStatus::orderBy('order')->get();
        $types = CryptType::all();

        return view('inventory.crypts.edit', compact('crypt', 'sections', 'statuses', 'types'));
    }
    
     public function update(Request $request, Crypt $crypt)
    {
        $validated = $request->validate([
            'crypt_type_id' => 'required|exists:crypt_types,id',
            'crypt_status_id' => 'required|exists:crypt_statuses,id',
            'capacity' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0',
            'dimensions' => 'nullable|string|max:50',
            'door_type' => 'nullable|in:marble,bronze,glass,stone,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $crypt->update($validated);

        return redirect()->route('inventory.crypts.show', $crypt)
            ->with('success', "Cripta {$crypt->code} actualizada exitosamente.");
    }

    public function destroy(Crypt $crypt)
    {
        // Validar que no tenga contratos activos (simplificado)
        if ($crypt->current_occupancy > 0) {
            return back()->with('error', 'No se puede eliminar una cripta que ya tiene ocupación.');
        }

        $crypt->delete();

        return redirect()->route('inventory.crypts.index')
            ->with('success', 'Cripta eliminada exitosamente.');
    }

    // --- Métodos para crear jerarquía vía AJAX ---

    public function storeSection(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20',
                'name' => 'required|string|max:100'
            ]);

            // Obtener tenant_id del usuario autenticado
            $tenantId = auth()->user()->tenant_id;
            
            // Si es SuperAdmin sin tenant_id, usar el primer tenant activo
            if (!$tenantId) {
                $tenantId = \App\Models\Tenant::where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->value('id');
                
                if (!$tenantId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay tenants disponibles. Crea un tenant primero.'
                    ], 422);
                }
            }

            // Validar que no exista duplicado
            $exists = \App\Models\Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->where('tenant_id', $tenantId)
                ->where('code', $validated['code'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe una sección con el código '{$validated['code']}'. Usa un código diferente."
                ], 422);
            }

            $validated['tenant_id'] = $tenantId;
            $section = \App\Models\Section::create($validated);

            return response()->json(['success' => true, 'data' => $section]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $e->errors()['code'] ?? $e->errors()['name'] ?? ['Error de validación'])
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear sección:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear sección: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeBlock(Request $request)
    {
        try {
            $validated = $request->validate([
                'section_id' => 'required|exists:sections,id',
                'code' => 'required|string|max:20',
                'name' => 'required|string|max:100'
            ]);

            // Usar withoutGlobalScope para encontrar la sección
            $section = \App\Models\Section::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->findOrFail($validated['section_id']);
            
            $validated['tenant_id'] = $section->tenant_id;

            // Validar que no exista duplicado
            $exists = \App\Models\Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->where('tenant_id', $validated['tenant_id'])
                ->where('section_id', $validated['section_id'])
                ->where('code', $validated['code'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe un bloque con el código '{$validated['code']}' en esta sección."
                ], 422);
            }

            $block = \App\Models\Block::create($validated);

            return response()->json(['success' => true, 'data' => $block]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $e->errors()['code'] ?? $e->errors()['name'] ?? ['Error de validación'])
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear bloque:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear bloque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeLevel(Request $request)
    {
        try {
            $validated = $request->validate([
                'block_id' => 'required|exists:blocks,id',
                'code' => 'required|string|max:20',
                'name' => 'required|string|max:100',
                'height_order' => 'required|integer|min:1'
            ]);

            // Usar withoutGlobalScope para encontrar el bloque
            $block = \App\Models\Block::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->findOrFail($validated['block_id']);
            
            $validated['tenant_id'] = $block->tenant_id;

            // Validar que no exista duplicado
            $exists = \App\Models\Level::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->where('tenant_id', $validated['tenant_id'])
                ->where('block_id', $validated['block_id'])
                ->where('code', $validated['code'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe un nivel con el código '{$validated['code']}' en este bloque."
                ], 422);
            }

            $level = \App\Models\Level::create($validated);

            return response()->json(['success' => true, 'data' => $level]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $e->errors()['code'] ?? $e->errors()['name'] ?? ['Error de validación'])
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear nivel:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nivel: ' . $e->getMessage()
            ], 500);
        }
    }

 
    /**
     * API: Obtener datos detallados de una cripta para el modal del mapa
     */
    public function apiShow(Crypt $crypt)
    {
        \Log::info('API Show llamado', ['crypt_id' => $crypt->id]);

        try {
            $crypt->load([
                'cryptStatus',
                'cryptType',
                'level.block.section',
                'contracts' => function ($q) {
                    $q->where('status', 'active')
                    ->with(['customer', 'contractType'])
                    ->orderBy('signed_at', 'desc')
                    ->limit(1);
                },
            ]);

            $activeContract = $crypt->contracts->first();

            \Log::info('Datos cargados', [
                'crypt_code' => $crypt->code,
                'status' => $crypt->cryptStatus->name ?? 'NULL',
                'type' => $crypt->cryptType->name ?? 'NULL',
            ]);

            $data = [
                'success' => true,
                'data' => [
                    'id' => $crypt->id,
                    'full_code' => $crypt->full_code,
                    'code' => $crypt->code,
                    'type' => [
                        'name' => $crypt->cryptType->name ?? 'N/A',
                        'code' => $crypt->cryptType->code ?? 'N/A',
                    ],
                    'status' => [
                        'name' => $crypt->cryptStatus->name ?? 'N/A',
                        'code' => $crypt->cryptStatus->code ?? 'N/A',
                        'color' => $crypt->cryptStatus->color ?? '#94a3b8',
                    ],
                    'capacity' => [
                        'total' => $crypt->capacity,
                        'occupied' => $crypt->current_occupancy,
                        'available' => $crypt->available_capacity,
                    ],
                    'price' => $crypt->price,
                    'location' => [
                        'section' => $crypt->level->block->section->code . ' - ' . $crypt->level->block->section->name,
                        'block' => $crypt->level->block->code . ' - ' . $crypt->level->block->name,
                        'level' => $crypt->level->code . ' - ' . $crypt->level->name,
                    ],
                    'dimensions' => $crypt->dimensions,
                    'door_type' => $crypt->door_type,
                    'is_blocked' => $crypt->is_blocked,
                    'blocked_reason' => $crypt->blocked_reason,
                    'active_contract' => $activeContract ? [
                        'number' => $activeContract->contract_number ?? 'N/A',
                        'type' => $activeContract->contractType->name ?? 'N/A',
                        'customer' => $activeContract->customer->name ?? 'N/A',
                        'customer_rfc' => $activeContract->customer->rfc ?? 'N/A',
                        'signed_at' => $activeContract->signed_at?->format('d/m/Y') ?? 'N/A',
                        'start_date' => $activeContract->start_date->format('d/m/Y'),
                        'end_date' => $activeContract->end_date?->format('d/m/Y') ?? 'Perpetuo',
                    ] : null,
                    'actions' => [
                        'show_url' => route('inventory.crypts.show', $crypt),
                        'edit_url' => route('inventory.crypts.edit', $crypt),
                    ],
                ],
            ];

            \Log::info('Respuesta API', $data);

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en apiShow', [
                'crypt_id' => $crypt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar formulario de importación masiva
     */
    public function showImport()
    {
        return view('inventory.crypts.import');
    }

    /**
     * Descargar plantilla de ejemplo para importación
     */
    public function downloadTemplate()
    {
        $filename = 'plantilla_criptas.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Columnas requeridas
        $columns = ['code', 'level_id', 'crypt_type_id', 'crypt_status_id', 'capacity', 'price', 'dimensions', 'door_type', 'notes'];
        
        // Crear CSV con BOM para UTF-8 en Excel
        $output = "\xEF\xBB\xBF";
        $output .= implode(',', $columns) . "\n";
        $output .= 'EJ-001,1,1,1,2,5000.00,1.5x2.0m,marble,Cripta de ejemplo\n';

        return response($output, 200, $headers);
    }

    /**
     * Procesar importación masiva de criptas
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:10240',
        ]);

        try {
            // TODO: Implementar lógica de importación
            return redirect()->route('inventory.crypts.index')
                ->with('success', 'Importación completada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}