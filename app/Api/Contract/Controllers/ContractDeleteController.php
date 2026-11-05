<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Modules\Contract\Handlers\ContractDeleteHandler;
use App\Modules\Contract\Handlers\Payloads\ContractDeletePayload;
use Illuminate\Http\Response;

final class ContractDeleteController
{
    public function __invoke(string $contract, ContractDeleteHandler $handler): Response
    {
        $handler->handle(new ContractDeletePayload($contract));

        return response()->noContent();
    }
}
