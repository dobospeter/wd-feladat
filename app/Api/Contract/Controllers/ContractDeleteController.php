<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use Illuminate\Http\Response;
use App\Modules\Contract\Handlers\ContractDeleteHandler;
use App\Modules\Contract\Handlers\Payloads\ContractDeletePayload;

final class ContractDeleteController
{
    public function __invoke(string $contract, ContractDeleteHandler $handler): Response
    {
        $handler->handle(new ContractDeletePayload($contract));

        return response()->noContent();
    }
}
