<?php

declare(strict_types=1);

namespace App\Api\Party\Controllers;

use App\Api\Party\Resources\PartyResource;
use App\Api\Party\Requests\PartySaveRequest;
use App\Modules\Party\Handlers\PartyUpdateHandler;

final class PartyUpdateController
{
    public function __invoke(string $party, PartySaveRequest $request, PartyUpdateHandler $handler): PartyResource
    {
        return new PartyResource($handler->handle($request->toUpdatePayload($party)));
    }
}
