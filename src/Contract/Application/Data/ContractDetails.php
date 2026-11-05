<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\Data;

use Inventory\Contract\Domain\Contract;

final readonly class ContractDetails
{
    /**
     * @param list<AssignedDevice> $devices
     */
    public function __construct(
        public Contract $contract,
        public array $devices,
    ) {
    }
}
