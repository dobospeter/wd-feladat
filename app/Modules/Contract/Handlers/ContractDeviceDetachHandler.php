<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Repositories\ContractDeviceRepository;
use App\Modules\Contract\Handlers\Payloads\ContractDeviceDetachPayload;

final readonly class ContractDeviceDetachHandler
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractDeviceRepository $contractDevices,
    ) {
    }

    public function handle(ContractDeviceDetachPayload $payload): void
    {
        $contract = $this->contracts->get($payload->contractId);

        $this->contractDevices->delete($this->contractDevices->getInContract($contract->id, $payload->assignmentId));
    }
}
