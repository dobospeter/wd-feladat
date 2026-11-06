<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\Device\Models\Device;
use Tests\Feature\Concerns\CreatesFixtures;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeviceApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function validPayloads(): array
    {
        $required = ['name' => 'Latitude', 'manufacturer' => 'Dell', 'model' => '5440'];

        return [
            'required fields only' => [$required],
            'every field' => [$required + ['description' => 'Business laptop']],
            'null description' => [$required + ['description' => null]],
            'blank description' => [$required + ['description' => '   ']],
            'longest name' => [['name' => str_repeat('n', Device::NAME_MAX)] + $required],
            'longest manufacturer' => [['manufacturer' => str_repeat('m', Device::MANUFACTURER_MAX)] + $required],
            'longest model' => [['model' => str_repeat('x', Device::MODEL_MAX)] + $required],
            'longest description' => [$required + ['description' => str_repeat('d', Device::DESCRIPTION_MAX)]],
            'accented text' => [['name' => 'Kávéfőző', 'manufacturer' => 'Jura', 'model' => 'Ő-1']],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, list<string>}>
     */
    public static function invalidPayloads(): array
    {
        $valid = ['name' => 'Latitude', 'manufacturer' => 'Dell', 'model' => '5440'];

        return [
            'empty payload' => [[], ['name', 'manufacturer', 'model']],
            'missing name' => [array_diff_key($valid, ['name' => 1]), ['name']],
            'missing manufacturer' => [array_diff_key($valid, ['manufacturer' => 1]), ['manufacturer']],
            'missing model' => [array_diff_key($valid, ['model' => 1]), ['model']],
            'null name' => [['name' => null] + $valid, ['name']],
            'empty manufacturer' => [['manufacturer' => ''] + $valid, ['manufacturer']],
            'model is not a string' => [['model' => 5440] + $valid, ['model']],
            'name is an array' => [['name' => ['x']] + $valid, ['name']],
            'name too long' => [['name' => str_repeat('n', Device::NAME_MAX + 1)] + $valid, ['name']],
            'manufacturer too long' => [
                ['manufacturer' => str_repeat('m', Device::MANUFACTURER_MAX + 1)] + $valid,
                ['manufacturer'],
            ],
            'model too long' => [['model' => str_repeat('x', Device::MODEL_MAX + 1)] + $valid, ['model']],
            'description too long' => [
                ['description' => str_repeat('d', Device::DESCRIPTION_MAX + 1)] + $valid,
                ['description'],
            ],
            'only an overlong name' => [['name' => str_repeat('x', 256)], ['name', 'manufacturer', 'model']],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('validPayloads')]
    public function testValidPayloadsCreateADevice(array $payload): void
    {
        // Arrange: the payload comes from the data provider.

        // Act
        $response = $this->postJson('/api/v1/devices', $payload);

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('devices', ['id' => $response->json('data.id')]);
        $this->assertDatabaseCount('devices', 1);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $invalidFields
     */
    #[DataProvider('invalidPayloads')]
    public function testInvalidPayloadsAreRejected(array $payload, array $invalidFields): void
    {
        // Arrange: the payload and the expected invalid fields come from the data provider.

        // Act
        $response = $this->postJson('/api/v1/devices', $payload);

        // Assert
        $response->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
        $this->assertEqualsCanonicalizing($invalidFields, array_keys($response->json('error.details')));
        $this->assertDatabaseCount('devices', 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $invalidFields
     */
    #[DataProvider('invalidPayloads')]
    public function testInvalidPayloadsAreRejectedOnUpdateToo(array $payload, array $invalidFields): void
    {
        // Arrange
        $id = $this->createDevice(['name' => 'Untouched']);

        // Act
        $response = $this->putJson("/api/v1/devices/{$id}", $payload);

        // Assert
        $response->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
        $this->assertEqualsCanonicalizing($invalidFields, array_keys($response->json('error.details')));
        $this->assertDatabaseHas('devices', ['id' => $id, 'name' => 'Untouched']);
    }

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

    public function testValidationErrorsUseTheErrorEnvelope(): void
    {
        // Arrange
        $payload = ['name' => str_repeat('x', Device::NAME_MAX + 1)];

        // Act
        $response = $this->postJson('/api/v1/devices', $payload);

        // Assert
        $response->assertUnprocessable()->assertJsonStructure([
            'error' => ['code', 'message', 'details' => ['name', 'manufacturer', 'model'], 'request_id'],
        ]);
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
            ->assertExactJson([
                'data' => [],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1],
            ]);
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
