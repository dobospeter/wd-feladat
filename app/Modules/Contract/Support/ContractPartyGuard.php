<?php

declare(strict_types=1);

namespace App\Modules\Contract\Support;

use App\Modules\Party\Repositories\PartyRepository;
use App\Modules\Party\Exceptions\PartyNotFoundException;
use App\Modules\Contract\Exceptions\PartyNotAvailableException;

final readonly class ContractPartyGuard
{
    public function __construct(private PartyRepository $parties)
    {
    }

    public function ensureExists(string $partyId): void
    {
        try {
            $this->parties->get($partyId);
        } catch (PartyNotFoundException) {
            throw new PartyNotAvailableException($partyId);
        }
    }
}
