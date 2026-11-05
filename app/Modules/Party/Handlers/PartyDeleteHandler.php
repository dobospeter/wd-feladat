<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers;

use App\Modules\Party\Handlers\Payloads\PartyDeletePayload;
use App\Modules\Party\Repositories\PartyRepository;

final readonly class PartyDeleteHandler
{
    public function __construct(private PartyRepository $parties)
    {
    }

    public function handle(PartyDeletePayload $payload): void
    {
        $this->parties->delete($this->parties->get($payload->id));
    }
}
