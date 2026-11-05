<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Api\Contract\Requests\ContractSaveRequest;
use App\Api\Contract\Resources\ContractDetailsResource;
use App\Modules\Contract\Handlers\ContractCreateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ContractCreateController
{
    public function __invoke(ContractSaveRequest $request, ContractCreateHandler $handler): JsonResponse
    {
        $details = $handler->handle($request->toCreatePayload());

        return (new ContractDetailsResource($details))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('contracts.show', ['contract' => $details->contract->id]));
    }
}
