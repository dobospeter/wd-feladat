<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Modules\Contract\Handlers\ContractShowHandler;
use App\Api\Contract\Resources\ContractDetailsResource;
use App\Modules\Contract\Handlers\Payloads\ContractShowPayload;

final class ContractShowController
{
    public function __invoke(string $contract, ContractShowHandler $handler): ContractDetailsResource
    {
        return new ContractDetailsResource($handler->handle(new ContractShowPayload($contract)));
    }
}
