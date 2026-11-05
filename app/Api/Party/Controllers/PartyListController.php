<?php

declare(strict_types=1);

namespace App\Api\Party\Controllers;

use App\Api\Party\Resources\PartyResource;
use App\Core\Http\Requests\PaginationRequest;
use App\Core\Http\Responses\PaginatedCollection;
use App\Modules\Party\Handlers\PartyListHandler;
use App\Modules\Party\Handlers\Payloads\PartyListPayload;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PartyListController
{
    public function __invoke(PaginationRequest $request, PartyListHandler $handler): AnonymousResourceCollection
    {
        return PaginatedCollection::from(
            $handler->handle(new PartyListPayload($request->toPageRequest())),
            PartyResource::class,
        );
    }
}
