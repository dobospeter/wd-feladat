<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Contract\Support\ContractPartyGuard;
use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Handlers\Results\ContractDetailsResult;
use App\Modules\Contract\Handlers\Payloads\ContractCreatePayload;
use App\Modules\Contract\Exceptions\ContractNumberAlreadyExistsException;

final readonly class ContractCreateHandler
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractPartyGuard $partyGuard,
    ) {
    }

    public function handle(ContractCreatePayload $payload): ContractDetailsResult
    {
        $attributes = $payload->attributes();
        $this->partyGuard->ensureExists($payload->partyId);

        if ($this->contracts->numberExists($attributes['contract_number'])) {
            throw new ContractNumberAlreadyExistsException($attributes['contract_number']);
        }

        return ContractDetailsResult::fromModel($this->contracts->loadDetails($this->contracts->create($attributes)));
    }
}
