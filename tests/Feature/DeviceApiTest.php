<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\Concerns\CreatesFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeviceApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    public function testADeviceCanBeCreated(): void
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

    public function testTextFieldsAreTrimmedAndABlankDescriptionBecomesNull(): void
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

    public function testCreatingADeviceValidatesThePayload(): void
    {
        $this->postJson('/api/v1/devices', ['name' => str_repeat('x', 256)])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['code', 'message', 'details' => ['name', 'manufacturer', 'model'], 'request_id']]);
    }

    public function testTheSameManufacturerAndModelCannotBeRegisteredTwice(): void
    {
        $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);

        $this->postJson('/api/v1/devices', ['name' => 'Another', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'device_already_exists');
    }

    public function testDevicesAreListedWithPaginationMetadata(): void
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

    public function testAnEmptyListIsAValidResponse(): void
    {
        $this->getJson('/api/v1/devices')
            ->assertOk()
            ->assertExactJson(['data' => [], 'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]);
    }

    public function testPaginationParametersAreValidated(): void
    {
        $this->getJson('/api/v1/devices?per_page=1000')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['per_page']]]);
    }

    public function testASingleDeviceCanBeFetched(): void
    {
        $id = $this->createDevice(['name' => 'Fetch me']);

        $this->getJson("/api/v1/devices/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Fetch me');
    }

    public function testAnUnknownDeviceIsA404WithTheErrorEnvelope(): void
    {
        $this->getJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'device_not_found');
    }

    public function testAMalformedIdentifierDoesNotMatchARoute(): void
    {
        $this->getJson('/api/v1/devices/not-a-uuid')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
    }

    public function testADeviceCanBeReplaced(): void
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

    public function testADeviceCanBeSavedWithoutChangingItsUniqueFields(): void
    {
        $id = $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);

        $this->putJson("/api/v1/devices/{$id}", ['name' => 'New name', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertOk();
    }

    public function testUpdatingADeviceIntoAnExistingManufacturerAndModelConflicts(): void
    {
        $this->createDevice(['manufacturer' => 'Dell', 'model' => '5440']);
        $id = $this->createDevice(['manufacturer' => 'HP', 'model' => 'G10']);

        $this->putJson("/api/v1/devices/{$id}", ['name' => 'X', 'manufacturer' => 'Dell', 'model' => '5440'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'device_already_exists');
    }

    public function testUpdatingAnUnknownDeviceIsA404(): void
    {
        $this->putJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000', [
            'name' => 'X', 'manufacturer' => 'Y', 'model' => 'Z',
        ])->assertNotFound()->assertJsonPath('error.code', 'device_not_found');
    }

    public function testPatchIsNotSupported(): void
    {
        $id = $this->createDevice();

        $this->patchJson("/api/v1/devices/{$id}", ['name' => 'X'])
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed');
    }

    public function testADeviceCanBeDeleted(): void
    {
        $id = $this->createDevice();

        $this->deleteJson("/api/v1/devices/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('devices', ['id' => $id]);
        $this->getJson("/api/v1/devices/{$id}")->assertNotFound();
        $this->deleteJson("/api/v1/devices/{$id}")->assertNotFound();
    }

    public function testADevicePlacedOnAContractCannotBeDeleted(): void
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
