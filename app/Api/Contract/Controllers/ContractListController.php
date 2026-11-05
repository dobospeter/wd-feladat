<?php

declare(strict_types=1);

namespace App\Api\Contract\Controllers;

use App\Core\Http\Requests\PaginationRequest;
use App\Api\Contract\Resources\ContractResource;
use App\Core\Http\Responses\PaginatedCollection;
use App\Modules\Contract\Handlers\ContractListHandler;
use App\Modules\Contract\Handlers\Payloads\ContractListPayload;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ContractListController
{
    public function __invoke(PaginationRequest $request, ContractListHandler $handler): AnonymousResourceCollection
    {
        return PaginatedCollection::from(
            $handler->handle(new ContractListPayload($request->toPageRequest())),
            ContractResource::class,
        );
    }
}
