<?php

namespace App\Services\Inventory;

use App\Models\Section;
use App\Models\Block;
use App\Models\Level;
use App\Models\Crypt;
use App\Models\CryptType;
use App\Models\CryptStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportService
{
    protected int $tenantId;
    protected array $report = [
        'success' => 0,
        'errors' => [],
        'warnings' => 0
    ];

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Procesar archivo CSV completo
     */
    public function processFile(string $filePath): array
    {
        Log::info('Iniciando processFile', ['file_path' => $filePath]);
        
        $this->report = ['success' => 0, 'errors' => [], 'warnings' => 0];
        
        // Verificar que el archivo exista
        if (!file_exists($filePath)) {
            Log::error('Archivo no existe', ['file_path' => $filePath]);
            return [
                'success' => 0,
                'errors' => [['row' => 0, 'message' => 'Archivo no encontrado: ' . $filePath]],
                'warnings' => 0
            ];
        }

        // Abrir archivo
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            Log::error('No se pudo abrir el archivo', ['file_path' => $filePath]);
            return [
                'success' => 0,
                'errors' => [['row' => 0, 'message' => 'No se pudo abrir el archivo']],
                'warnings' => 0
            ];
        }

        Log::info('Archivo abierto correctamente');

        // Leer encabezado
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            Log::error('Archivo vacío');
            return [
                'success' => 0,
                'errors' => [['row' => 0, 'message' => 'Archivo vacío o formato inválido']],
                'warnings' => 0
            ];
        }

        Log::info('Encabezados leídos', ['headers' => $header]);

        // Validar encabezados esperados
        $expectedHeaders = [
            'seccion_codigo',
            'bloque_codigo',
            'nivel_codigo',
            'cripta_codigo',
            'tipo_codigo',
            'capacidad',
            'precio'
        ];

        // Limpiar encabezados: trim de espacios y caracteres invisibles (BOM, etc.)
        $cleanHeader = array_map(function($h) {
            return trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF");
        }, $header);

        if ($cleanHeader !== $expectedHeaders) {
            fclose($handle);
            Log::error('Encabezados inválidos', [
                'expected' => $expectedHeaders,
                'got' => $header,
                'cleaned' => $cleanHeader
            ]);
            return [
                'success' => 0,
                'errors' => [[
                    'row' => 0,
                    'message' => 'Encabezados inválidos. Esperado: ' . implode(', ', $expectedHeaders) . '. Obtenido: ' . implode(', ', $cleanHeader)
                ]],
                'warnings' => 0
            ];
        }

        $rowCount = 1; // Empezar en 1 porque el header es la fila 1
        
        Log::info('Procesando filas...');
        
        // Procesar cada fila
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            
            // Saltar filas vacías
            if (count($row) < 7 || empty($row[0]) || empty($row[3])) {
                $this->report['warnings']++;
                continue;
            }

            try {
                DB::beginTransaction();
                $this->importRow($row, $rowCount);
                DB::commit();
                
                Log::info("Fila {$rowCount} procesada exitosamente");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->report['errors'][] = [
                    'row' => $rowCount,
                    'code' => $row[3] ?? 'N/A',
                    'message' => $e->getMessage()
                ];
                
                Log::error("Error en fila {$rowCount}", [
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
                
                // Continuar con la siguiente fila
                continue;
            }
        }

        fclose($handle);
        
        Log::info('Proceso completado', $this->report);
        
        return $this->report;
    }

    /**
     * Importar una fila individual
     */
    protected function importRow(array $row, int $rowNumber): void
    {
        // Extraer y limpiar datos
        [
            $secCode,
            $blkCode,
            $lvlCode,
            $cryptCode,
            $typeCode,
            $capacity,
            $price
        ] = array_map('trim', $row);

        Log::info("Procesando fila {$rowNumber}", [
            'seccion' => $secCode,
            'bloque' => $blkCode,
            'nivel' => $lvlCode,
            'cripta' => $cryptCode,
            'tipo' => $typeCode,
            'capacidad' => $capacity,
            'precio' => $price,
        ]);

        // 1. Validar Tipo de Cripta
        $cryptType = CryptType::where('code', strtolower($typeCode))->first();
        if (!$cryptType) {
            throw new \Exception(
                "Tipo de cripta '{$typeCode}' no válido. Usa: crypt, niche, mausoleum, ossuary"
            );
        }

        // 2. Validar capacidad
        if (!is_numeric($capacity) || $capacity < 1 || $capacity > $cryptType->max_capacity) {
            throw new \Exception(
                "Capacidad '{$capacity}' inválida para tipo {$typeCode}. Debe ser 1-{$cryptType->max_capacity}"
            );
        }

        // 3. Validar precio
        if (!is_numeric($price) || $price < 0) {
            throw new \Exception(
                "Precio '{$price}' inválido. Debe ser un número mayor o igual a 0"
            );
        }

        // 4. Crear/Obtener Jerarquía (Idempotente)
        $section = Section::firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'code' => strtoupper($secCode)
            ],
            [
                'name' => "Sección " . strtoupper($secCode)
            ]
        );

        $block = Block::firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'section_id' => $section->id,
                'code' => strtoupper($blkCode)
            ],
            [
                'name' => "Bloque " . strtoupper($blkCode)
            ]
        );

        $level = Level::firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'block_id' => $block->id,
                'code' => strtoupper($lvlCode)
            ],
            [
                'name' => "Nivel " . strtoupper($lvlCode),
                'height_order' => 1
            ]
        );

        // 5. Validar Unicidad de Cripta (RN-01)
        $exists = Crypt::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', $this->tenantId)
            ->where('code', strtoupper($cryptCode))
            ->exists();

        if ($exists) {
            $this->report['warnings']++;
            Log::info("Cripta duplicada omitida en fila {$rowNumber}: {$cryptCode}");
            return;
        }

        // 6. Crear Cripta
        $availableStatus = CryptStatus::where('code', 'available')->firstOrFail();

        Crypt::create([
            'tenant_id' => $this->tenantId,
            'level_id' => $level->id,
            'crypt_type_id' => $cryptType->id,
            'crypt_status_id' => $availableStatus->id,
            'code' => strtoupper($cryptCode),
            'capacity' => (int) $capacity,
            'current_occupancy' => 0,
            'price' => (float) $price,
            'is_blocked' => false,
        ]);

        $this->report['success']++;
        
        Log::info("Cripta {$cryptCode} creada exitosamente");
    }
}