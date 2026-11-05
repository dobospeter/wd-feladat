<?php

namespace Tests\Feature\Concerns;

trait CreatesFixtures
{
    private int $sequence = 0;

    /**
     * @param array<string, mixed> $overrides
     */
    protected function createDevice(array $overrides = []): string
    {
        $this->sequence++;

        $response = $this->postJson('/api/v1/devices', $overrides + [
            'name' => "Device {$this->sequence}",
            'manufacturer' => 'Dell',
            'model' => "Model {$this->sequence}",
            'description' => 'A test device',
        ]);

        $response->assertCreated();

        return $response->json('data.id');
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function createParty(array $overrides = []): string
    {
        $this->sequence++;

        $response = $this->postJson('/api/v1/parties', $overrides + [
            'name' => "Party {$this->sequence}",
            'tax_number' => null,
            'address' => '1051 Budapest, Test utca 1.',
            'email' => "party{$this->sequence}@example.com",
            'phone' => '+36 1 234 5678',
        ]);

        $response->assertCreated();

        return $response->json('data.id');
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function createContract(array $overrides = []): string
    {
        $this->sequence++;

        $partyId = $overrides['party_id'] ?? $this->createParty();

        $response = $this->postJson('/api/v1/contracts', $overrides + [
            'contract_number' => "C-{$this->sequence}",
            'party_id' => $partyId,
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'notes' => null,
        ]);

        $response->assertCreated();

        return $response->json('data.id');
    }

    protected function attachDevice(string $contractId, string $deviceId, string $serialNumber): \Illuminate\Testing\TestResponse
    {
        return $this->postJson("/api/v1/contracts/{$contractId}/devices", [
            'device_id' => $deviceId,
            'serial_number' => $serialNumber,
        ]);
    }
}
