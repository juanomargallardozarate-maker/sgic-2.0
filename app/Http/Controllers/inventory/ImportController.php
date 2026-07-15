<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Mostrar vista de importación
     */
    public function index()
    {
        return view('inventory.crypts.import');
    }

    /**
     * Descargar plantilla CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_criptas_sgic.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Encabezados exactos
            fputcsv($file, [
                'seccion_codigo',
                'bloque_codigo',
                'nivel_codigo',
                'cripta_codigo',
                'tipo_codigo',
                'capacidad',
                'precio'
            ]);
            
            // Datos de ejemplo
            fputcsv($file, ['A', '1', '1', '001', 'crypt', '2', '15000.00']);
            fputcsv($file, ['A', '1', '1', '002', 'niche', '1', '8500.50']);
            fputcsv($file, ['B', '2', '1', '001', 'mausoleum', '4', '45000.00']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Procesar importación de CSV
     */
    public function import(Request $request)
    {
        // Validar archivo
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
        ], [
            'file.required' => 'Debes seleccionar un archivo CSV.',
            'file.mimes' => 'Solo se permiten archivos CSV.',
            'file.max' => 'El archivo no debe superar los 10MB.',
        ]);

        try {
            // Verificar que el archivo exista
            if (!$request->hasFile('file')) {
                return back()->with('error', 'No se recibió ningún archivo.');
            }

            $file = $request->file('file');
            
            // Verificar que el archivo sea válido
            if (!$file->isValid()) {
                return back()->with('error', 'El archivo subido no es válido.');
            }

            // Obtener tenant_id
            $tenantId = auth()->user()->tenant_id;
            
            // Fallback para SuperAdmin
            if (!$tenantId) {
                $tenantId = \App\Models\Tenant::where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->value('id');
                    
                if (!$tenantId) {
                    return back()->with('error', 'No hay tenants disponibles para importar.');
                }
            }

            Log::info('Iniciando importación', [
                'tenant_id' => $tenantId,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            // Guardar archivo en disco local
            $path = $file->store('imports', 'local');
            
            if (!$path) {
                Log::error('Error al guardar archivo');
                return back()->with('error', 'Error al guardar el archivo.');
            }

            Log::info('Archivo guardado', ['path' => $path]);

            // ✅ CORRECCIÓN: Usar Storage::path() para obtener ruta correcta
            $fullPath = Storage::disk('local')->path($path);
            
            Log::info('Ruta completa del archivo', ['full_path' => $fullPath]);

            // Verificar que el archivo exista físicamente
            if (!file_exists($fullPath)) {
                Log::error('Archivo no encontrado en ruta: ' . $fullPath);
                return back()->with('error', 'El archivo no se guardó correctamente.');
            }

            // Procesar importación
            Log::info('Iniciando servicio de importación');
            $service = new ImportService($tenantId);
            $report = $service->processFile($fullPath);
            
            Log::info('Importación completada', $report);
            
            // Limpiar archivo temporal
            Storage::disk('local')->delete($path);

            // Calcular estadísticas
            $totalRows = $report['success'] + $report['warnings'] + count($report['errors']);

            // Retornar con reporte
            if (empty($report['errors']) && $report['success'] > 0) {
                return back()->with([
                    'success' => "Importación completada: {$report['success']} criptas creadas exitosamente.",
                    'import_stats' => [
                        'total' => $totalRows,
                        'success' => $report['success'],
                        'warnings' => $report['warnings'],
                        'errors' => count($report['errors']),
                    ]
                ]);
            }

            return back()->with('import_report', $report);
            
        } catch (\Exception $e) {
            Log::error('Error fatal en importación', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error fatal: ' . $e->getMessage());
        }
    }
}