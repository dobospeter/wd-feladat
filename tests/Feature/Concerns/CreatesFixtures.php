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
    protected function createContract(array $overrides = []): string
    {
        $this->sequence++;

        $response = $this->postJson('/api/v1/contracts', $overrides + [
            'contract_number' => "C-{$this->sequence}",
            'partner_name' => 'Happy Gastro Kft.',
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
