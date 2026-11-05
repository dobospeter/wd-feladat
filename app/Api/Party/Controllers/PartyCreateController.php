<?php

declare(strict_types=1);

namespace App\Api\Party\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Api\Party\Resources\PartyResource;
use App\Api\Party\Requests\PartySaveRequest;
use App\Modules\Party\Handlers\PartyCreateHandler;

final class PartyCreateController
{
    public function __invoke(PartySaveRequest $request, PartyCreateHandler $handler): JsonResponse
    {
        $party = $handler->handle($request->toCreatePayload());

        return (new PartyResource($party))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('parties.show', ['party' => $party->id]));
    }
}
