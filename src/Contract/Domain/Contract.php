<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain;

use Inventory\Contract\Domain\Exception\ContractDeviceNotFound;
use Inventory\Contract\Domain\Exception\SerialNumberAlreadyAssigned;
use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Guard;

final class Contract
{
    public const NUMBER_MAX = 64;
    public const PARTNER_MAX = 255;
    public const NOTES_MAX = 2000;

    /**
     * @var array<string, ContractDevice> keyed by the assignment identifier
     */
    private array $devices = [];

    private function __construct(
        private readonly ContractId $id,
        private string $contractNumber,
        private string $partnerName,
        private ContractPeriod $period,
        private ?string $notes,
    ) {
    }

    public static function conclude(
        ContractId $id,
        string $contractNumber,
        string $partnerName,
        ContractPeriod $period,
        ?string $notes,
    ): self {
        return new self(
            $id,
            Guard::text($contractNumber, 'contract_number', self::NUMBER_MAX),
            Guard::text($partnerName, 'partner_name', self::PARTNER_MAX),
            $period,
            Guard::optionalText($notes, 'notes', self::NOTES_MAX),
        );
    }

    /**
     * @param list<ContractDevice> $devices
     */
    public static function reconstitute(
        ContractId $id,
        string $contractNumber,
        string $partnerName,
        ContractPeriod $period,
        ?string $notes,
        array $devices,
    ): self {
        $contract = new self($id, $contractNumber, $partnerName, $period, $notes);

        foreach ($devices as $device) {
            $contract->devices[$device->id->value] = $device;
        }

        return $contract;
    }

    public function revise(string $contractNumber, string $partnerName, ContractPeriod $period, ?string $notes): void
    {
        $this->contractNumber = Guard::text($contractNumber, 'contract_number', self::NUMBER_MAX);
        $this->partnerName = Guard::text($partnerName, 'partner_name', self::PARTNER_MAX);
        $this->period = $period;
        $this->notes = Guard::optionalText($notes, 'notes', self::NOTES_MAX);
    }

    public function attachDevice(ContractDeviceId $id, DeviceId $deviceId, SerialNumber $serialNumber): ContractDevice
    {
        foreach ($this->devices as $existing) {
            if ($existing->isUnit($deviceId, $serialNumber)) {
                throw new SerialNumberAlreadyAssigned($deviceId, $serialNumber);
            }
        }

        return $this->devices[$id->value] = new ContractDevice($id, $deviceId, $serialNumber);
    }

    public function detachDevice(ContractDeviceId $id): void
    {
        if (!isset($this->devices[$id->value])) {
            throw new ContractDeviceNotFound($this->id, $id);
        }

        unset($this->devices[$id->value]);
    }

    public function id(): ContractId
    {
        return $this->id;
    }

    public function contractNumber(): string
    {
        return $this->contractNumber;
    }

    public function partnerName(): string
    {
        return $this->partnerName;
    }

    public function period(): ContractPeriod
    {
        return $this->period;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return list<ContractDevice>
     */
    public function devices(): array
    {
        return array_values($this->devices);
    }
}
