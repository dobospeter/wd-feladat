<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

final readonly class ContractShowPayload
{
    public function __construct(public string $id)
    {
    }
}
