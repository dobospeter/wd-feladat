<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Handlers\Payloads\ContractShowPayload;
use App\Modules\Contract\Handlers\Results\ContractDetailsResult;

final readonly class ContractShowHandler
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    public function handle(ContractShowPayload $payload): ContractDetailsResult
    {
        return ContractDetailsResult::fromModel($this->contracts->loadDetails($this->contracts->get($payload->id)));
    }
}
