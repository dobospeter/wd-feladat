<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers;

use App\Modules\Party\Handlers\Results\PartyResult;
use App\Modules\Party\Repositories\PartyRepository;
use App\Modules\Party\Handlers\Payloads\PartyUpdatePayload;
use App\Modules\Party\Exceptions\PartyAlreadyExistsException;

final readonly class PartyUpdateHandler
{
    public function __construct(private PartyRepository $parties)
    {
    }

    public function handle(PartyUpdatePayload $payload): PartyResult
    {
        $party = $this->parties->get($payload->id);

        if ($payload->taxNumber !== null && $this->parties->taxNumberExists($payload->taxNumber, $payload->id)) {
            throw new PartyAlreadyExistsException($payload->taxNumber);
        }

        return PartyResult::fromModel($this->parties->update($party, $payload->attributes()));
    }
}
