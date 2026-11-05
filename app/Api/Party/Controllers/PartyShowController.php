<?php

declare(strict_types=1);

namespace App\Api\Party\Controllers;

use App\Api\Party\Resources\PartyResource;
use App\Modules\Party\Handlers\PartyShowHandler;
use App\Modules\Party\Handlers\Payloads\PartyShowPayload;

final class PartyShowController
{
    public function __invoke(string $party, PartyShowHandler $handler): PartyResource
    {
        return new PartyResource($handler->handle(new PartyShowPayload($party)));
    }
}
