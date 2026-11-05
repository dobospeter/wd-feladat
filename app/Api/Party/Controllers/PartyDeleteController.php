<?php

declare(strict_types=1);

namespace App\Api\Party\Controllers;

use Illuminate\Http\Response;
use App\Modules\Party\Handlers\PartyDeleteHandler;
use App\Modules\Party\Handlers\Payloads\PartyDeletePayload;

final class PartyDeleteController
{
    public function __invoke(string $party, PartyDeleteHandler $handler): Response
    {
        $handler->handle(new PartyDeletePayload($party));

        return response()->noContent();
    }
}
