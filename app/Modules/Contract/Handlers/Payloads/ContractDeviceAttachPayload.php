<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

final readonly class ContractDeviceAttachPayload
{
    public function __construct(
        public string $contractId,
        public string $deviceId,
        public string $serialNumber,
    ) {
    }
}
