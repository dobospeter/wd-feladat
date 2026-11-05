<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Uid\Uuid;
use Tests\Feature\Concerns\CreatesFixtures;
use Tests\TestCase;

class PartyApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    private const MISSING_ID = '0199c0de-0000-7000-8000-000000000000';

    public function test_a_party_can_be_created(): void
    {
        $response = $this->postJson('/api/v1/parties', [
            'name' => 'Happy Gastro Kft.',
            'tax_number' => '12345678-2-42',
            'address' => '1051 Budapest, Test utca 1.',
            'email' => 'info@happygastro.hu',
            'phone' => '+36 1 234 5678',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Happy Gastro Kft.')
            ->assertJsonPath('data.tax_number', '12345678-2-42')
            ->assertJsonPath('data.address', '1051 Budapest, Test utca 1.')
            ->assertJsonPath('data.email', 'info@happygastro.hu')
            ->assertJsonPath('data.phone', '+36 1 234 5678');

        $id = $response->json('data.id');
        $this->assertTrue(Uuid::isValid($id));
        $response->assertHeader('Location', url("/api/v1/parties/{$id}"));
        $this->assertDatabaseHas('parties', ['id' => $id, 'name' => 'Happy Gastro Kft.']);
    }

    public function test_only_the_name_is_required_and_blank_optionals_become_null(): void
    {
        $this->postJson('/api/v1/parties', ['name' => '  Minimal Kft.  ', 'tax_number' => '  ', 'email' => ''])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Minimal Kft.')
            ->assertJsonPath('data.tax_number', null)
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null);
    }

    public function test_creating_a_party_validates_the_payload(): void
    {
        $this->postJson('/api/v1/parties', ['email' => 'not-an-email', 'tax_number' => str_repeat('1', 33)])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['name', 'email', 'tax_number']]]);
    }

    public function test_the_tax_number_must_be_unique(): void
    {
        $this->createParty(['tax_number' => '11111111-1-11']);

        $this->postJson('/api/v1/parties', ['name' => 'Other', 'tax_number' => '11111111-1-11'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_already_exists');
    }

    public function test_parties_without_a_tax_number_do_not_conflict(): void
    {
        $this->createParty();
        $this->createParty();

        $this->assertDatabaseCount('parties', 2);
    }

    public function test_parties_are_listed_by_name_with_pagination_metadata(): void
    {
        $this->createParty(['name' => 'Charlie']);
        $this->createParty(['name' => 'Alpha']);
        $this->createParty(['name' => 'Bravo']);

        $this->getJson('/api/v1/parties?per_page=2')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha')
            ->assertJsonPath('data.1.name', 'Bravo')
            ->assertJsonPath('meta', ['page' => 1, 'per_page' => 2, 'total' => 3, 'last_page' => 2]);
    }

    public function test_a_single_party_can_be_fetched(): void
    {
        $id = $this->createParty(['name' => 'Fetch me']);

        $this->getJson("/api/v1/parties/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Fetch me');
    }

    public function test_an_unknown_party_is_a_404(): void
    {
        $this->getJson('/api/v1/parties/' . self::MISSING_ID)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'party_not_found');
    }

    public function test_a_party_can_be_replaced(): void
    {
        $id = $this->createParty(['tax_number' => '22222222-2-22']);

        $this->putJson("/api/v1/parties/{$id}", ['name' => 'Renamed', 'tax_number' => null, 'phone' => '123'])
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Renamed')
            ->assertJsonPath('data.tax_number', null)
            ->assertJsonPath('data.address', null)
            ->assertJsonPath('data.phone', '123');
    }

    public function test_a_party_can_be_saved_without_changing_its_tax_number(): void
    {
        $id = $this->createParty(['tax_number' => '33333333-3-33']);

        $this->putJson("/api/v1/parties/{$id}", ['name' => 'Same tax', 'tax_number' => '33333333-3-33'])->assertOk();
    }

    public function test_updating_a_party_into_an_existing_tax_number_conflicts(): void
    {
        $this->createParty(['tax_number' => '44444444-4-44']);
        $id = $this->createParty(['tax_number' => '55555555-5-55']);

        $this->putJson("/api/v1/parties/{$id}", ['name' => 'X', 'tax_number' => '44444444-4-44'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_already_exists');
    }

    public function test_updating_an_unknown_party_is_a_404(): void
    {
        $this->putJson('/api/v1/parties/' . self::MISSING_ID, ['name' => 'X'])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'party_not_found');
    }

    public function test_a_party_can_be_deleted(): void
    {
        $id = $this->createParty();

        $this->deleteJson("/api/v1/parties/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('parties', ['id' => $id]);
        $this->deleteJson("/api/v1/parties/{$id}")->assertNotFound();
    }

    public function test_a_party_with_contracts_cannot_be_deleted(): void
    {
        $partyId = $this->createParty();
        $contractId = $this->createContract(['party_id' => $partyId]);

        $this->deleteJson("/api/v1/parties/{$partyId}")
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_in_use');

        $this->deleteJson("/api/v1/contracts/{$contractId}")->assertNoContent();
        $this->deleteJson("/api/v1/parties/{$partyId}")->assertNoContent();
    }

    public function test_a_party_can_be_shared_by_many_contracts(): void
    {
        $partyId = $this->createParty();
        $this->createContract(['party_id' => $partyId]);
        $this->createContract(['party_id' => $partyId]);

        $this->assertDatabaseCount('contracts', 2);
        $this->assertDatabaseCount('parties', 1);
    }
}
