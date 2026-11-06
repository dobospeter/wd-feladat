<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Symfony\Component\Uid\Uuid;
use App\Modules\Party\Models\Party;
use Tests\Feature\Concerns\CreatesFixtures;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartyApiTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    private const string MISSING_ID = '0199c0de-0000-7000-8000-000000000000';

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function validPayloads(): array
    {
        return [
            'only the name' => [['name' => 'Minimal Kft.']],
            'every field' => [[
                'name' => 'Full Kft.',
                'tax_number' => '12345678-2-42',
                'address' => '1051 Budapest, Test utca 1.',
                'email' => 'info@example.com',
                'phone' => '+36 1 234 5678',
            ]],
            'explicit nulls' => [[
                'name' => 'Nulls', 'tax_number' => null, 'address' => null, 'email' => null, 'phone' => null,
            ]],
            'blank optionals' => [[
                'name' => 'Blanks', 'tax_number' => '', 'address' => '  ', 'email' => '', 'phone' => '',
            ]],
            'longest name' => [['name' => str_repeat('n', Party::NAME_MAX)]],
            'longest tax number' => [['name' => 'Tax', 'tax_number' => str_repeat('1', Party::TAX_NUMBER_MAX)]],
            'longest address' => [['name' => 'Address', 'address' => str_repeat('a', Party::ADDRESS_MAX)]],
            'longest phone' => [['name' => 'Phone', 'phone' => str_repeat('1', Party::PHONE_MAX)]],
            'accented name' => [['name' => 'Árvíztűrő Tükörfúrógép Kft.']],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, list<string>}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'empty payload' => [[], ['name']],
            'missing name' => [['tax_number' => '1'], ['name']],
            'null name' => [['name' => null], ['name']],
            'empty name' => [['name' => ''], ['name']],
            'name is not a string' => [['name' => 123], ['name']],
            'name is an array' => [['name' => ['x']], ['name']],
            'name too long' => [['name' => str_repeat('n', Party::NAME_MAX + 1)], ['name']],
            'tax number too long' => [
                ['name' => 'X', 'tax_number' => str_repeat('1', Party::TAX_NUMBER_MAX + 1)],
                ['tax_number'],
            ],
            'address too long' => [
                ['name' => 'X', 'address' => str_repeat('a', Party::ADDRESS_MAX + 1)],
                ['address'],
            ],
            'phone too long' => [['name' => 'X', 'phone' => str_repeat('1', Party::PHONE_MAX + 1)], ['phone']],
            'email without at sign' => [['name' => 'X', 'email' => 'not-an-email'], ['email']],
            'email without domain' => [['name' => 'X', 'email' => 'user@'], ['email']],
            'email is not a string' => [['name' => 'X', 'email' => 42], ['email']],
            'several errors at once' => [
                ['email' => 'nope', 'tax_number' => str_repeat('1', Party::TAX_NUMBER_MAX + 1)],
                ['name', 'email', 'tax_number'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('validPayloads')]
    public function testValidPayloadsCreateAParty(array $payload): void
    {
        // Arrange: the payload comes from the data provider.

        // Act
        $response = $this->postJson('/api/v1/parties', $payload);

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('parties', ['id' => $response->json('data.id')]);
        $this->assertDatabaseCount('parties', 1);
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
        $response = $this->postJson('/api/v1/parties', $payload);

        // Assert
        $response->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
        $this->assertEqualsCanonicalizing($invalidFields, array_keys($response->json('error.details')));
        $this->assertDatabaseCount('parties', 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $invalidFields
     */
    #[DataProvider('invalidPayloads')]
    public function testInvalidPayloadsAreRejectedOnUpdateToo(array $payload, array $invalidFields): void
    {
        // Arrange
        $id = $this->createParty(['name' => 'Untouched']);

        // Act
        $response = $this->putJson("/api/v1/parties/{$id}", $payload);

        // Assert
        $response->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
        $this->assertEqualsCanonicalizing($invalidFields, array_keys($response->json('error.details')));
        $this->assertDatabaseHas('parties', ['id' => $id, 'name' => 'Untouched']);
    }

    public function testAPartyCanBeCreated(): void
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

    public function testOnlyTheNameIsRequiredAndBlankOptionalsBecomeNull(): void
    {
        $this->postJson('/api/v1/parties', ['name' => '  Minimal Kft.  ', 'tax_number' => '  ', 'email' => ''])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Minimal Kft.')
            ->assertJsonPath('data.tax_number', null)
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null);
    }

    public function testTheTaxNumberMustBeUnique(): void
    {
        $this->createParty(['tax_number' => '11111111-1-11']);

        $this->postJson('/api/v1/parties', ['name' => 'Other', 'tax_number' => '11111111-1-11'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_already_exists');
    }

    public function testPartiesWithoutATaxNumberDoNotConflict(): void
    {
        $this->createParty();
        $this->createParty();

        $this->assertDatabaseCount('parties', 2);
    }

    public function testPartiesAreListedByNameWithPaginationMetadata(): void
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

    public function testASinglePartyCanBeFetched(): void
    {
        $id = $this->createParty(['name' => 'Fetch me']);

        $this->getJson("/api/v1/parties/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.name', 'Fetch me');
    }

    public function testAnUnknownPartyIsA404(): void
    {
        $this->getJson('/api/v1/parties/' . self::MISSING_ID)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'party_not_found');
    }

    public function testAPartyCanBeReplaced(): void
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

    public function testAPartyCanBeSavedWithoutChangingItsTaxNumber(): void
    {
        $id = $this->createParty(['tax_number' => '33333333-3-33']);

        $this->putJson("/api/v1/parties/{$id}", ['name' => 'Same tax', 'tax_number' => '33333333-3-33'])->assertOk();
    }

    public function testUpdatingAPartyIntoAnExistingTaxNumberConflicts(): void
    {
        $this->createParty(['tax_number' => '44444444-4-44']);
        $id = $this->createParty(['tax_number' => '55555555-5-55']);

        $this->putJson("/api/v1/parties/{$id}", ['name' => 'X', 'tax_number' => '44444444-4-44'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_already_exists');
    }

    public function testUpdatingAnUnknownPartyIsA404(): void
    {
        $this->putJson('/api/v1/parties/' . self::MISSING_ID, ['name' => 'X'])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'party_not_found');
    }

    public function testAPartyCanBeDeleted(): void
    {
        $id = $this->createParty();

        $this->deleteJson("/api/v1/parties/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('parties', ['id' => $id]);
        $this->deleteJson("/api/v1/parties/{$id}")->assertNotFound();
    }

    public function testAPartyWithContractsCannotBeDeleted(): void
    {
        $partyId = $this->createParty();
        $contractId = $this->createContract(['party_id' => $partyId]);

        $this->deleteJson("/api/v1/parties/{$partyId}")
            ->assertConflict()
            ->assertJsonPath('error.code', 'party_in_use');

        $this->deleteJson("/api/v1/contracts/{$contractId}")->assertNoContent();
        $this->deleteJson("/api/v1/parties/{$partyId}")->assertNoContent();
    }

    public function testAPartyCanBeSharedByManyContracts(): void
    {
        $partyId = $this->createParty();
        $this->createContract(['party_id' => $partyId]);
        $this->createContract(['party_id' => $partyId]);

        $this->assertDatabaseCount('contracts', 2);
        $this->assertDatabaseCount('parties', 1);
    }
}
