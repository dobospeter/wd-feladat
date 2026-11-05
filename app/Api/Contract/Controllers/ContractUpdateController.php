<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Api\Contract\Requests\ContractSaveRequest;
use App\Api\Contract\Resources\ContractDetailsResource;
use App\Modules\Contract\Handlers\ContractUpdateHandler;

final class ContractUpdateController
{
    public function __invoke(string $contract, ContractSaveRequest $request, ContractUpdateHandler $handler): ContractDetailsResource
    {
        return new ContractDetailsResource($handler->handle($request->toUpdatePayload($contract)));
    }
}
