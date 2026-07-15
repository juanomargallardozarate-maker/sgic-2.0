<?php

use App\Models\Crypt;
use App\Models\CryptStatus;
use App\Models\Section;
use App\Models\Block;
use App\Models\Level;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Inventory\CryptService;
use App\Exceptions\CryptNotAvailableException;

beforeEach(function () {
    // Crear tenant y usuario de prueba
    $this->tenant = Tenant::factory()->create(['is_active' => true]);
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $this->user->assignRole('admin_cemetery');

    // Crear jerarquía
    $this->section = Section::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'TEST',
        'name' => 'Sección Test',
    ]);

    $this->block = Block::create([
        'tenant_id' => $this->tenant->id,
        'section_id' => $this->section->id,
        'code' => '1',
        'name' => 'Bloque Test',
    ]);

    $this->level = Level::create([
        'tenant_id' => $this->tenant->id,
        'block_id' => $this->block->id,
        'code' => '1',
        'name' => 'Nivel Test',
        'height_order' => 1,
    ]);

    // Obtener estados
    $this->availableStatus = CryptStatus::where('code', 'available')->first();
    $this->occupiedStatus = CryptStatus::where('code', 'occupied')->first();
    $this->reservedStatus = CryptStatus::where('code', 'reserved')->first();
    $this->blockedStatus = CryptStatus::where('code', 'blocked_debt')->first();

    $this->actingAs($this->user);
});

test('valida que cripta disponible puede venderse', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->availableStatus->id,
        'code' => 'TEST-001',
        'capacity' => 2,
        'current_occupancy' => 0,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $service->validateForSale($crypt);

    expect(true)->toBeTrue();
});

test('lanza excepción si cripta está ocupada', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->occupiedStatus->id,
        'code' => 'TEST-002',
        'capacity' => 2,
        'current_occupancy' => 1,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $service->validateForSale($crypt);
})->throws(CryptNotAvailableException::class);

test('lanza excepción si cripta está bloqueada', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->availableStatus->id,
        'code' => 'TEST-003',
        'capacity' => 2,
        'current_occupancy' => 0,
        'price' => 15000,
        'is_blocked' => true,
        'blocked_reason' => 'Morosidad',
    ]);

    $service = app(CryptService::class);
    $service->validateForSale($crypt);
})->throws(CryptNotAvailableException::class);

test('incrementa ocupación correctamente', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->availableStatus->id,
        'code' => 'TEST-004',
        'capacity' => 4,
        'current_occupancy' => 2,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $service->incrementOccupancy($crypt);

    expect($crypt->fresh()->current_occupancy)->toBe(3);
});

test('no permite incrementar si está llena', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->occupiedStatus->id,
        'code' => 'TEST-005',
        'capacity' => 2,
        'current_occupancy' => 2,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $service->incrementOccupancy($crypt);
})->throws(\DomainException::class);

test('bloquea cripta por morosidad', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->occupiedStatus->id,
        'code' => 'TEST-006',
        'capacity' => 2,
        'current_occupancy' => 1,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $service->blockForDebt($crypt, 3);

    expect($crypt->fresh()->is_blocked)->toBeTrue();
    expect($crypt->fresh()->cryptStatus->code)->toBe('blocked_debt');
});

test('desbloquea cripta tras pago', function () {
    $crypt = Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->blockedStatus->id,
        'code' => 'TEST-007',
        'capacity' => 2,
        'current_occupancy' => 1,
        'price' => 15000,
        'is_blocked' => true,
        'blocked_reason' => 'Morosidad',
    ]);

    $service = app(CryptService::class);
    $service->unblockAfterPayment($crypt);

    expect($crypt->fresh()->is_blocked)->toBeFalse();
    expect($crypt->fresh()->cryptStatus->code)->toBe('occupied');
});

test('obtiene estadísticas de inventario', function () {
    // Crear criptas de prueba
    Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->availableStatus->id,
        'code' => 'STAT-001',
        'capacity' => 2,
        'current_occupancy' => 0,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    Crypt::create([
        'tenant_id' => $this->tenant->id,
        'level_id' => $this->level->id,
        'crypt_type_id' => 1,
        'crypt_status_id' => $this->occupiedStatus->id,
        'code' => 'STAT-002',
        'capacity' => 2,
        'current_occupancy' => 1,
        'price' => 15000,
        'is_blocked' => false,
    ]);

    $service = app(CryptService::class);
    $stats = $service->getInventoryStats();

    expect($stats['total'])->toBeGreaterThanOrEqual(2);
    expect($stats['available'])->toBeGreaterThanOrEqual(1);
    expect($stats['occupied'])->toBeGreaterThanOrEqual(1);
});