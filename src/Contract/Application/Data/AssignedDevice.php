<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\Data;

use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\SerialNumber;
use Inventory\Device\Domain\Device;

final readonly class AssignedDevice
{
    public function __construct(
        public ContractDeviceId $assignmentId,
        public Device $device,
        public SerialNumber $serialNumber,
    ) {
    }
}
