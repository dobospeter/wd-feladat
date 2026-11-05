<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Contract\Support\ContractPartyGuard;
use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Handlers\Results\ContractDetailsResult;
use App\Modules\Contract\Handlers\Payloads\ContractUpdatePayload;
use App\Modules\Contract\Exceptions\ContractNumberAlreadyExistsException;

final readonly class ContractUpdateHandler
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractPartyGuard $partyGuard,
    ) {
    }

    public function handle(ContractUpdatePayload $payload): ContractDetailsResult
    {
        $contract = $this->contracts->get($payload->id);
        $attributes = $payload->attributes();
        $this->partyGuard->ensureExists($payload->partyId);

        if ($this->contracts->numberExists($attributes['contract_number'], $payload->id)) {
            throw new ContractNumberAlreadyExistsException($attributes['contract_number']);
        }

        $updated = $this->contracts->update($contract, $attributes);

        return ContractDetailsResult::fromModel($this->contracts->loadDetails($updated));
    }
}
