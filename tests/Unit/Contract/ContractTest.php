<?php

namespace Tests\Unit\Contract;

use DateTimeImmutable;
use Inventory\Contract\Domain\Contract;
use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractPeriod;
use Inventory\Contract\Domain\Exception\ContractDeviceNotFound;
use Inventory\Contract\Domain\Exception\InvalidContractPeriod;
use Inventory\Contract\Domain\Exception\SerialNumberAlreadyAssigned;
use Inventory\Contract\Domain\SerialNumber;
use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Exception\InvalidValue;
use PHPUnit\Framework\TestCase;

class ContractTest extends TestCase
{
    private function contract(): Contract
    {
        return Contract::conclude(
            ContractId::generate(),
            ' C-1 ',
            'Partner',
            new ContractPeriod(new DateTimeImmutable('2026-01-01'), null),
            '  ',
        );
    }

    public function test_the_text_fields_are_normalised(): void
    {
        $contract = $this->contract();

        $this->assertSame('C-1', $contract->contractNumber());
        $this->assertNull($contract->notes());
    }

    public function test_a_blank_contract_number_is_rejected(): void
    {
        $this->expectException(InvalidValue::class);

        Contract::conclude(
            ContractId::generate(),
            '   ',
            'Partner',
            new ContractPeriod(new DateTimeImmutable('2026-01-01'), null),
            null,
        );
    }

    public function test_the_period_cannot_end_before_it_starts(): void
    {
        $this->expectException(InvalidContractPeriod::class);

        new ContractPeriod(new DateTimeImmutable('2026-02-01'), new DateTimeImmutable('2026-01-31'));
    }

    public function test_a_one_day_period_is_valid(): void
    {
        $period = new ContractPeriod(new DateTimeImmutable('2026-02-01 10:00'), new DateTimeImmutable('2026-02-01 09:00'));

        $this->assertEquals($period->startsOn, $period->endsOn);
    }

    public function test_devices_can_be_attached_and_detached(): void
    {
        $contract = $this->contract();
        $deviceId = DeviceId::generate();
        $first = ContractDeviceId::generate();

        $contract->attachDevice($first, $deviceId, SerialNumber::fromString('SN-1'));
        $contract->attachDevice(ContractDeviceId::generate(), $deviceId, SerialNumber::fromString('SN-2'));
        $this->assertCount(2, $contract->devices());

        $contract->detachDevice($first);

        $this->assertCount(1, $contract->devices());
        $this->assertSame('SN-2', $contract->devices()[0]->serialNumber->value);
    }

    public function test_the_same_unit_cannot_be_attached_twice(): void
    {
        $contract = $this->contract();
        $deviceId = DeviceId::generate();
        $contract->attachDevice(ContractDeviceId::generate(), $deviceId, SerialNumber::fromString('SN-1'));

        $this->expectException(SerialNumberAlreadyAssigned::class);

        $contract->attachDevice(ContractDeviceId::generate(), $deviceId, SerialNumber::fromString('SN-1'));
    }

    public function test_detaching_an_unknown_assignment_fails(): void
    {
        $this->expectException(ContractDeviceNotFound::class);

        $this->contract()->detachDevice(ContractDeviceId::generate());
    }

    public function test_a_blank_serial_number_is_rejected(): void
    {
        $this->expectException(InvalidValue::class);

        SerialNumber::fromString('  ');
    }

    public function test_identifiers_must_be_uuids(): void
    {
        $this->expectException(InvalidValue::class);

        ContractId::fromString('42');
    }

    public function test_identifiers_of_different_types_are_never_equal(): void
    {
        $uuid = ContractId::generate()->value;

        $this->assertFalse(ContractId::fromString($uuid)->equals(DeviceId::fromString($uuid)));
        $this->assertTrue(ContractId::fromString($uuid)->equals(ContractId::fromString(strtoupper($uuid))));
    }
}
