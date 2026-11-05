<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Domain\ContractDeviceId;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractRepository;

final readonly class DetachDevice
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    public function handle(ContractId $contractId, ContractDeviceId $assignmentId): void
    {
        $contract = $this->contracts->get($contractId);

        $contract->detachDevice($assignmentId);

        $this->contracts->save($contract);
    }
}
