<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers;

use App\Modules\Party\Exceptions\PartyAlreadyExistsException;
use App\Modules\Party\Handlers\Payloads\PartyCreatePayload;
use App\Modules\Party\Handlers\Results\PartyResult;
use App\Modules\Party\Repositories\PartyRepository;

final readonly class PartyCreateHandler
{
    public function __construct(private PartyRepository $parties)
    {
    }

    public function handle(PartyCreatePayload $payload): PartyResult
    {
        if ($payload->taxNumber !== null && $this->parties->taxNumberExists($payload->taxNumber)) {
            throw new PartyAlreadyExistsException($payload->taxNumber);
        }

        return PartyResult::fromModel($this->parties->create($payload->attributes()));
    }
}
