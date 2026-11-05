<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesFixtures;
use Tests\TestCase;

class DeviceApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    public function test_a_device_can_be_created(): void
    {
        $response = $this->postJson('/api/v1/devices', [
            'name' => 'Latitude 5440',
            'manufacturer' => 'Dell',
            'model' => '5440',
            'description' => 'Business laptop',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Latitude 5440')
            ->assertJsonPath('data.manufacturer', 'Dell')
            ->assertJsonPath('data.model', '5440')
            ->assertJsonPath('data.description', 'Business laptop');

        $id = $response->json('data.id');
        $this->assertTrue(\Symfony\Component\Uid\Uuid::isValid($id));
        $response->assertHeader('Location', url("/api/v1/devices/{$id}"));
        $this->assertDatabaseHas('devices', ['id' => $id, 'manufacturer' => 'Dell']);
    }

    public function test_text_fields_are_trimmed_and_a_blank_description_becomes_null(): void
    {
        $this->postJson('/api/v1/devices', [
            'name' => '  Latitude  ',
            'manufacturer' => ' Dell ',
            'model' => ' 5440 ',
            'description' => '   ',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Latitude')
            ->assertJsonPath('data.manufacturer', 'Dell')
            ->assertJsonPath('data.description', null);
    }

    public function test_creating_a_device_validates_the_payload(): void
    {
        $this->postJson('/api/v1/devices', ['name' => str_repeat('x', 256)])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['code', 'message', 'details' => ['name', 'manufacturer', 'model'], 'request_id']]);
    }

    public function test_the_same_manufacturer_and_model_cannot_be_registered_twice(): void
    {
        $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);

        $this->postJson('/api/v1/devices', ['name' => 'Another', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'device_already_exists');
    }

    public function test_devices_are_listed_with_pagination_metadata(): void
    {
        foreach (range(1, 3) as $i) {
            $this->createDevice(['name' => "Device {$i}"]);
        }

        $this->getJson('/api/v1/devices?per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta', ['page' => 2, 'per_page' => 2, 'total' => 3, 'last_page' => 2])
            ->assertJsonPath('data.0.name', 'Device 3');
    }

    public function test_an_empty_list_is_a_valid_response(): void
    {
        $this->getJson('/api/v1/devices')
            ->assertOk()
            ->assertExactJson(['data' => [], 'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]);
    }

    public function test_pagination_parameters_are_validated(): void
    {
        $this->getJson('/api/v1/devices?per_page=1000')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['per_page']]]);
    }

    public function test_a_single_device_can_be_fetched(): void
    {
        $id = $this->createDevice(['name' => 'Fetch me']);

        $this->getJson("/api/v1/devices/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Fetch me');
    }

    public function test_an_unknown_device_is_a_404_with_the_error_envelope(): void
    {
        $this->getJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'device_not_found');
    }

    public function test_a_malformed_identifier_does_not_match_a_route(): void
    {
        $this->getJson('/api/v1/devices/not-a-uuid')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_a_device_can_be_replaced(): void
    {
        $id = $this->createDevice();

        $this->putJson("/api/v1/devices/{$id}", [
            'name' => 'Renamed',
            'manufacturer' => 'HP',
            'model' => 'EliteBook',
            'description' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Renamed')
            ->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('devices', ['id' => $id, 'manufacturer' => 'HP']);
    }

    public function test_a_device_can_be_saved_without_changing_its_unique_fields(): void
    {
        $id = $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);

        $this->putJson("/api/v1/devices/{$id}", ['name' => 'New name', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertOk();
    }

    public function test_updating_a_device_into_an_existing_manufacturer_and_model_conflicts(): void
    {
        $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);
        $id = $this->createDevice(['manufacturer' => 'HP', 'model' => 'G10']);

        $this->putJson("/api/v1/devices/{$id}", ['name' => 'X', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'device_already_exists');
    }

    public function test_updating_an_unknown_device_is_a_404(): void
    {
        $this->putJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000', [
            'name' => 'X', 'manufacturer' => 'Y', 'model' => 'Z',
        ])->assertNotFound()->assertJsonPath('error.code', 'device_not_found');
    }

    public function test_patch_is_not_supported(): void
    {
        $id = $this->createDevice();

        $this->patchJson("/api/v1/devices/{$id}", ['name' => 'X'])
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed');
    }

    public function test_a_device_can_be_deleted(): void
    {
        $id = $this->createDevice();

        $this->deleteJson("/api/v1/devices/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('devices', ['id' => $id]);
        $this->getJson("/api/v1/devices/{$id}")->assertNotFound();
        $this->deleteJson("/api/v1/devices/{$id}")->assertNotFound();
    }

    public function test_a_device_placed_on_a_contract_cannot_be_deleted(): void
    {
        $deviceId = $this->createDevice();
        $contractId = $this->createContract();
        $this->attachDevice($contractId, $deviceId, 'SN-1')->assertCreated();

        $this->deleteJson("/api/v1/devices/{$deviceId}")
            ->assertConflict()
            ->assertJsonPath('error.code', 'device_in_use');

        $this->assertDatabaseHas('devices', ['id' => $deviceId]);
    }
}
