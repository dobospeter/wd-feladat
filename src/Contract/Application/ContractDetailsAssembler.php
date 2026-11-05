<?php

declare(strict_types=1);

namespace Inventory\Contract\Application;

use Inventory\Contract\Application\Data\AssignedDevice;
use Inventory\Contract\Application\Data\ContractDetails;
use Inventory\Contract\Domain\Contract;
use Inventory\Contract\Domain\ContractDevice;
use Inventory\Device\Domain\DeviceRepository;

final readonly class ContractDetailsAssembler
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function assemble(Contract $contract): ContractDetails
    {
        $placed = $contract->devices();

        $known = $this->devices->findByIds(
            array_map(static fn (ContractDevice $unit) => $unit->deviceId, $placed),
        );

        $assigned = [];
        foreach ($placed as $unit) {
            $device = $known[$unit->deviceId->value] ?? null;

            // The foreign key guarantees the device exists; skip defensively.
            if ($device !== null) {
                $assigned[] = new AssignedDevice($unit->id, $device, $unit->serialNumber);
            }
        }

        return new ContractDetails($contract, $assigned);
    }
}
