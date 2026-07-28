<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Facades\Tenancy;

/**
 * Seeder para datos de auditoría de ejemplo
 * 
 * NOTA: En producción, los registros de auditoría se generan automáticamente
 * a través del trait Auditable y el AuditService. Este seeder es solo para
 * propósitos de testing o demostración.
 */
class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los tenants
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->command->warn('No hay tenants registrados. Saltando seed de auditoría.');
            return;
        }
        
        foreach ($tenants as $tenant) {
            // Inicializar contexto del tenant
            Tenancy::initialize($tenant);
            
            $this->command->info("Generando logs de auditoría para tenant: {$tenant->name} (ID: {$tenant->id})");
            
            // Crear algunos registros de ejemplo para demostración
            $sampleLogs = [
                [
                    'action' => 'created',
                    'model_type' => 'App\\Models\\InterestRate',
                    'model_id' => 1,
                    'description' => 'Se creó InterestRate #1 - Tasa de interés inicial',
                    'old_values' => null,
                    'new_values' => ['min_months' => 1, 'max_months' => 3, 'interest_rate' => 5.0],
                    'tags' => ['interestrate', 'created', 'setup'],
                    'reason' => 'Configuración inicial del sistema',
                ],
                [
                    'action' => 'updated',
                    'model_type' => 'App\\Models\\InterestRate',
                    'model_id' => 1,
                    'description' => 'Se actualizó InterestRate #1: interest_rate',
                    'old_values' => ['interest_rate' => 5.0],
                    'new_values' => ['interest_rate' => 5.5],
                    'tags' => ['interestrate', 'updated'],
                    'reason' => 'Ajuste por inflación',
                ],
                [
                    'action' => 'created',
                    'model_type' => 'App\\Models\\Contract',
                    'model_id' => 1,
                    'description' => 'Se creó Contract #1 - Primer contrato de nicho',
                    'old_values' => null,
                    'new_values' => ['contract_number' => '2026-001', 'status' => 'active'],
                    'tags' => ['contract', 'created'],
                    'reason' => null,
                ],
                [
                    'action' => 'updated',
                    'model_type' => 'App\\Models\\Contract',
                    'model_id' => 1,
                    'description' => 'Se actualizó Contract #1: status',
                    'old_values' => ['status' => 'active'],
                    'new_values' => ['status' => 'paid_off'],
                    'tags' => ['contract', 'updated', 'critical'],
                    'reason' => 'Pago completo del contrato',
                ],
                [
                    'action' => 'deleted',
                    'model_type' => 'App\\Models\\Reservation',
                    'model_id' => 999,
                    'description' => 'Se eliminó Reservation #999 - Reserva cancelada',
                    'old_values' => ['reservation_code' => 'RES-999', 'status' => 'cancelled'],
                    'new_values' => null,
                    'tags' => ['reservation', 'deleted', 'critical'],
                    'reason' => 'Cancelación solicitada por el cliente',
                ],
            ];
            
            // Insertar logs de ejemplo
            foreach ($sampleLogs as $logData) {
                AuditLog::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => 1, // Usuario admin por defecto
                    'action' => $logData['action'],
                    'model_type' => $logData['model_type'],
                    'model_id' => $logData['model_id'],
                    'description' => $logData['description'],
                    'old_values' => $logData['old_values'],
                    'new_values' => $logData['new_values'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder',
                    'url' => '/admin/seed',
                    'tags' => $logData['tags'],
                    'reason' => $logData['reason'],
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
            
            // Finalizar contexto del tenant
            Tenancy::end();
        }
        
        $totalLogs = AuditLog::count();
        $this->command->info("✓ Seeder completado. Total de logs de auditoría: {$totalLogs}");
    }
}
