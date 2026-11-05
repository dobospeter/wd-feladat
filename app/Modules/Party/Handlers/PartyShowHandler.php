<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers;

use App\Modules\Party\Handlers\Payloads\PartyShowPayload;
use App\Modules\Party\Handlers\Results\PartyResult;
use App\Modules\Party\Repositories\PartyRepository;

final readonly class PartyShowHandler
{
    public function __construct(private PartyRepository $parties)
    {
    }

    public function handle(PartyShowPayload $payload): PartyResult
    {
        return PartyResult::fromModel($this->parties->get($payload->id));
    }
}
