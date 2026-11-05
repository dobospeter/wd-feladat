<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Modules\Contract\Handlers\ContractDeviceDetachHandler;
use App\Modules\Contract\Handlers\Payloads\ContractDeviceDetachPayload;
use Illuminate\Http\Response;

final class ContractDeviceDetachController
{
    public function __invoke(string $contract, string $assignment, ContractDeviceDetachHandler $handler): Response
    {
        $handler->handle(new ContractDeviceDetachPayload($contract, $assignment));

        return response()->noContent();
    }
}
