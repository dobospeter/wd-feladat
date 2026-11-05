<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\Party\Models\Party;
use App\Modules\ApiLog\Models\ApiLog;
use App\Modules\Device\Models\Device;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function testPartiesAreCreatedWithUniqueTaxNumbers(): void
    {
        $parties = Party::factory()->count(100)->create();

        $this->assertCount(100, $parties->pluck('tax_number')->unique());
        $this->assertDatabaseCount('parties', 100);
    }

    public function testPartyStates(): void
    {
        $this->assertNull(Party::factory()->withoutTaxNumber()->create()->tax_number);

        $minimal = Party::factory()->minimal()->create();
        $this->assertNull($minimal->tax_number);
        $this->assertNull($minimal->address);
        $this->assertNull($minimal->email);
        $this->assertNull($minimal->phone);

        $this->assertDatabaseCount('parties', 2);
    }

    public function testDevicesNeverCollideOnManufacturerAndModel(): void
    {
        Device::factory()->count(100)->create();

        $this->assertDatabaseCount('devices', 100);
    }

    public function testContractsGetAPartyAndAValidPeriod(): void
    {
        $contracts = Contract::factory()->count(50)->create();

        $this->assertDatabaseCount('contracts', 50);
        $this->assertDatabaseCount('parties', 50);

        foreach ($contracts as $contract) {
            $this->assertNotNull($contract->party);
            $this->assertGreaterThanOrEqual($contract->starts_on, $contract->ends_on);
        }
    }

    public function testContractStates(): void
    {
        $this->assertNull(Contract::factory()->openEnded()->create()->ends_on);

        $expired = Contract::factory()->expired()->create();
        $this->assertSame('2024-12-31', $expired->ends_on);
    }

    public function testContractsCanBelongToTheSameParty(): void
    {
        $party = Party::factory()->create();

        Contract::factory()->count(3)->for($party)->create();

        $this->assertSame(3, Contract::query()->where('party_id', $party->id)->count());
        $this->assertDatabaseCount('parties', 1);
    }

    public function testContractsCanBeCreatedWithDevicesThatHaveDistinctSerialNumbers(): void
    {
        $contract = Contract::factory()->withDevices(5)->create()->load('devices.device');

        $this->assertCount(5, $contract->devices);
        $this->assertCount(5, $contract->devices->pluck('serial_number')->unique());
        $this->assertNotNull($contract->devices->first()->device);
    }

    public function testTheSameDeviceTypeCanBePlacedOnManyContractsWithDifferentSerials(): void
    {
        $device = Device::factory()->create();

        ContractDevice::factory()->count(3)->for($device)->create();

        $this->assertSame(3, ContractDevice::query()->where('device_id', $device->id)->count());
        $this->assertDatabaseCount('contracts', 3);
    }

    public function testApiLogsCanBeCreatedIncludingFailedOnes(): void
    {
        ApiLog::factory()->count(3)->create();
        $failed = ApiLog::factory()->failed()->create();

        $this->assertDatabaseCount('api_logs', 4);
        $this->assertGreaterThanOrEqual(404, $failed->response_status);
        $this->assertSame('POST', $failed->method);
    }

    public function testFactoriesProduceDataThatTheApiCanServe(): void
    {
        $contract = Contract::factory()->withDevices(2)->create();

        $this->getJson("/api/v1/contracts/{$contract->id}")
            ->assertOk()
            ->assertJsonPath('data.contract_number', $contract->contract_number)
            ->assertJsonCount(2, 'data.devices');
    }
}
