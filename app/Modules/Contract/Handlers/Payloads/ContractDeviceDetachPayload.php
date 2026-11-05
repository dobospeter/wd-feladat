<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

final readonly class ContractDeviceDetachPayload
{
    public function __construct(
        public string $contractId,
        public string $assignmentId,
    ) {
    }
}
