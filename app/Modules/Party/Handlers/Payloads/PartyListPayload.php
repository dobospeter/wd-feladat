<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers\Payloads;

use App\Core\Pagination\PageRequest;

final readonly class PartyListPayload
{
    public function __construct(public PageRequest $pagination)
    {
    }
}
