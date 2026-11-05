<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Results;

use App\Modules\Contract\Models\ContractDevice;
use App\Modules\Device\Handlers\Results\DeviceResult;

final readonly class ContractDeviceResult
{
    public function __construct(
        public string $assignmentId,
        public string $serialNumber,
        public DeviceResult $device,
    ) {
    }

    public static function fromModel(ContractDevice $contractDevice): self
    {
        return new self(
            $contractDevice->id,
            $contractDevice->serial_number,
            DeviceResult::fromModel($contractDevice->device),
        );
    }
}
