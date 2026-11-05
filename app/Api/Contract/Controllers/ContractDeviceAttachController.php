<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Api\Contract\Requests\ContractDeviceAttachRequest;
use App\Api\Contract\Resources\ContractDetailsResource;
use App\Modules\Contract\Handlers\ContractDeviceAttachHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ContractDeviceAttachController
{
    public function __invoke(string $contract, ContractDeviceAttachRequest $request, ContractDeviceAttachHandler $handler): JsonResponse
    {
        return (new ContractDetailsResource($handler->handle($request->toPayload($contract))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('contracts.show', ['contract' => $contract]));
    }
}
