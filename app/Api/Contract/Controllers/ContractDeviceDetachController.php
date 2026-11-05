<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use Illuminate\Http\Response;
use App\Modules\Contract\Handlers\ContractDeviceDetachHandler;
use App\Modules\Contract\Handlers\Payloads\ContractDeviceDetachPayload;

final class ContractDeviceDetachController
{
    public function __invoke(string $contract, string $assignment, ContractDeviceDetachHandler $handler): Response
    {
        $handler->handle(new ContractDeviceDetachPayload($contract, $assignment));

        return response()->noContent();
    }
}
