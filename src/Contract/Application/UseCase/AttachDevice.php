<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Application\ContractDetailsAssembler;
use Inventory\Contract\Application\Data\ContractDetails;
use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractRepository;
use Inventory\Contract\Domain\Exception\DeviceNotAvailable;
use Inventory\Contract\Domain\Exception\SerialNumberAlreadyAssigned;
use Inventory\Contract\Domain\SerialNumber;
use Inventory\Device\Domain\DeviceId;
use Inventory\Device\Domain\DeviceRepository;
use Inventory\Device\Domain\Exception\DeviceNotFound;

final readonly class AttachDevice
{
    public function __construct(
        private ContractRepository $contracts,
        private DeviceRepository $devices,
        private ContractDetailsAssembler $assembler,
    ) {
    }

    public function handle(ContractId $contractId, DeviceId $deviceId, string $serialNumber): ContractDetails
    {
        $contract = $this->contracts->get($contractId);
        $serial = SerialNumber::fromString($serialNumber);

        try {
            $this->devices->get($deviceId);
        } catch (DeviceNotFound) {
            throw new DeviceNotAvailable($deviceId);
        }

        if ($this->contracts->unitExists($deviceId, $serial)) {
            throw new SerialNumberAlreadyAssigned($deviceId, $serial);
        }

        $contract->attachDevice(ContractDeviceId::generate(), $deviceId, $serial);
        $this->contracts->save($contract);

        return $this->assembler->assemble($contract);
    }
}
