<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyDatabaseStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:verify {--table=} {--verbose}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la estructura de la base de datos y compara con las migraciones';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Verificando estructura de la base de datos...');
        $this->newLine();

        // 1. Verificar migraciones ejecutadas
        $this->section('Migraciones Ejecutadas');
        $this->showMigrationsStatus();

        // 2. Verificar tablas existentes
        $this->section('Tablas en la Base de Datos');
        $this->showExistingTables();

        // 3. Verificar si hay discrepancias
        $this->section('Análisis de Discrepancias');
        $this->analyzeDiscrepancies();

        // 4. Mostrar detalles de una tabla específica si se solicita
        if ($table = $this->option('table')) {
            $this->section("Estructura de la tabla: {$table}");
            $this->showTableStructure($table);
        }

        return Command::SUCCESS;
    }

    /**
     * Mostrar el estado de las migraciones
     */
    private function showMigrationsStatus(): void
    {
        try {
            $migrations = DB::table('migrations')
                ->orderBy('id', 'asc')
                ->get(['id', 'migration', 'batch']);

            if ($migrations->isEmpty()) {
                $this->warn('⚠️ No hay migraciones registradas en la tabla migrations');
                return;
            }

            $rows = $migrations->map(fn($m) => [
                $m->id,
                $m->migration,
                $m->batch
            ])->toArray();

            $this->table(['ID', 'Migración', 'Batch'], $rows);
            $this->info("Total: {$migrations->count()} migraciones ejecutadas");
        } catch (\Exception $e) {
            $this->error('❌ Error al leer migraciones: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar todas las tablas existentes
     */
    private function showExistingTables(): void
    {
        try {
            $tables = Schema::getAllTables();
            
            $tablesWithRows = [];
            foreach ($tables as $table) {
                $tableName = is_array($table) ? reset($table) : $table;
                
                try {
                    $rowCount = DB::table($tableName)->count();
                    $tablesWithRows[] = [$tableName, $rowCount];
                } catch (\Exception $e) {
                    $tablesWithRows[] = [$tableName, 'N/A'];
                }
            }

            $this->table(['Tabla', 'Filas'], $tablesWithRows);
            $this->info("Total: " . count($tables) . " tablas encontradas");
        } catch (\Exception $e) {
            $this->error('❌ Error al obtener tablas: ' . $e->getMessage());
        }
    }

    /**
     * Analizar discrepancias entre migraciones y tablas reales
     */
    private function analyzeDiscrepancies(): void
    {
        try {
            // Obtener migraciones
            $migrations = DB::table('migrations')->pluck('migration');
            
            // Obtener tablas reales
            $tables = collect(Schema::getAllTables())->map(fn($t) => is_array($t) ? reset($t) : $t);

            // Tablas del sistema que no necesitan migración
            $systemTables = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 
                           'password_reset_tokens', 'sessions', 'telescope_entries', 'telescope_entries_tags',
                           'telescope_monitoring'];

            // Extraer nombres de tablas esperadas de las migraciones
            $expectedTables = $migrations->flatMap(function($migration) {
                if (preg_match('/create_(\w+)_table/', $migration, $matches)) {
                    return [$matches[1]];
                }
                if (preg_match('/add_\w+_to_(\w+)_table/', $migration, $matches)) {
                    return [$matches[1]];
                }
                return [];
            })->unique()->values();

            // Verificar tablas que deberían existir
            $missingTables = $expectedTables->diff($tables)->diff($systemTables);
            $extraTables = $tables->diff($expectedTables)->diff($systemTables);

            if ($missingTables->isNotEmpty()) {
                $this->error('⚠️ Tablas faltantes (en migraciones pero no en BD):');
                $this->bulletList($missingTables->toArray());
            } else {
                $this->info('✅ Todas las tablas esperadas existen');
            }

            if ($extraTables->isNotEmpty()) {
                $this->warn('ℹ️ Tablas adicionales (en BD pero no en migraciones recientes):');
                $this->bulletList($extraTables->toArray());
            }

        } catch (\Exception $e) {
            $this->error('❌ Error en análisis: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar estructura detallada de una tabla
     */
    private function showTableStructure(string $table): void
    {
        try {
            if (!Schema::hasTable($table)) {
                $this->error("❌ La tabla '{$table}' no existe");
                return;
            }

            $columns = Schema::getColumnListing($table);
            
            $columnDetails = [];
            foreach ($columns as $column) {
                $type = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
                $columnDetails[] = [
                    $column,
                    $type->Type ?? 'N/A',
                    $type->Null ?? 'N/A',
                    $type->Key ?? 'N/A',
                    $type->Default ?? 'NULL'
                ];
            }

            $this->table(['Columna', 'Tipo', 'Null', 'Key', 'Default'], $columnDetails);

            // Mostrar índices
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            if (!empty($indexes)) {
                $this->info("\nÍndices:");
                $indexList = collect($indexes)->pluck('Key_name')->unique()->toArray();
                $this->bulletList($indexList);
            }

            // Mostrar filas de ejemplo
            $count = DB::table($table)->count();
            $this->info("\nTotal de registros: {$count}");
            
            if ($count > 0 && $this->option('verbose')) {
                $sample = DB::table($table)->limit(3)->get();
                $this->info("\nMuestra de datos:");
                $this->table(array_keys((array)$sample->first() ?? []), $sample->toArray());
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper para mostrar lista con bullets
     */
    private function bulletList(array $items): void
    {
        foreach ($items as $item) {
            $this->line("   • {$item}");
        }
    }

    /**
     * Helper para crear secciones
     */
    private function section(string $title): void
    {
        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("  {$title}");
        $this->info("═══════════════════════════════════════");
    }
}
