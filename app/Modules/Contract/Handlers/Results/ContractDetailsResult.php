<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Results;

use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractDevice;

final readonly class ContractDetailsResult
{
    /**
     * @param list<ContractDeviceResult> $devices
     */
    public function __construct(
        public ContractResult $contract,
        public array $devices,
    ) {
    }

    /**
     * Expects the party and devices.device relations to be loaded.
     */
    public static function fromModel(Contract $contract): self
    {
        return new self(
            ContractResult::fromModel($contract),
            $contract->devices
                ->map(static fn (ContractDevice $unit): ContractDeviceResult => ContractDeviceResult::fromModel($unit))
                ->all(),
        );
    }
}
