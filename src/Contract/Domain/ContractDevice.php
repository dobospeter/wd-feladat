<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain;

use Inventory\Device\Domain\DeviceId;

final readonly class ContractDevice
{
    public function __construct(
        public ContractDeviceId $id,
        public DeviceId $deviceId,
        public SerialNumber $serialNumber,
    ) {
    }

    public function isUnit(DeviceId $deviceId, SerialNumber $serialNumber): bool
    {
        return $this->deviceId->equals($deviceId) && $this->serialNumber->equals($serialNumber);
    }
}
