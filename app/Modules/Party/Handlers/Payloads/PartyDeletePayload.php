<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers\Payloads;

final readonly class PartyDeletePayload
{
    public function __construct(public string $id)
    {
    }
}
