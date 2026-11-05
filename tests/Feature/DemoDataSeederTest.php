<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testTheSeederFillsTheRegister(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('parties', 4);
        $this->assertDatabaseCount('devices', 6);
        $this->assertDatabaseCount('contracts', 5);
        $this->assertDatabaseCount('contract_devices', 16);
    }

    public function testRunningTheSeederAgainChangesNothing(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('parties', 4);
        $this->assertDatabaseCount('devices', 6);
        $this->assertDatabaseCount('contracts', 5);
        $this->assertDatabaseCount('contract_devices', 16);
    }

    public function testTheSeededDataCanBeListedThroughTheApi(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->getJson('/api/v1/parties')->assertOk()->assertJsonPath('meta.total', 4);
        $this->getJson('/api/v1/devices')->assertOk()->assertJsonPath('meta.total', 6);
        $this->getJson('/api/v1/contracts')->assertOk()
            ->assertJsonPath('meta.total', 5)
            ->assertJsonMissingPath('data.0.devices')
            ->assertJsonPath('data.0.party.name', 'Happy Gastro Kft.');
    }

    public function testASeededContractShowsItsDevicesAndTheSameTypeSitsOnSeveralContracts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contracts = $this->getJson('/api/v1/contracts?per_page=15')->json('data');
        $first = collect($contracts)->firstWhere('contract_number', 'SZ-2026-001');

        $this->getJson("/api/v1/contracts/{$first['id']}")
            ->assertOk()
            ->assertJsonCount(4, 'data.devices');

        // Verifone V240m is on three contracts, each unit with its own serial number.
        $this->assertDatabaseCount('contract_devices', 16);
        $this->assertSame(3, \App\Modules\Contract\Models\ContractDevice::query()
            ->whereHas('device', fn ($query) => $query->where('model', 'V240m'))
            ->distinct('contract_id')->count('contract_id'));
    }
}
