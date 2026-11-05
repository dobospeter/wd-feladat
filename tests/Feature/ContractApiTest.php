<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\Concerns\CreatesFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    private const string MISSING_ID = '0199c0de-0000-7000-8000-000000000000';

    public function testAContractCanBeCreated(): void
    {
        $partyId = $this->createParty(['name' => 'Happy Gastro Kft.', 'tax_number' => '12345678-2-42']);

        $response = $this->postJson('/api/v1/contracts', [
            'contract_number' => 'SZ-2026-001',
            'party_id' => $partyId,
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'notes' => 'Framework contract',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.contract_number', 'SZ-2026-001')
            ->assertJsonPath('data.party.id', $partyId)
            ->assertJsonPath('data.party.name', 'Happy Gastro Kft.')
            ->assertJsonPath('data.party.tax_number', '12345678-2-42')
            ->assertJsonPath('data.starts_on', '2026-01-01')
            ->assertJsonPath('data.ends_on', '2026-12-31')
            ->assertJsonPath('data.notes', 'Framework contract')
            ->assertJsonPath('data.devices', []);

        $id = $response->json('data.id');
        $response->assertHeader('Location', url("/api/v1/contracts/{$id}"));
        $this->assertDatabaseHas('contracts', ['id' => $id, 'contract_number' => 'SZ-2026-001']);
    }

    public function testTextFieldsAreTrimmedAndBlankNotesBecomeNull(): void
    {
        $this->postJson('/api/v1/contracts', [
            'contract_number' => '  C-TRIM  ',
            'party_id' => $this->createParty(),
            'starts_on' => '2026-01-01',
            'notes' => '   ',
        ])
            ->assertCreated()
            ->assertJsonPath('data.contract_number', 'C-TRIM')
            ->assertJsonPath('data.notes', null);
    }

    public function testAContractNeedsAnExistingParty(): void
    {
        $this->postJson('/api/v1/contracts', [
            'contract_number' => 'NO-PARTY',
            'party_id' => self::MISSING_ID,
            'starts_on' => '2026-01-01',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'party_not_found')
            ->assertJsonStructure(['error' => ['details' => ['party_id']]]);

        $this->assertDatabaseCount('contracts', 0);
    }

    public function testUpdatingAContractToAnUnknownPartyIsRejected(): void
    {
        $contractId = $this->createContract();

        $this->putJson("/api/v1/contracts/{$contractId}", [
            'contract_number' => 'X',
            'party_id' => self::MISSING_ID,
            'starts_on' => '2026-01-01',
        ])->assertUnprocessable()->assertJsonPath('error.code', 'party_not_found');
    }

    public function testThePartyIdMustBeAUuid(): void
    {
        $this->postJson('/api/v1/contracts', [
            'contract_number' => 'BAD-PARTY', 'party_id' => 'acme', 'starts_on' => '2026-01-01',
        ])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['party_id']]]);
    }

    public function testTheListAndTheDetailBothEmbedTheParty(): void
    {
        $partyId = $this->createParty(['name' => 'Embedded Kft.']);
        $contractId = $this->createContract(['party_id' => $partyId]);

        $this->getJson('/api/v1/contracts')
            ->assertOk()
            ->assertJsonPath('data.0.party.id', $partyId)
            ->assertJsonPath('data.0.party.name', 'Embedded Kft.');

        $this->getJson("/api/v1/contracts/{$contractId}")
            ->assertOk()
            ->assertJsonPath('data.party.id', $partyId);
    }

    public function testAContractCanBeOpenEnded(): void
    {
        $this->postJson('/api/v1/contracts', [
            'contract_number' => 'OPEN-1',
            'party_id' => $this->createParty(),
            'starts_on' => '2026-01-01',
        ])->assertCreated()->assertJsonPath('data.ends_on', null);
    }

    public function testCreatingAContractValidatesThePayload(): void
    {
        $this->postJson('/api/v1/contracts', ['starts_on' => '01/02/2026', 'ends_on' => 'tomorrow'])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['contract_number', 'party_id', 'starts_on', 'ends_on']]]);
    }

    public function testAContractCannotEndBeforeItStarts(): void
    {
        $this->postJson('/api/v1/contracts', [
            'contract_number' => 'BAD-1',
            'party_id' => $this->createParty(),
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-01-01',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'invalid_contract_period')
            ->assertJsonStructure(['error' => ['details' => ['ends_on']]]);

        $this->assertDatabaseCount('contracts', 0);
    }

    public function testTheContractNumberMustBeUnique(): void
    {
        $this->createContract(['contract_number' => 'DUP-1']);

        $this->postJson('/api/v1/contracts', [
            'contract_number' => 'DUP-1', 'party_id' => $this->createParty(), 'starts_on' => '2026-01-01',
        ])->assertConflict()->assertJsonPath('error.code', 'contract_number_already_exists');
    }

    public function testTheContractListDoesNotContainTheDevices(): void
    {
        $contractId = $this->createContract();
        $this->attachDevice($contractId, $this->createDevice(), 'SN-1')->assertCreated();

        $response = $this->getJson('/api/v1/contracts')->assertOk();

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $contractId)
            ->assertJsonPath('meta.total', 1);
        $this->assertArrayNotHasKey('devices', $response->json('data.0'));
    }

    public function testTheContractDetailContainsTheDevices(): void
    {
        $contractId = $this->createContract();
        $deviceId = $this->createDevice(['name' => 'Latitude', 'manufacturer' => 'Dell', 'model' => '5440']);
        $assignmentId = $this->attachDevice($contractId, $deviceId, 'SN-1')
            ->assertCreated()
            ->json('data.devices.0.assignment_id');

        $this->getJson("/api/v1/contracts/{$contractId}")
            ->assertOk()
            ->assertJsonCount(1, 'data.devices')
            ->assertJsonPath('data.devices.0', [
                'assignment_id' => $assignmentId,
                'serial_number' => 'SN-1',
                'device' => [
                    'id' => $deviceId,
                    'name' => 'Latitude',
                    'manufacturer' => 'Dell',
                    'model' => '5440',
                    'description' => 'A test device',
                ],
            ]);
    }

    public function testAnUnknownContractIsA404(): void
    {
        $this->getJson('/api/v1/contracts/' . self::MISSING_ID)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'contract_not_found');
    }

    public function testAContractCanBeReplacedAndKeepsItsDevices(): void
    {
        $contractId = $this->createContract();
        $this->attachDevice($contractId, $this->createDevice(), 'SN-1')->assertCreated();
        $newParty = $this->createParty(['name' => 'New partner']);

        $this->putJson("/api/v1/contracts/{$contractId}", [
            'contract_number' => 'RENEWED-1',
            'party_id' => $newParty,
            'starts_on' => '2027-01-01',
            'ends_on' => null,
            'notes' => 'Renewed',
        ])
            ->assertOk()
            ->assertJsonPath('data.contract_number', 'RENEWED-1')
            ->assertJsonPath('data.party.id', $newParty)
            ->assertJsonPath('data.party.name', 'New partner')
            ->assertJsonPath('data.ends_on', null)
            ->assertJsonCount(1, 'data.devices');

        $this->assertDatabaseHas('contracts', [
            'id' => $contractId,
            'contract_number' => 'RENEWED-1',
            'party_id' => $newParty,
        ]);
    }

    public function testUpdatingAContractValidatesThePeriodAndTheNumber(): void
    {
        $this->createContract(['contract_number' => 'TAKEN']);
        $contractId = $this->createContract();

        $this->putJson("/api/v1/contracts/{$contractId}", [
            'contract_number' => 'TAKEN', 'party_id' => $this->createParty(), 'starts_on' => '2026-01-01',
        ])->assertConflict()->assertJsonPath('error.code', 'contract_number_already_exists');

        $this->putJson("/api/v1/contracts/{$contractId}", [
            'contract_number' => 'OK',
            'party_id' => $this->createParty(),
            'starts_on' => '2026-02-01',
            'ends_on' => '2026-01-01',
        ])->assertUnprocessable()->assertJsonPath('error.code', 'invalid_contract_period');
    }

    public function testUpdatingAnUnknownContractIsA404(): void
    {
        $this->putJson('/api/v1/contracts/' . self::MISSING_ID, [
            'contract_number' => 'X', 'party_id' => $this->createParty(), 'starts_on' => '2026-01-01',
        ])->assertNotFound()->assertJsonPath('error.code', 'contract_not_found');
    }

    public function testAContractCanBeDeletedAndReleasesItsDevices(): void
    {
        $contractId = $this->createContract();
        $deviceId = $this->createDevice();
        $this->attachDevice($contractId, $deviceId, 'SN-1')->assertCreated();

        $this->deleteJson("/api/v1/contracts/{$contractId}")->assertNoContent();

        $this->assertDatabaseMissing('contracts', ['id' => $contractId]);
        $this->assertDatabaseCount('contract_devices', 0);
        $this->getJson("/api/v1/contracts/{$contractId}")->assertNotFound();
        $this->deleteJson("/api/v1/devices/{$deviceId}")->assertNoContent();
    }

    public function testDeletingAnUnknownContractIsA404(): void
    {
        $this->deleteJson('/api/v1/contracts/' . self::MISSING_ID)->assertNotFound();
    }

    public function testTheSameDeviceTypeCanBeOnManyContractsWithDifferentSerialNumbers(): void
    {
        $deviceId = $this->createDevice();
        $first = $this->createContract();
        $second = $this->createContract();

        $this->attachDevice($first, $deviceId, 'SN-A')->assertCreated();
        $this->attachDevice($second, $deviceId, 'SN-B')->assertCreated();
        $this->attachDevice($second, $deviceId, 'SN-C')->assertCreated()->assertJsonCount(2, 'data.devices');

        $this->getJson("/api/v1/contracts/{$first}")->assertJsonCount(1, 'data.devices');
        $this->getJson("/api/v1/contracts/{$second}")->assertJsonCount(2, 'data.devices');
    }

    public function testAUnitCannotBePlacedTwice(): void
    {
        $deviceId = $this->createDevice();
        $first = $this->createContract();
        $second = $this->createContract();
        $this->attachDevice($first, $deviceId, 'SN-A')->assertCreated();

        $this->attachDevice($first, $deviceId, 'SN-A')
            ->assertConflict()
            ->assertJsonPath('error.code', 'serial_number_already_assigned');
        $this->attachDevice($second, $deviceId, 'SN-A')
            ->assertConflict()
            ->assertJsonPath('error.code', 'serial_number_already_assigned');

        $this->assertDatabaseCount('contract_devices', 1);
    }

    public function testDifferentDeviceTypesMayShareASerialNumber(): void
    {
        $contractId = $this->createContract();

        $this->attachDevice($contractId, $this->createDevice(), 'SN-1')->assertCreated();
        $this->attachDevice($contractId, $this->createDevice(), 'SN-1')->assertCreated();
    }

    public function testAttachingValidatesThePayload(): void
    {
        $contractId = $this->createContract();

        $this->postJson("/api/v1/contracts/{$contractId}/devices", ['device_id' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['device_id', 'serial_number']]]);
    }

    public function testAttachingAnUnknownDeviceIsRejectedAsABusinessError(): void
    {
        $contractId = $this->createContract();

        $this->attachDevice($contractId, self::MISSING_ID, 'SN-1')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'device_not_found')
            ->assertJsonStructure(['error' => ['details' => ['device_id']]]);
    }

    public function testAttachingToAnUnknownContractIsA404(): void
    {
        $this->attachDevice(self::MISSING_ID, $this->createDevice(), 'SN-1')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'contract_not_found');
    }

    public function testADeviceCanBeTakenOffAContract(): void
    {
        $contractId = $this->createContract();
        $deviceId = $this->createDevice();
        $keep = $this->attachDevice($contractId, $deviceId, 'SN-KEEP')->json('data.devices.0.assignment_id');
        $drop = $this->attachDevice($contractId, $deviceId, 'SN-DROP')->json('data.devices.1.assignment_id');

        $this->deleteJson("/api/v1/contracts/{$contractId}/devices/{$drop}")->assertNoContent();

        $this->getJson("/api/v1/contracts/{$contractId}")
            ->assertJsonCount(1, 'data.devices')
            ->assertJsonPath('data.devices.0.assignment_id', $keep);

        $other = $this->createContract();
        $this->attachDevice($other, $deviceId, 'SN-DROP')->assertCreated();
    }

    public function testTakingOffAnUnknownAssignmentIsA404(): void
    {
        $contractId = $this->createContract();

        $this->deleteJson("/api/v1/contracts/{$contractId}/devices/" . self::MISSING_ID)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'contract_device_not_found');
    }

    public function testAnAssignmentCannotBeRemovedThroughAnotherContract(): void
    {
        $owner = $this->createContract();
        $stranger = $this->createContract();
        $assignment = $this->attachDevice($owner, $this->createDevice(), 'SN-1')->json('data.devices.0.assignment_id');

        $this->deleteJson("/api/v1/contracts/{$stranger}/devices/{$assignment}")->assertNotFound();

        $this->getJson("/api/v1/contracts/{$owner}")->assertJsonCount(1, 'data.devices');
    }
}
